<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
date_default_timezone_set('America/Guayaquil');
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_CONFIG_PATH . '/google.php';
emxVerificarCsrfSiPOST();
require_once EMX_HELPERS_PATH . '/funciones_facturacion.php';
require_once EMX_HELPERS_PATH . '/flujo_admin.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth.php?action=login');
    exit();
}

$rol = $_SESSION['usuario_rol'] ?? 'CLIENTE';
if ($rol !== 'CLIENTE') {
    if ($rol === 'PROVEEDOR') header('Location: proveedor.php');
    elseif ($rol === 'SUPERADMIN' || $rol === 'ADMIN') header('Location: admin.php');
    else header('Location: index.php');
    exit();
}

$user_id = $_SESSION['usuario_id'];
emxGarantizarColumnasDevoluciones($pdo);
$msg = $_GET['msg'] ?? null;
$msg_type = $_GET['msg_type'] ?? 'success';
$seccion_activa = $_GET['seccion'] ?? 'pedidos';

// ============================================
// SIMULACIÓN: Verificar si el período de prueba expiró y cobrar automáticamente
// ============================================
$stmt_user = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!empty($user['es_prueba']) && ($user['es_prueba'] === 't' || $user['es_prueba'] === true)) {
    $expira_en = strtotime($user['plan_expira_en']);
    if (time() >= $expira_en) {
        $pdo->prepare("UPDATE usuarios SET es_prueba = FALSE WHERE id = ?")->execute([$user_id]);
        $user['es_prueba'] = false;
        $msg = 'Tu período de prueba ha finalizado. Se ha procesado el pago simulado de tu membresía exitosamente.';
        $msg_type = 'success';
    }
}

// ============================================
// PROCESAR CANCELACIÓN DE MEMBRESÍA
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_membresia'])) {
    try {
        $pdo->beginTransaction();
        $pdo->prepare("
            UPDATE usuarios 
            SET plan_id = NULL, plan_activo = FALSE, plan_expira_en = NULL, 
                es_prime = FALSE, tiene_badge_verificado = FALSE, es_prueba = FALSE
            WHERE id = ?
        ")->execute([$user_id]);
        
        $_SESSION['es_prime'] = false;
        $_SESSION['es_verificado'] = false;
        
        $pdo->commit();
        $msg = 'Tu membresía ha sido cancelada. Los beneficios se mantendrán hasta el final de tu período actual.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = 'Error al cancelar la membresía.';
        $msg_type = 'error';
    }
}

// ============================================
// PROCESAR CANCELACIÓN DE PEDIDO
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_pedido'])) {
    $pedido_id = $_POST['pedido_id'] ?? null;
    try {
        $stmt = $pdo->prepare("SELECT estado, estado_pago, historial_estados FROM pedidos WHERE id = ? AND usuario_id = ? FOR UPDATE");
        $pdo->beginTransaction();
        $stmt->execute([$pedido_id, $user_id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Regla simple: el cliente puede cancelar solo antes de que el admin apruebe el pago.
        if ($pedido && $pedido['estado'] === 'Pendiente' && (($pedido['estado_pago'] ?? 'pendiente_aprobacion') === 'pendiente_aprobacion')) {
            if (function_exists('emxLiberarInventarioPedidoCancelado')) {
                emxLiberarInventarioPedidoCancelado($pdo, $pedido_id);
            }
            $historial = json_decode($pedido['historial_estados'] ?: '[]', true);
            if (!is_array($historial)) $historial = [];
            $historial[] = ['estado' =>'Cancelado', 'descripcion' =>'Pedido cancelado por el cliente antes de aprobación de pago. Se liberó la reserva.', 'fecha' =>date('Y-m-d H:i:s'), 'icono' =>'fa-times-circle'];
            $pdo->prepare("UPDATE pedidos SET estado = 'Cancelado', estado_pago = 'cancelado', cancelado_en = NOW(), cancelado_por = ?, motivo_cancelacion = 'Cancelado por cliente antes de aprobación', historial_estados = ?::jsonb WHERE id = ?")->execute([$user_id, json_encode($historial, JSON_UNESCAPED_UNICODE), $pedido_id]);
            $pdo->commit();
            $msg = 'Pedido cancelado exitosamente. La reserva fue liberada.';
        } else {
            $pdo->rollBack();
            $msg = 'Este pedido ya no se puede cancelar porque el pago fue aprobado o el pedido avanzó de estado.';
            $msg_type = 'error';
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $msg = 'Error al cancelar el pedido.';
        $msg_type = 'error';
    }
}

// ============================================
// ⭐ PROCESAR CONFIRMACIÓN DE ENTREGA (CON FOTOS Y REEMPLAZO SEGURO)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_confirmacion'])) {
    $pedido_id = $_POST['pedido_id'] ?? null;
    $accion = $_POST['accion_confirmacion'] ?? '';
    $solucion_extravio = $_POST['solucion_extravio'] ?? null;
    
    try {
        // Obtenemos TODOS los datos necesarios del pedido original para evitar errores NOT NULL
        $stmt = $pdo->prepare("SELECT id, usuario_id, estado, confirmacion_cliente_estado, historial_estados, nombre_cliente, email, telefono, direccion, ciudad, codigo_postal, provincia, subtotal, iva_total, total, metodo_pago, sucursal_asignada_id, fecha_estimada_entrega FROM pedidos WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$pedido_id, $user_id]);
        $pedido_check = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pedido_check && $pedido_check['estado'] === 'Entregado' && $pedido_check['confirmacion_cliente_estado'] === 'pendiente') {
            $pdo->beginTransaction();
            $historial = json_decode($pedido_check['historial_estados'] ?: '[]', true);
            
            if ($accion === 'confirmar_ok') {
                // ⭐ PROCESAR FOTOS DE EVIDENCIA
                $fotos_confirmacion = [];
                if (!empty($_FILES['fotos_confirmacion']['name'][0])) {
                    $fotos_confirmacion = emxSubirArchivosMultiplesSeguro('fotos_confirmacion', emxCarpetaConfirmacionPedidoUploads($pedido_id), ['prefijo' =>'conf_' . preg_replace('/[^a-z0-9]/i', '', substr($pedido_id, 0, 8))]);
                }
                
                $descripcion = ' El cliente confirmó la recepción en perfectas condiciones.';
                if (!empty($fotos_confirmacion)) {
                    $descripcion .= ' Se adjuntaron ' . count($fotos_confirmacion) . ' foto(s) de evidencia.';
                }
                $descripcion .= ' Garantía de 30 días activada.';
                
                $historial[] = ['estado' =>'Confirmado por Cliente', 'descripcion' =>$descripcion, 'fecha' =>date('Y-m-d H:i:s'), 'icono' =>'fa-check-circle'];
                
                $pdo->prepare("UPDATE pedidos SET confirmacion_cliente_estado = 'confirmado_ok', fecha_confirmacion_cliente = NOW(), fotos_confirmacion = ?::jsonb, historial_estados = ?::jsonb WHERE id = ?")
                    ->execute([json_encode($fotos_confirmacion), json_encode($historial), $pedido_id]);
                $msg = '¡Gracias por confirmar! Tu garantía de 30 días ha comenzado.';
                
            } elseif ($accion === 'confirmar_no_recibido') {
                if ($solucion_extravio === 'reenvio_express') {
                    $historial[] = ['estado' =>'Incidencia: No Recibido - Reenvío', 'descripcion' =>' Cliente no recibió. Se creó pedido de reemplazo express.', 'fecha' =>date('Y-m-d H:i:s'), 'icono' =>'fa-exclamation-triangle'];
                    $pdo->prepare("UPDATE pedidos SET estado = 'En Revisión', confirmacion_cliente_estado = 'no_recibido_reenvio', historial_estados = ?::jsonb WHERE id = ?")
                        ->execute([json_encode($historial), $pedido_id]);
                    
                    // ⭐ INSERT SEGURO: Copiamos los datos del pedido original para evitar violación de restricciones NOT NULL
                    $stmt_new = $pdo->prepare("
                        INSERT INTO pedidos (
                            usuario_id, nombre_cliente, email, telefono, direccion, ciudad, codigo_postal, 
                            subtotal, iva_total, total, metodo_pago, sucursal_asignada_id, fecha_estimada_entrega, 
                            estado, historial_estados, confirmacion_cliente_estado
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'En Preparación', ?::jsonb, 'pendiente')
                        RETURNING id
                    ");
                    $stmt_new->execute([
                        $user_id,
                        $pedido_check['nombre_cliente'],
                        $pedido_check['email'],
                        $pedido_check['telefono'],
                        $pedido_check['direccion'],
                        $pedido_check['ciudad'],
                        $pedido_check['codigo_postal'],
                        $pedido_check['subtotal'],
                        $pedido_check['iva_total'],
                        $pedido_check['total'],
                        $pedido_check['metodo_pago'],
                        $pedido_check['sucursal_asignada_id'],
                        $pedido_check['fecha_estimada_entrega'],
                        json_encode([['estado' =>'En Preparación', 'descripcion' =>'Pedido de reemplazo por incidencia de no recibido.', 'fecha' =>date('Y-m-d H:i:s'), 'icono' =>'fa-box-open']])
                    ]);
                    $nuevo_pedido_id = $stmt_new->fetchColumn();
                    
                    // ⭐ CORREGIDO: Copiar detalles incluyendo nombre_producto e iva_porcentaje para evitar errores NOT NULL
                    $stmt_det = $pdo->prepare("SELECT producto_id, nombre_producto, cantidad, precio_unitario, iva_porcentaje, total FROM detalle_pedidos WHERE pedido_id = ?");
                    $stmt_det->execute([$pedido_id]);
                    $productos = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($productos as $prod) {
                        $pdo->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario, iva_porcentaje, total) VALUES (?, ?, ?, ?, ?, ?, ?)")
                            ->execute([
                                $nuevo_pedido_id, 
                                $prod['producto_id'], 
                                $prod['nombre_producto'], 
                                $prod['cantidad'], 
                                $prod['precio_unitario'], 
                                $prod['iva_porcentaje'] ?? 15, // Valor por defecto 15 si es null
                                $prod['total']
                            ]);
                    }
                    
                    $msg = ' Reemplazo creado. Se enviará un nuevo producto en 24-48h.';
                } else {
                    // Reembolso total
                    $historial[] = ['estado' =>'Incidencia: No Recibido - Reembolso', 'descripcion' =>' Cliente no recibió. Se procesó reembolso total.', 'fecha' =>date('Y-m-d H:i:s'), 'icono' =>'fa-money-bill-wave'];
                    $pdo->prepare("UPDATE pedidos SET estado = 'Reembolsado', confirmacion_cliente_estado = 'no_recibido_reembolso', historial_estados = ?::jsonb WHERE id = ?")
                        ->execute([json_encode($historial), $pedido_id]);
                    $msg = ' Reembolso procesado. El monto será acreditado en 3-5 días.';
                }
            } elseif ($accion === 'confirmar_danado') {
                $historial[] = ['estado' =>'Incidencia: Producto Dañado', 'descripcion' =>'El cliente reportó que el producto llegó dañado o incompleto al confirmar la entrega. Se abre caso de devolución con retorno e inspección antes de ofrecer solución.', 'fecha' =>date('Y-m-d H:i:s'), 'icono' =>'fa-camera'];
                $pdo->prepare("UPDATE pedidos SET estado = 'En Revisión', confirmacion_cliente_estado = 'llego_danado', historial_estados = ?::jsonb WHERE id = ?")
                    ->execute([json_encode($historial, JSON_UNESCAPED_UNICODE), $pedido_id]);

                // Caso C completo: no se resuelve automáticamente. Se crea la devolución/incidencia
                // para que el admin autorice retorno, reciba, inspeccione y luego habilite reembolso/cambio.
                if (function_exists('emxCrearIncidenciaPedido')) {
                    emxCrearIncidenciaPedido(
                        $pdo,
                        $pedido_check,
                        'danado_envio',
                        'Cliente reportó daño o producto incompleto al confirmar la entrega. Flujo: admin revisa, autoriza retorno, almacén recibe, técnico inspecciona y admin ofrece reembolso, cambio o ambas opciones al cliente.',
                        'pendiente_revision',
                        'incidencia_entrega'
                    );
                }

                if (function_exists('emxNotificar')) {
                    emxNotificar($pdo, $user_id, 'incidencia_dano', 'Caso de devolución abierto', 'Abrimos un caso por producto dañado o incompleto. El admin revisará, coordinará el retorno y después de inspección podrás elegir la solución que te habiliten.', 'mi_cuenta.php?seccion=devoluciones', 'devolucion');
                }
                $msg = 'Caso abierto por producto dañado. El admin revisará el retorno y luego podrá ofrecerte reembolso, cambio o ambas opciones.';
                $msg_type = 'warning';
            }
            $pdo->commit();
        } else {
            $msg = 'No se puede procesar esta confirmación en este momento.';
            $msg_type = 'error';
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = 'Error al procesar la confirmación: ' . $e->getMessage();
        $msg_type = 'error';
    }
}

// ============================================
// PROCESAR ACTUALIZACIÓN DE PERFIL
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $telefono = trim($_POST['telefono'] ?? '');
    $cedula = trim($_POST['cedula_ruc'] ?? '');
    $nuevo_email = trim($_POST['email'] ?? '');
    
    try {
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ? AND deleted_at IS NULL");
        $stmt_check->execute([$nuevo_email, $user_id]);
        if ($stmt_check->fetch()) throw new Exception('Este correo ya está registrado por otro usuario.');
        
        $pdo->prepare("UPDATE usuarios SET telefono = ?, cedula_ruc = ?, email = ? WHERE id = ?")->execute([$telefono, $cedula, $nuevo_email, $user_id]);
        $_SESSION['usuario_email'] = $nuevo_email;
        $msg = 'Datos actualizados correctamente.';
    } catch (Exception $e) {
        $msg = $e->getMessage();
        $msg_type = 'error';
    }
}

// ============================================
// PROCESAR SUBIDA DE FOTO DE PERFIL
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_foto'])) {
    try {
        if (!empty($_FILES['foto_perfil']['name'])) {
            $ruta = emxSubirArchivoSeguro('foto_perfil', emxCarpetaPerfilUploads('clientes', $user_id), ['prefijo' =>'user_' . preg_replace('/[^a-z0-9]/i', '', $user_id)]);
            if ($ruta) {
                $stmt = $pdo->prepare("SELECT foto_perfil_url FROM usuarios WHERE id = ?");
                $stmt->execute([$user_id]);
                $old_foto = $stmt->fetchColumn();
                if ($old_foto && file_exists($old_foto)) @unlink($old_foto);
                $pdo->prepare("UPDATE usuarios SET foto_perfil_url = ? WHERE id = ?")->execute([$ruta, $user_id]);
                $msg = 'Foto de perfil actualizada.';
            }
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        $msg_type = 'error';
    }
}

if (isset($_GET['eliminar_foto'])) {
    $stmt = $pdo->prepare("SELECT foto_perfil_url FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $old_foto = $stmt->fetchColumn();
    if ($old_foto && file_exists($old_foto)) unlink($old_foto);
    $pdo->prepare("UPDATE usuarios SET foto_perfil_url = NULL WHERE id = ?")->execute([$user_id]);
    header('Location: mi_cuenta.php?seccion=perfil&msg=Foto+eliminada');
    exit();
}

// ============================================
// PROCESAR CAMBIO DE CONTRASEÑA
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';
    
    try {
        if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) throw new Exception('Todos los campos son obligatorios.');
        if (strlen($password_nueva) < 6) throw new Exception('La nueva contraseña debe tener al menos 6 caracteres.');
        if ($password_nueva !== $password_confirmar) throw new Exception('Las contraseñas nuevas no coinciden.');
        
        $stmt = $pdo->prepare("SELECT password_hash FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($password_actual, $user_data['password_hash'])) throw new Exception('La contraseña actual es incorrecta.');
        
        $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?")->execute([password_hash($password_nueva, PASSWORD_DEFAULT), $user_id]);
        $msg = 'Contraseña actualizada exitosamente.';
    } catch (Exception $e) {
        $msg = $e->getMessage();
        $msg_type = 'error';
    }
}

// ============================================
// PROCESAR GUARDAR DIRECCIÓN
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_direccion'])) {
    try {
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM direcciones_usuario WHERE usuario_id = ?");
        $stmt_count->execute([$user_id]);
        if ($stmt_count->fetchColumn() >= 3) throw new Exception('Ya tienes el máximo de 3 direcciones guardadas.');
        
        $alias = trim($_POST['alias'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $cp = trim($_POST['codigo_postal'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $lat = !empty($_POST['latitud']) ? (float)$_POST['latitud'] : null;
        $lng = !empty($_POST['longitud']) ? (float)$_POST['longitud'] : null;
        
        if (empty($alias) || empty($direccion) || empty($ciudad)) throw new Exception('Campos obligatorios faltantes.');
        
        $pdo->prepare("INSERT INTO direcciones_usuario (usuario_id, alias, direccion, ciudad, codigo_postal, telefono, latitud, longitud) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$user_id, $alias, $direccion, $ciudad, $cp, $telefono, $lat, $lng]);
        $msg = 'Dirección guardada exitosamente.';
    } catch (Exception $e) {
        $msg = $e->getMessage();
        $msg_type = 'error';
    }
}

if (isset($_GET['set_principal'])) {
    $pdo->prepare("UPDATE direcciones_usuario SET es_principal = FALSE WHERE usuario_id = ?")->execute([$user_id]);
    $pdo->prepare("UPDATE direcciones_usuario SET es_principal = TRUE WHERE id = ? AND usuario_id = ?")->execute([$_GET['set_principal'], $user_id]);
    header('Location: mi_cuenta.php?seccion=direcciones&msg=Dirección+principal+actualizada');
    exit();
}

if (isset($_GET['eliminar'])) {
    $pdo->prepare("DELETE FROM direcciones_usuario WHERE id = ? AND usuario_id = ?")->execute([$_GET['eliminar'], $user_id]);
    header('Location: mi_cuenta.php?seccion=direcciones&msg=Dirección+eliminada');
    exit();
}

// ============================================
// CARGAR DATOS ADICIONALES
// ============================================
$plan_actual = null;
if (!empty($user['plan_id'])) {
    $stmt_plan = $pdo->prepare("SELECT * FROM planes WHERE id = ?");
    $stmt_plan->execute([$user['plan_id']]);
    $plan_actual = $stmt_plan->fetch(PDO::FETCH_ASSOC);
    $plan_actual['expirado'] = (!empty($user['plan_expira_en']) && strtotime($user['plan_expira_en']) < time());
}

$stmt_pedidos = $pdo->prepare("SELECT p.*, f.id AS factura_id, f.numero_factura FROM pedidos p LEFT JOIN facturas f ON f.pedido_id = p.id WHERE p.usuario_id = ? ORDER BY p.created_at DESC");
$stmt_pedidos->execute([$user_id]);
$pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);

$stmt_dev = $pdo->prepare("SELECT d.*, p.id as pedido_id_str, p.total as total_pedido FROM devoluciones d JOIN pedidos p ON d.pedido_id = p.id WHERE d.usuario_id = ? ORDER BY d.created_at DESC");
$stmt_dev->execute([$user_id]);
$devoluciones = $stmt_dev->fetchAll(PDO::FETCH_ASSOC);

$stmt_vistos = $pdo->prepare("
    SELECT pv.visto_en, p.id as producto_id, p.nombre, p.precio_base, p.iva_porcentaje, 
           p.descuento_porcentaje, p.descuento_desde, p.descuento_hasta, pm.url as imagen 
    FROM productos_vistos pv 
    JOIN productos p ON pv.producto_id = p.id 
    LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.orden = 1 
    WHERE pv.usuario_id = ? AND p.deleted_at IS NULL 
    ORDER BY pv.visto_en DESC LIMIT 20
");
$stmt_vistos->execute([$user_id]);
$productos_vistos = $stmt_vistos->fetchAll(PDO::FETCH_ASSOC);

$stmt_dirs = $pdo->prepare("SELECT * FROM direcciones_usuario WHERE usuario_id = ? ORDER BY es_principal DESC, created_at DESC");
$stmt_dirs->execute([$user_id]);
$direcciones = $stmt_dirs->fetchAll(PDO::FETCH_ASSOC);

function getEstadoColor($estado) {
    $estados = [
        'Pendiente' =>'bg-amber-50 text-amber-700 border border-amber-200', 'Pago confirmado' =>'bg-sky-50 text-sky-700 border border-sky-200',
        'En Preparación' =>'bg-indigo-50 text-indigo-700 border border-indigo-200', 'Despachado' =>'bg-purple-50 text-purple-700 border border-purple-200',
        'En Tránsito' =>'bg-violet-50 text-violet-700 border border-violet-200', 'En Reparto' =>'bg-pink-50 text-pink-700 border border-pink-200',
        'Entregado' =>'bg-emerald-50 text-emerald-700 border border-emerald-200', 'Cancelado' =>'bg-red-50 text-red-700 border border-red-200',
        'En Revisión' =>'bg-orange-50 text-orange-700 border border-orange-200', 'Reembolsado' =>'bg-cyan-50 text-cyan-700 border border-cyan-200'
    ];
    return $estados[$estado] ?? 'bg-slate-100 text-slate-700 border border-slate-200';
}

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/frontend/mi_cuenta_view.php
require EMX_VIEWS_PATH . '/frontend/mi_cuenta_view.php';
exit;
