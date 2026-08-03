<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_ROOT . '/app/Services/Proveedor/SupplierCapacityService.php';
emxVerificarCsrfSiPOST();

// ============================================
//  PROTECCIÓN: Solo PROVEEDORES pueden entrar
// ============================================
emxRequireRole(['PROVEEDOR']);
$rol_actual = emxRolActual();

$user_id = $_SESSION['usuario_id'];
$seccion = $_GET['seccion'] ?? 'dashboard';
$msg = $_GET['msg'] ?? null;
$msg_type = $_GET['msg_type'] ?? 'success';

// ============================================
// CARGAR DATOS DEL PROVEEDOR
// ============================================
$stmt_user = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// ============================================
// ⭐ PROCESAR ACTUALIZACIÓN DE PERFIL
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    try {
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $cedula = trim($_POST['cedula_ruc'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if (empty($nombres) || empty($apellidos) || empty($email)) {
            throw new Exception('Nombres, apellidos y email son obligatorios');
        }
        
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt_check->execute([$email, $user_id]);
        if ($stmt_check->fetchColumn()) {
            throw new Exception('Este correo ya está registrado por otro usuario');
        }
        
        if (!empty($password)) {
            if (strlen($password) < 6) throw new Exception('La contraseña debe tener al menos 6 caracteres');
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE usuarios SET nombres=?, apellidos=?, email=?, telefono=?, cedula_ruc=?, password_hash=? WHERE id=?")
                ->execute([$nombres, $apellidos, $email, $telefono, $cedula, $hash, $user_id]);
        } else {
            $pdo->prepare("UPDATE usuarios SET nombres=?, apellidos=?, email=?, telefono=?, cedula_ruc=? WHERE id=?")
                ->execute([$nombres, $apellidos, $email, $telefono, $cedula, $user_id]);
        }
        
        $_SESSION['usuario_nombre'] = $nombres;
        $_SESSION['usuario_email'] = $email;
        
        $stmt_user->execute([$user_id]);
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
        
        $msg = ' Perfil actualizado correctamente';
    } catch (Exception $e) {
        $msg = $e->getMessage();
        $msg_type = 'error';
    }
}

// ============================================
// ⭐ PROCESAR SUBIDA DE FOTO DE PERFIL
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_foto_perfil'])) {
    try {
        if (!empty($_FILES['foto_perfil']['name'])) {
            $ruta = emxSubirArchivoSeguro('foto_perfil', emxCarpetaPerfilUploads('proveedores', $user_id), ['prefijo' =>'prov_' . preg_replace('/[^a-z0-9]/i', '', $user_id)]);
            if ($ruta) {
                if (!empty($user['foto_perfil_url']) && file_exists($user['foto_perfil_url'])) {
                    @unlink($user['foto_perfil_url']);
                }
                $pdo->prepare("UPDATE usuarios SET foto_perfil_url = ? WHERE id = ?")->execute([$ruta, $user_id]);
                $user['foto_perfil_url'] = $ruta;
                $msg = ' Foto de perfil actualizada';
            }
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        $msg_type = 'error';
    }
}


if (!function_exists('emxProveedorCalcularDescuentoRango')) {
function emxProveedorCalcularDescuentoRango($descuentosJson, $cantidad) {
    return ElectroMaxSupplierCapacityService::calcularDescuentoPorRango($descuentosJson, (int)$cantidad);
}
}

// ============================================
// PROCESAR CAPACIDAD DE PRODUCCIÓN (CREAR/EDITAR)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_capacidad'])) {
    try {
        $capacidad_id = $_POST['capacidad_id'] ?? null;
        $producto_id = $_POST['producto_id'] ?? null;
        $capacidad_diaria = (int)($_POST['capacidad_diaria'] ?? 0);
        $capacidad_semanal = (int)($_POST['capacidad_semanal'] ?? 0);
        $capacidad_maxima_pedido = (int)($_POST['capacidad_maxima_pedido'] ?? 0);
        $tiempo_entrega_estandar = (int)($_POST['tiempo_entrega_estandar'] ?? 5);
        $distancia_km = (float)($_POST['distancia_km'] ?? 0);
        $velocidad_promedio_kmh = (float)($_POST['velocidad_promedio_kmh'] ?? 60);
        $tiempo_aduanas_dias = (int)($_POST['tiempo_aduanas_dias'] ?? 0);
        $tasa_defectos_fabrica = (float)($_POST['tasa_defectos_fabrica'] ?? 0.05);
        $unidades_disponibles = (int)($_POST['unidades_disponibles'] ?? 0);
        $proxima_produccion = !empty($_POST['proxima_produccion']) ? $_POST['proxima_produccion'] : null;
        $unidades_proxima_produccion = (int)($_POST['unidades_proxima_produccion'] ?? 0);
        
        $descuentos_volumen = [];
        if (!empty($_POST['descuentos_volumen'])) {
            foreach ($_POST['descuentos_volumen'] as $rango) {
                $cantidad_min = (int)($rango['cantidad_min'] ?? 0);
                $cantidad_max_raw = $rango['cantidad_max'] ?? '';
                $descuento = (float)($rango['descuento'] ?? 0);
                
                if ($cantidad_min >0 && $descuento >0) {
                    $cantidad_max = ($cantidad_max_raw === '' || strtolower($cantidad_max_raw) === 'ilimitado') 
                                    ? null 
                                    : (int)$cantidad_max_raw;
                    
                    $descuentos_volumen[] = [
                        'cantidad_min' =>$cantidad_min,
                        'cantidad_max' =>$cantidad_max,
                        'descuento' =>$descuento
                    ];
                }
            }
        }
        
        if (!$producto_id) throw new Exception('Debes seleccionar un producto');
        if ($capacidad_diaria <= 0) throw new Exception('La capacidad diaria debe ser mayor a 0');
        
        if ($capacidad_id) {
            $stmtUpdCap = $pdo->prepare("
                UPDATE capacidad_proveedor 
                SET producto_id = ?, capacidad_diaria = ?, capacidad_semanal = ?, capacidad_maxima_pedido = ?,
                    tiempo_entrega_estandar = ?, distancia_km = ?, velocidad_promedio_kmh = ?,
                    tiempo_aduanas_dias = ?, tasa_defectos_fabrica = ?, unidades_disponibles = ?,
                    proxima_produccion = ?, unidades_proxima_produccion = ?,
                    descuentos_volumen = ?::jsonb, updated_at = NOW()
                WHERE id = ? AND proveedor_id = ?
            ");
            $stmtUpdCap->execute([
                $producto_id, $capacidad_diaria, $capacidad_semanal, $capacidad_maxima_pedido,
                $tiempo_entrega_estandar, $distancia_km, $velocidad_promedio_kmh,
                $tiempo_aduanas_dias, $tasa_defectos_fabrica, $unidades_disponibles,
                $proxima_produccion, $unidades_proxima_produccion,
                json_encode($descuentos_volumen), $capacidad_id, $user_id
            ]);
            if ($stmtUpdCap->rowCount() === 0) {
                throw new Exception('No se pudo actualizar la capacidad. Verifica que pertenezca a tu cuenta de proveedor.');
            }
            $msg = 'Capacidad de producción actualizada correctamente';
        } else {
            $pdo->prepare("
                INSERT INTO capacidad_proveedor 
                (proveedor_id, producto_id, capacidad_diaria, capacidad_semanal, capacidad_maxima_pedido,
                 tiempo_entrega_estandar, distancia_km, velocidad_promedio_kmh, tiempo_aduanas_dias,
                 tasa_defectos_fabrica, unidades_disponibles, proxima_produccion, unidades_proxima_produccion,
                 descuentos_volumen)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?::jsonb)
            ")->execute([
                $user_id, $producto_id, $capacidad_diaria, $capacidad_semanal, $capacidad_maxima_pedido,
                $tiempo_entrega_estandar, $distancia_km, $velocidad_promedio_kmh, $tiempo_aduanas_dias,
                $tasa_defectos_fabrica, $unidades_disponibles, $proxima_produccion, $unidades_proxima_produccion,
                json_encode($descuentos_volumen)
            ]);
            $msg = 'Nueva capacidad de producción registrada correctamente';
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        $msg_type = 'error';
    }
}

// ============================================
// ⭐ PROCESAR PROPUESTA (MAPEADO A COLUMNAS REALES DE LA BD)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_propuesta'])) {
    try {
        $solicitud_id = $_POST['solicitud_id'] ?? null;
        $notas = trim($_POST['notas'] ?? '');
        
        $cantidad_ofrecida = 0;
        $dias_entrega = 0;
        $precio_unitario = 0.0;
        $calendario_entregas = [];
        
        // 1. Si enviaron oferta completa
        if (!empty($_POST['oferta_completa']['cantidad']) && !empty($_POST['oferta_completa']['dias']) && !empty($_POST['oferta_completa']['precio'])) {
            $cantidad_ofrecida = (int)$_POST['oferta_completa']['cantidad'];
            $dias_entrega = (int)$_POST['oferta_completa']['dias'];
            $precio_unitario = (float)$_POST['oferta_completa']['precio'];
            
            // Para oferta completa, el calendario es un solo lote
            $fecha_entrega = date('Y-m-d', strtotime("+$dias_entrega days"));
            $calendario_entregas[] = [
                'fecha' =>$fecha_entrega,
                'unidades' =>$cantidad_ofrecida
            ];
        }
        
        // 2. Si enviaron oferta parcial (sobreescribe o complementa la lógica)
        if (!empty($_POST['oferta_parcial']['lotes']) && !empty($_POST['oferta_parcial']['precio'])) {
            $lotes = [];
            $total_unidades_parcial = 0;
            $precio_unitario = (float)$_POST['oferta_parcial']['precio'];
            $max_dias = 0;
            
            foreach ($_POST['oferta_parcial']['lotes'] as $lote) {
                if (!empty($lote['fecha']) && !empty($lote['unidades'])) {
                    $lotes[] = [
                        'fecha' =>$lote['fecha'],
                        'unidades' =>(int)$lote['unidades']
                    ];
                    $total_unidades_parcial += (int)$lote['unidades'];
                    
                    // Calcular días desde hoy hasta esta fecha
                    $dias_desde_hoy = (int)ceil((strtotime($lote['fecha']) - time()) / 86400);
                    if ($dias_desde_hoy >$max_dias) {
                        $max_dias = $dias_desde_hoy;
                    }
                }
            }
            
            if (count($lotes) >0) {
                $cantidad_ofrecida = $total_unidades_parcial;
                $dias_entrega = $max_dias >0 ? $max_dias : 1;
                $calendario_entregas = $lotes;
            }
        }
        
        if (!$solicitud_id) throw new Exception('Solicitud no válida');
        if ($cantidad_ofrecida <= 0) throw new Exception('Debes especificar la cantidad de unidades a ofrecer');
        if ($dias_entrega <= 0) throw new Exception('Debes especificar los días de entrega');
        if ($precio_unitario <= 0) throw new Exception('El precio unitario debe ser mayor a 0');
        
        // Verificar que no haya enviado propuesta antes
        $stmt_check = $pdo->prepare("SELECT id FROM propuestas_proveedor WHERE solicitud_id = ? AND proveedor_id = ?");
        $stmt_check->execute([$solicitud_id, $user_id]);
        if ($stmt_check->fetchColumn()) {
            throw new Exception('Ya enviaste una propuesta para esta solicitud');
        }
        
        $descuento_configurado = 0.0;
        try {
            $stSolProd = $pdo->prepare("SELECT producto_id FROM solicitudes_reabastecimiento WHERE id = ? LIMIT 1");
            $stSolProd->execute([$solicitud_id]);
            $prodSol = $stSolProd->fetchColumn();
            if ($prodSol) {
                $stCapDesc = $pdo->prepare("SELECT descuentos_volumen FROM capacidad_proveedor WHERE proveedor_id = ? AND producto_id = ? LIMIT 1");
                $stCapDesc->execute([$user_id, $prodSol]);
                $descuento_configurado = emxProveedorCalcularDescuentoRango($stCapDesc->fetchColumn() ?: '[]', $cantidad_ofrecida);
            }
        } catch (Throwable $e) {}

        if ($descuento_configurado > 0 && stripos($notas, 'descuento por rango') === false) {
            $notas = trim($notas . "
Descuento por rango configurado aplicable a {$cantidad_ofrecida} unidades: {$descuento_configurado}%.");
        }

        $precio_total = $cantidad_ofrecida * $precio_unitario;
        
        //  INSERT CORREGIDO usando las columnas que REALMENTE existen en tu BD
        $pdo->prepare("
            INSERT INTO propuestas_proveedor 
            (solicitud_id, proveedor_id, cantidad_ofrecida, dias_entrega, precio_unitario, precio_total, calendario_entregas, notas, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?::jsonb, ?, 'pendiente')
        ")->execute([
            $solicitud_id, 
            $user_id, 
            $cantidad_ofrecida, 
            $dias_entrega, 
            $precio_unitario, 
            $precio_total,
            json_encode($calendario_entregas), 
            $notas
        ]);
        
        // Actualizar estado de la solicitud
        $pdo->prepare("UPDATE solicitudes_reabastecimiento SET estado = 'cotizada' WHERE id = ?")->execute([$solicitud_id]);
        
        $msg = ' Propuesta enviada exitosamente. El admin revisará tus ofertas.';
    } catch (Exception $e) {
        $msg = $e->getMessage();
        $msg_type = 'error';
    }
}

// ============================================
// CARGAR DATOS PARA LA VISTA
// ============================================
$stmt_cap = $pdo->prepare("
    SELECT cp.*, p.nombre as producto_nombre, p.sku as producto_sku
    FROM capacidad_proveedor cp
    JOIN productos p ON cp.producto_id = p.id
    WHERE cp.proveedor_id = ? AND cp.is_active = TRUE
    ORDER BY cp.created_at DESC
");
$stmt_cap->execute([$user_id]);
$capacidades = $stmt_cap->fetchAll(PDO::FETCH_ASSOC);

$stmt_prod = $pdo->prepare("
    SELECT p.id, p.nombre, p.sku, p.stock_actual_global
    FROM productos p
    JOIN producto_proveedor pp ON p.id = pp.producto_id
    WHERE pp.proveedor_id = ? AND p.deleted_at IS NULL AND p.is_active = TRUE
    ORDER BY p.nombre
");
$stmt_prod->execute([$user_id]);
$productos_asignados = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);

$stmt_sol = $pdo->prepare("
    SELECT sr.*, p.nombre as producto_nombre, p.sku as producto_sku,
           (SELECT COUNT(*) FROM propuestas_proveedor WHERE solicitud_id = sr.id) as total_propuestas,
           (SELECT COUNT(*) FROM propuestas_proveedor WHERE solicitud_id = sr.id AND proveedor_id = ?) as mi_propuesta
    FROM solicitudes_reabastecimiento sr
    JOIN productos p ON sr.producto_id = p.id
    JOIN producto_proveedor pp ON p.id = pp.producto_id
    WHERE pp.proveedor_id = ? AND sr.estado IN ('pendiente', 'cotizada')
    ORDER BY sr.created_at DESC
");
$stmt_sol->execute([$user_id, $user_id]);
$solicitudes = $stmt_sol->fetchAll(PDO::FETCH_ASSOC);

$stmt_prop = $pdo->prepare("
    SELECT pp.*, sr.cantidad_necesaria, sr.estado as solicitud_estado, sr.fecha_limite,
           p.nombre as producto_nombre, p.sku as producto_sku
    FROM propuestas_proveedor pp
    JOIN solicitudes_reabastecimiento sr ON pp.solicitud_id = sr.id
    JOIN productos p ON sr.producto_id = p.id
    WHERE pp.proveedor_id = ?
    ORDER BY pp.created_at DESC
    LIMIT 50
");
$stmt_prop->execute([$user_id]);
$propuestas_enviadas = $stmt_prop->fetchAll(PDO::FETCH_ASSOC);

$stats = [
    'productos_asignados' =>count($productos_asignados),
    'capacidades_registradas' =>count($capacidades),
    'solicitudes_pendientes' =>count(array_filter($solicitudes, fn($s) =>$s['mi_propuesta'] == 0)),
    'propuestas_enviadas' =>count($propuestas_enviadas),
    'propuestas_aprobadas' =>count(array_filter($propuestas_enviadas, fn($p) =>$p['estado'] === 'aprobada'))
];

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/proveedor/proveedor_view.php
require EMX_VIEWS_PATH . '/proveedor/proveedor_view.php';
exit;
