<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
date_default_timezone_set('America/Guayaquil');
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_HELPERS_PATH . '/flujo_admin.php';

//  PROTECCIÓN
if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth.php?action=login');
    exit();
}

$pedido_id = $_GET['id'] ?? null;
if (!$pedido_id) {
    header('Location: mi_cuenta.php?seccion=pedidos');
    exit();
}

// Obtener el pedido
$stmt = $pdo->prepare("
    SELECT p.*, s.nombre as sucursal_nombre, s.ciudad as sucursal_ciudad
    FROM pedidos p 
    LEFT JOIN sucursales s ON p.sucursal_asignada_id = s.id 
    WHERE p.id = ?
");
$stmt->execute([$pedido_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header('Location: mi_cuenta.php?seccion=pedidos');
    exit();
}

// VALIDACIÓN DE PERMISOS
$es_dueño = ($pedido['usuario_id'] === $_SESSION['usuario_id']);
$es_admin = in_array($_SESSION['usuario_rol'] ?? '', ['ADMIN', 'SUPERADMIN']);

if (!$es_dueño && !$es_admin) {
    header('Location: mi_cuenta.php?seccion=pedidos&msg=No+tienes+acceso+a+este+pedido&msg_type=error');
    exit();
}

// ============================================
// ⭐ PROCESAR ACCIONES (SIMULACIÓN Y CONFIRMACIÓN)
// ============================================
$msg_simulacion = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_tracking'])) {
    emxVerificarCsrf();
    $accion = $_POST['accion_tracking'];
    if (in_array($accion, ['simular_escaneo_courier', 'simular_entrega_courier'], true) && !in_array($_SESSION['usuario_rol'] ?? '', ['ADMIN', 'SUPERADMIN'], true)) {
        header('Location: tracking.php?id=' . urlencode($pedido_id) . '&msg=' . urlencode('Acción restringida a administración.') . '&msg_type=error');
        exit();
    }
    $solucion_elegida = $_POST['solucion_extravio'] ?? null; // Para cuando elige A o B
    
    try {
        $pdo->beginTransaction();
        
        // Obtener historial actual
        $stmt_hist = $pdo->prepare("SELECT historial_estados, estado, confirmacion_cliente_estado FROM pedidos WHERE id = ?");
        $stmt_hist->execute([$pedido_id]);
        $row = $stmt_hist->fetch(PDO::FETCH_ASSOC);
        $historial = json_decode($row['historial_estados'] ?: '[]', true);
        $estado_actual = $row['estado'];
        $estado_confirmacion = $row['confirmacion_cliente_estado'] ?? 'pendiente';
        
        // ==========================================
        // SIMULACIÓN: Escaneo de Courier
        // ==========================================
        if ($accion === 'simular_escaneo_courier') {
            $nuevo_estado = 'En Tránsito';
            if ($estado_actual === 'En Tránsito') $nuevo_estado = 'En Reparto';
            
            $historial[] = [
                'estado' =>$nuevo_estado,
                'descripcion' =>' [SIMULACIÓN] Escaneo de courier registrado. Paquete en movimiento.',
                'fecha' =>date('Y-m-d H:i:s'),
                'icono' =>'fa-barcode'
            ];
            
            $pdo->prepare("UPDATE pedidos SET estado = ?, historial_estados = ?::jsonb WHERE id = ?")
                ->execute([$nuevo_estado, json_encode($historial), $pedido_id]);
            
            // Notificar al cliente
            $pdo->prepare("INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace_accion, tipo_enlace, leida, creado_en) VALUES (?, 'pedido_actualizado', ' Tu pedido está en movimiento', 'Hemos registrado el escaneo de tu paquete. Estado actual: {$nuevo_estado}', 'tracking.php?id={$pedido_id}', 'pedido', FALSE, NOW())")
                ->execute([$pedido['usuario_id']]);
            
            $msg_simulacion = " Escaneo de Courier simulado. Estado: {$nuevo_estado}";
        }
        
        // ==========================================
        // SIMULACIÓN: Entrega Final
        // ==========================================
        elseif ($accion === 'simular_entrega_courier') {
            $nuevo_estado = 'Entregado';
            $fecha_limite = date('Y-m-d H:i:s', strtotime('+7 days'));
            
            $historial[] = [
                'estado' =>'Pendiente Confirmación Cliente',
                'descripcion' =>' [SIMULACIÓN] Courier marcó como entregado. Esperando confirmación del cliente.',
                'fecha' =>date('Y-m-d H:i:s'),
                'icono' =>'fa-box-open'
            ];
            
            $pdo->prepare("UPDATE pedidos SET estado = ?, confirmacion_cliente_estado = 'pendiente', fecha_limite_confirmacion = ?, historial_estados = ?::jsonb WHERE id = ?")
                ->execute([$nuevo_estado, $fecha_limite, json_encode($historial), $pedido_id]);
            
            // Notificar al cliente
            $pdo->prepare("INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace_accion, tipo_enlace, leida, creado_en) VALUES (?, 'confirmacion_requerida', ' Tu pedido fue entregado', 'El courier marcó tu pedido como entregado. Por favor confirma la recepción en las próximas 48h.', 'tracking.php?id={$pedido_id}', 'pedido', FALSE, NOW())")
                ->execute([$pedido['usuario_id']]);
            
            $msg_simulacion = " Entrega simulada. Cliente tiene 7 días para confirmar.";
        }
        
        // ==========================================
        // CLIENTE: Confirma que llegó bien
        // ==========================================
        elseif ($accion === 'confirmar_ok') {
            $historial[] = [
                'estado' =>'Confirmado por Cliente',
                'descripcion' =>' El cliente confirmó la recepción en perfectas condiciones. Garantía de 30 días activada.',
                'fecha' =>date('Y-m-d H:i:s'),
                'icono' =>'fa-check-circle'
            ];
            
            $pdo->prepare("UPDATE pedidos SET confirmacion_cliente_estado = 'confirmado_ok', fecha_confirmacion_cliente = NOW(), historial_estados = ?::jsonb WHERE id = ?")
                ->execute([json_encode($historial), $pedido_id]);
            
            // Notificar al cliente
            $pdo->prepare("INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace_accion, tipo_enlace, leida, creado_en) VALUES (?, 'garantia_activada', ' Garantía activada', 'Tu confirmación fue registrada. Tienes 30 días de garantía desde hoy.', 'mi_cuenta.php?seccion=pedidos', 'pedido', FALSE, NOW())")
                ->execute([$pedido['usuario_id']]);
            
            $msg_simulacion = " Confirmación exitosa. Garantía de 30 días activada.";
        }
        
        // ==========================================
        // CLIENTE: Reporta que NO recibió (Elige A o B)
        // ==========================================
        elseif ($accion === 'confirmar_no_recibido') {
            $historial[] = [
                'estado' =>'Incidencia: No Recibido',
                'descripcion' =>'El cliente reportó que no recibió el paquete. Se abre investigación con courier; no se genera reembolso ni reemplazo automático.',
                'fecha' =>date('Y-m-d H:i:s'),
                'icono' =>'fa-exclamation-triangle'
            ];

            $pdo->prepare("UPDATE pedidos SET estado = 'En Revisión', confirmacion_cliente_estado = 'no_recibido', historial_estados = ?::jsonb WHERE id = ?")
                ->execute([json_encode($historial), $pedido_id]);

            emxCrearIncidenciaPedido($pdo, $pedido, 'no_recibido', 'Cliente reportó no recibido desde tracking. Debe investigarse con courier antes de resolver.', 'investigacion_courier', 'incidencia_courier');

            $pdo->prepare("INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace_accion, tipo_enlace, leida, creado_en) VALUES (?, 'investigacion_courier', 'Caso en investigación', 'Abrimos una investigación con el courier. Te notificaremos cuando el admin resuelva el caso.', 'mi_cuenta.php?seccion=devoluciones', 'devolucion', FALSE, NOW())")
                ->execute([$pedido['usuario_id']]);

            $msg_simulacion = "Caso abierto. El admin revisará la investigación con courier.";
        }
        
        // ==========================================
        // CLIENTE: Reporta que llegó dañado
        // ==========================================
        elseif ($accion === 'confirmar_danado') {
            $historial[] = [
                'estado' =>'Incidencia: Producto Dañado',
                'descripcion' =>'El cliente reportó que el producto llegó dañado o incompleto. Se abre caso de devolución con retorno e inspección antes de ofrecer solución.',
                'fecha' =>date('Y-m-d H:i:s'),
                'icono' =>'fa-camera'
            ];
            
            $pdo->prepare("UPDATE pedidos SET estado = 'En Revisión', confirmacion_cliente_estado = 'llego_danado', historial_estados = ?::jsonb WHERE id = ?")
                ->execute([json_encode($historial), $pedido_id]);

            emxCrearIncidenciaPedido($pdo, $pedido, 'danado_envio', 'Cliente reportó producto dañado o incompleto desde tracking. Flujo: admin revisa, autoriza retorno, almacén recibe, técnico inspecciona y admin ofrece reembolso, cambio o ambas opciones al cliente.', 'pendiente_revision', 'incidencia_entrega');
            
            // Notificar al cliente
            $pdo->prepare("INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace_accion, tipo_enlace, leida, creado_en) VALUES (?, 'incidencia_dano', ' Incidencia reportada', 'Hemos recibido tu reporte de daño. Abrimos un caso de devolución. El admin coordinará retorno, inspección y luego habilitará reembolso, cambio o ambas opciones.', 'mi_cuenta.php?seccion=devoluciones', 'devolucion', FALSE, NOW())")
                ->execute([$pedido['usuario_id']]);
            
            $msg_simulacion = "Daño reportado. Caso de devolución abierto para revisión e inspección.";
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg_simulacion = "Error: " . $e->getMessage();
    }
    
    // Recargar datos actualizados
    $stmt = $pdo->prepare("SELECT p.*, s.nombre as sucursal_nombre FROM pedidos p LEFT JOIN sucursales s ON p.sucursal_asignada_id = s.id WHERE p.id = ?");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ============================================
// LÓGICA DE VISUALIZACIÓN
// ============================================
$historial = json_decode($pedido['historial_estados'] ?: '[]', true);
$estado_actual = $pedido['estado'];
$estado_confirmacion = $pedido['confirmacion_cliente_estado'] ?? 'pendiente';

// Calcular porcentaje de avance
$estados_posibles = ['Pago confirmado', 'En Preparación', 'Despachado', 'En Tránsito', 'En Reparto', 'Entregado'];
$indice_actual = array_search($estado_actual, $estados_posibles);
$porcentaje_avance = $indice_actual !== false ? min(100, round((($indice_actual + 1) / count($estados_posibles)) * 100)) : 10;
if ($estado_actual === 'Entregado' && $estado_confirmacion === 'confirmado_ok') $porcentaje_avance = 100;

// Función para colores de estado
function getEstadoColorTracking($estado) {
    $estados = [
        'Pago confirmado' =>'bg-blue-100 text-blue-800 border-blue-200', 
        'En Preparación' =>'bg-indigo-100 text-indigo-800 border-indigo-200',
        'Despachado' =>'bg-purple-100 text-purple-800 border-purple-200',
        'En Tránsito' =>'bg-violet-100 text-violet-800 border-violet-200',
        'En Reparto' =>'bg-pink-100 text-pink-800 border-pink-200',
        'Entregado' =>'bg-emerald-100 text-emerald-800 border-emerald-200', 
        'Cancelado' =>'bg-red-100 text-red-800 border-red-200',
        'En Revisión' =>'bg-amber-100 text-amber-800 border-amber-200',
        'Reembolsado' =>'bg-cyan-100 text-cyan-800 border-cyan-200'
    ];
    return $estados[$estado] ?? 'bg-slate-100 text-slate-800 border-slate-200';
}

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/frontend/tracking_view.php
require EMX_VIEWS_PATH . '/frontend/tracking_view.php';
exit;
