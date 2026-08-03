<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_automatizacion.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

/**
 * ============================================================================
 * MÁQUINA DE ESTADOS AUTOMATIZADA - ElectroMax
 * ============================================================================
 * Este archivo contiene la lógica para:
 * - Registrar cambios de estado en el historial del pedido
 * - Automatizar el avance de estados basado en tiempo transcurrido
 * - Generar notificaciones (preparado para integración futura)
 * ============================================================================
 */

require_once __DIR__ . '/funciones_logistica.php';

/**
 * Registra un cambio de estado en el historial del pedido (JSONB)
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @param string $pedido_id ID del pedido
 * @param string $nuevo_estado Nuevo estado del pedido
 * @param string $descripcion Descripción del evento
 * @return bool True si se registró correctamente
 */
function registrarCambioEstado($pdo, $pedido_id, $nuevo_estado, $descripcion) {
    try {
        // 1. Obtener historial actual
        $stmt = $pdo->prepare("SELECT historial_estados FROM pedidos WHERE id = ?");
        $stmt->execute([$pedido_id]);
        $historial = json_decode($stmt->fetchColumn() ?: '[]', true);

        // 2. Agregar nuevo evento
        $historial[] = [
            'estado' =>$nuevo_estado,
            'descripcion' =>$descripcion,
            'fecha' =>date('Y-m-d H:i:s'),
            'icono' =>obtenerIconoEstado($nuevo_estado)
        ];

        // 3. Guardar en la BD
        $update = $pdo->prepare("UPDATE pedidos SET estado = ?, historial_estados = ?::jsonb WHERE id = ?");
        $update->execute([$nuevo_estado, json_encode($historial), $pedido_id]);

        // 4. Aquí se integraría el envío de notificaciones (email, SMS, push)
        // enviarNotificacionAlCliente($pedido_id, $nuevo_estado, $descripcion);
        
        return true;
    } catch (Exception $e) {
        error_log("Error al registrar cambio de estado: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el icono correspondiente a cada estado
 * 
 * @param string $estado Estado del pedido
 * @return string Clase de Font Awesome
 */
function obtenerIconoEstado($estado) {
    $iconos = [
        'Pago confirmado' =>'fa-credit-card',
        'En Preparación' =>'fa-box-open',
        'Empacado' =>'fa-box',
        'Despachado' =>'fa-truck-loading',
        'En Tránsito' =>'fa-truck-moving',
        'Llegó al centro de distribución' =>'fa-warehouse',
        'En Reparto' =>'fa-motorcycle',
        'Entregado' =>'fa-check-circle',
        'Cancelado' =>'fa-times-circle',
        'Devolución' =>'fa-undo'
    ];
    return $iconos[$estado] ?? 'fa-info-circle';
}

/**
 * MOTOR DE AUTOMATIZACIÓN: Avanza los estados automáticamente
 * 
 * Se ejecuta cada vez que se carga el tracking de un pedido.
 * Evalúa si ha pasado suficiente tiempo para avanzar al siguiente estado.
 * 
 * Tiempos de simulación (en minutos):
 * - Pago confirmado → En Preparación: 5 min
 * - En Preparación → Empacado: 10 min
 * - Empacado → Despachado: 15 min
 * - Despachado → En Tránsito: 20 min
 * - En Tránsito → Llegó al centro: 30 min
 * - Llegó al centro → En Reparto: 40 min
 * - En Reparto → Entregado: 50 min
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @param string $pedido_id ID del pedido
 * @return void
 */
function procesarAutomatizacionPedido($pdo, $pedido_id) {
    $stmt = $pdo->prepare("SELECT estado, historial_estados, created_at FROM pedidos WHERE id = ?");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido || $pedido['estado'] === 'Entregado' || $pedido['estado'] === 'Cancelado') {
        return; // No hacer nada si ya terminó
    }

    $historial = json_decode($pedido['historial_estados'] ?: '[]', true);
    if (empty($historial)) return;

    $ultimo_evento = end($historial);
    $tiempo_transcurrido_minutos = (time() - strtotime($ultimo_evento['fecha'])) / 60;

    // REGLAS DE AUTOMATIZACIÓN
    // Nota: Estos tiempos son para DEMOSTRACIÓN. En producción, ajustar a tiempos reales.
    
    if ($pedido['estado'] === 'Pago confirmado' && $tiempo_transcurrido_minutos >= 5) {
        registrarCambioEstado($pdo, $pedido_id, 'En Preparación', 'El almacén está preparando tus productos con cuidado.');
    } 
    elseif ($pedido['estado'] === 'En Preparación' && $tiempo_transcurrido_minutos >= 10) {
        registrarCambioEstado($pdo, $pedido_id, 'Empacado', 'Tu pedido fue empacado y está listo para salir.');
    }
    elseif ($pedido['estado'] === 'Empacado' && $tiempo_transcurrido_minutos >= 15) {
        registrarCambioEstado($pdo, $pedido_id, 'Despachado', 'Tu pedido salió de nuestra sucursal y está en ruta.');
    }
    elseif ($pedido['estado'] === 'Despachado' && $tiempo_transcurrido_minutos >= 20) {
        registrarCambioEstado($pdo, $pedido_id, 'En Tránsito', 'Tu pedido está viajando hacia tu ciudad.');
    }
    elseif ($pedido['estado'] === 'En Tránsito' && $tiempo_transcurrido_minutos >= 30) {
        registrarCambioEstado($pdo, $pedido_id, 'Llegó al centro de distribución', 'Tu pedido llegó al centro de distribución de tu ciudad.');
    }
    elseif ($pedido['estado'] === 'Llegó al centro de distribución' && $tiempo_transcurrido_minutos >= 40) {
        registrarCambioEstado($pdo, $pedido_id, 'En Reparto', 'Un repartidor ha sido asignado y lleva tu pedido.');
    }
    elseif ($pedido['estado'] === 'En Reparto' && $tiempo_transcurrido_minutos >= 50) {
        registrarCambioEstado($pdo, $pedido_id, 'Entregado', 'Tu pedido fue entregado correctamente. ¡Gracias por tu compra!');
    }
}

/**
 * Inicia el seguimiento de un nuevo pedido
 * Se llama inmediatamente después de aprobar el pago
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @param string $pedido_id ID del pedido
 * @param array $ruta_logistica Información de la sucursal asignada
 * @param array $tiempo_entrega Información del tiempo estimado
 * @return void
 */
function iniciarSeguimientoPedido($pdo, $pedido_id, $ruta_logistica, $tiempo_entrega) {
    // 1. Generar número de guía
    $numero_guia = generarNumeroGuia();
    
    // 2. Registrar el primer estado
    $descripcion = 'Pago aprobado. ' . $tiempo_entrega['mensaje_logistico'] . '. Despacho desde: ' . $ruta_logistica['sucursal_nombre'];
    
    registrarCambioEstado($pdo, $pedido_id, 'Pago confirmado', $descripcion);
    
    // 3. Actualizar fecha estimada y número de guía
    $update = $pdo->prepare("UPDATE pedidos SET fecha_estimada_entrega = ? WHERE id = ?");
    $update->execute([$tiempo_entrega['fecha_estimada'], $pedido_id]);
}
?>