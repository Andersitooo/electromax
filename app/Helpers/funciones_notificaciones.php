<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_notificaciones.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

// funciones_notificaciones.php

function enviarNotificacionCliente($pdo, $usuario_id, $tipo, $titulo, $mensaje, $enlace = '#', $tipo_enlace = 'ninguno') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace_accion, tipo_enlace, leida, creado_en)
            VALUES (?, ?, ?, ?, ?, ?, FALSE, NOW())
        ");
        return $stmt->execute([$usuario_id, $tipo, $titulo, $mensaje, $enlace, $tipo_enlace]);
    } catch (Exception $e) {
        error_log("Error al enviar notificación: " . $e->getMessage());
        return false;
    }
}
?>