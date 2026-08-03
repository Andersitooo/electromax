<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
emxVerificarCsrfSiPOST();
require_once EMX_HELPERS_PATH . '/flujo_admin.php';
emxGarantizarColumnasDevoluciones($pdo);

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mi_cuenta.php?seccion=devoluciones');
    exit();
}

$user_id = $_SESSION['usuario_id'];
$dev_id = $_POST['devolucion_id'] ?? null;
$accion = $_POST['accion'] ?? null;

if (!$dev_id || !in_array($accion, ['aceptar', 'rechazar', 'elegir_reembolso', 'elegir_cambio'], true)) {
    header('Location: mi_cuenta.php?seccion=devoluciones&msg=' . urlencode('Acción no válida.') . '&msg_type=error');
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM devoluciones WHERE id = ? AND usuario_id = ? FOR UPDATE");
    $stmt->execute([$dev_id, $user_id]);
    $dev = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dev) {
        throw new Exception('Esta solicitud no existe o no te pertenece.');
    }

    if (($dev['respuesta_usuario'] ?? 'pendiente') !== 'pendiente') {
        throw new Exception('Esta solicitud ya fue respondida.');
    }

    $estadoActual = emxEstadoDevolucionNormalizado($dev['estado']);
    $respuesta_usuario = ($accion === 'rechazar') ? 'rechazada' : 'aceptada';
    $solucion_final = $dev['solucion_propuesta'] ?? null;

    if ($estadoActual === 'esperando_decision_cliente') {
        $oferta = $dev['solucion_propuesta'] ?? '';
        if ($accion === 'rechazar') {
            $nuevo_estado = 'pendiente_revision';
            $descripcion = 'El cliente rechazó las opciones ofrecidas. El caso vuelve a revisión manual.';
            $respuesta_usuario = 'rechazada';
        } elseif ($accion === 'elegir_reembolso' || ($accion === 'aceptar' && $oferta === 'opcion_reembolso')) {
            if (!in_array($oferta, ['opcion_reembolso', 'opcion_reembolso_cambio'], true)) {
                throw new Exception('El reembolso no está disponible para esta devolución.');
            }
            $nuevo_estado = 'cliente_eligio_reembolso';
            $solucion_final = 'reembolso_total';
            $descripcion = 'El cliente eligió reembolso. El admin debe ejecutar el reembolso y generar la nota de crédito.';
        } elseif ($accion === 'elegir_cambio' || ($accion === 'aceptar' && $oferta === 'opcion_cambio')) {
            if (!in_array($oferta, ['opcion_cambio', 'opcion_reembolso_cambio'], true)) {
                throw new Exception('El cambio por el mismo producto no está disponible para esta devolución.');
            }
            $nuevo_estado = 'cliente_eligio_cambio';
            $solucion_final = 'cambio_producto';
            $descripcion = 'El cliente eligió cambio por el mismo producto. El admin debe crear el reemplazo.';
        } else {
            throw new Exception('Debes escoger una de las opciones habilitadas por el admin.');
        }
    } elseif ($accion === 'aceptar') {
        if ($estadoActual === 'autorizada_retorno') {
            $nuevo_estado = 'en_camino_retorno';
            $descripcion = 'El cliente aceptó la autorización de retorno y el producto queda en camino.';
        } else {
            $nuevo_estado = $estadoActual;
            $descripcion = 'El cliente aceptó la solución propuesta por soporte.';
        }
    } else {
        $nuevo_estado = 'pendiente_revision';
        $descripcion = 'El cliente rechazó la solución propuesta. El caso vuelve a revisión manual.';
    }

    $historial = emxAgregarHistorial($dev['historial_estados'] ?? '[]', $nuevo_estado, $descripcion, $accion === 'rechazar' ? 'fa-comment-dots' : 'fa-check');

    $stmtUpdate = $pdo->prepare("UPDATE devoluciones SET respuesta_usuario = ?, estado = ?, solucion_propuesta = COALESCE(NULLIF(?, ''), solucion_propuesta), historial_estados = ?::jsonb, updated_at = NOW() WHERE id = ? AND usuario_id = ?");
    $stmtUpdate->execute([$respuesta_usuario, $nuevo_estado, $solucion_final, $historial, $dev_id, $user_id]);

    $pdo->commit();

    if ($accion === 'elegir_reembolso') {
        $msg = 'Elegiste reembolso. El admin continuará con la nota de crédito y el reembolso.';
    } elseif ($accion === 'elegir_cambio') {
        $msg = 'Elegiste cambio por otro igual. El admin creará el pedido de reemplazo con nueva fecha estimada.';
    } elseif ($accion === 'aceptar') {
        $msg = 'Respuesta registrada. El admin continuará con el siguiente paso del flujo.';
    } else {
        $msg = 'Has rechazado la solución. El caso volvió a revisión manual.';
    }

    header('Location: mi_cuenta.php?seccion=devoluciones&msg=' . urlencode($msg) . '&msg_type=success');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: mi_cuenta.php?seccion=devoluciones&msg=' . urlencode($e->getMessage()) . '&msg_type=error');
    exit();
}
?>