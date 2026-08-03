<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_HELPERS_PATH . '/funciones_wishlist.php';

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

emxVerificarCsrf();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' =>false, 'message' =>'Debes iniciar sesión para usar la lista de deseos']);
    exit();
}

$producto_id = trim((string)($_POST['producto_id'] ?? ''));
if ($producto_id === '') {
    echo json_encode(['success' =>false, 'message' =>'ID de producto requerido']);
    exit();
}
if (!emxIsUuid($producto_id) && !preg_match('/^[0-9]+$/', $producto_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
    exit();
}

$en_wishlist = estaEnWishlist($pdo, $_SESSION['usuario_id'], $producto_id);

if ($en_wishlist) {
    $success = eliminarDeWishlist($pdo, $_SESSION['usuario_id'], $producto_id);
    $new_state = false;
    $msg = 'Eliminado de tu lista de deseos';
} else {
    $success = agregarAWishlist($pdo, $_SESSION['usuario_id'], $producto_id);
    $new_state = true;
    $msg = 'Agregado a tu lista de deseos';
}

if ($success) {
    echo json_encode(['success' =>true, 'in_wishlist' =>$new_state, 'message' =>$msg]);
} else {
    echo json_encode(['success' =>false, 'message' =>'Error al actualizar la lista de deseos']);
}
?>