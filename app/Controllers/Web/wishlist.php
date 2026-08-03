<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_HELPERS_PATH . '/funciones_wishlist.php';
emxVerificarCsrfSiPOST();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth.php?action=login');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $producto_id = trim((string)($_POST['producto_id'] ?? ''));
    
    if ($action === 'agregar' && $producto_id !== '') {
        agregarAWishlist($pdo, $usuario_id, $producto_id);
        header('Location: wishlist.php?msg=producto_agregado');
        exit();
    } elseif ($action === 'eliminar' && $producto_id !== '') {
        eliminarDeWishlist($pdo, $usuario_id, $producto_id);
        header('Location: wishlist.php?msg=producto_eliminado');
        exit();
    }
}

// Obtener wishlist
$wishlist = obtenerWishlist($pdo, $usuario_id);
$msg = $_GET['msg'] ?? '';

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/frontend/wishlist_view.php
require EMX_VIEWS_PATH . '/frontend/wishlist_view.php';
exit;
