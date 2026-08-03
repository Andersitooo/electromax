<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_HELPERS_PATH . '/funciones_wishlist.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth.php?action=login');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Marcar como leída si se solicita (para compatibilidad con JS)
if (isset($_GET['marcar_leida'])) {
    $notif_id = (int)$_GET['marcar_leida'];
    marcarNotificacionLeida($pdo, $notif_id, $usuario_id);
    exit(); // Responde vacío para la petición fetch
}

// Marcar todas como leídas
if (isset($_GET['marcar_todas'])) {
    marcarTodasNotificacionesLeidas($pdo, $usuario_id);
    header('Location: notificaciones.php');
    exit();
}

$notificaciones = obtenerNotificaciones($pdo, $usuario_id, 50);
$total_no_leidas = contarNotificacionesNoLeidas($pdo, $usuario_id);

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/frontend/notificaciones_view.php
require EMX_VIEWS_PATH . '/frontend/notificaciones_view.php';
exit;
