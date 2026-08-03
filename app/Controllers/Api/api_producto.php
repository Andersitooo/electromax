<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
header('Content-Type: application/json; charset=utf-8');
require_once EMX_CONFIG_PATH . '/database.php';

$id = $_GET['id'] ?? '';
if (empty($id)) {
    echo json_encode(['success' =>false, 'error' =>'ID vacío']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        echo json_encode(['success' =>false, 'error' =>'Producto no encontrado']);
        exit;
    }
    
    $stmt2 = $pdo->prepare("SELECT id, url, tipo, orden FROM producto_multimedia WHERE producto_id = ? ORDER BY orden ASC");
    $stmt2->execute([$id]);
    $imagenes = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    $specs = json_decode($producto['especificaciones_tecnicas'] ?? '{}', true) ?: [];
    
    echo json_encode([
        'success' =>true,
        'producto' =>$producto,
        'imagenes' =>$imagenes,
        'specs' =>$specs
    ]);
} catch(Exception $e) {
    echo json_encode(['success' =>false, 'error' =>$e->getMessage()]);
}