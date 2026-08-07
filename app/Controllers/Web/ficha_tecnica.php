<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_CONFIG_PATH . '/company.php';
require_once EMX_HELPERS_PATH . '/funciones_ficha_tecnica.php';

$id = trim($_GET['id'] ?? '');
$origen = $_GET['origen'] ?? '';
if (!emxIsUuid($id)) {
    http_response_code(400);
    exit('Producto inválido');
}

$stmt = $pdo->prepare("\n    SELECT p.*, c.nombre AS categoria, m.nombre AS marca, m.pais_origen, pm.url AS imagen\n    FROM productos p\n    LEFT JOIN categorias c ON c.id = p.categoria_id\n    LEFT JOIN marcas m ON m.id = p.marca_id\n    LEFT JOIN producto_multimedia pm ON pm.producto_id = p.id AND pm.tipo = 'FOTO' AND pm.orden = 1\n    WHERE p.id = ? AND p.deleted_at IS NULL AND p.is_active = TRUE\n");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$producto) {
    http_response_code(404);
    exit('Producto no encontrado');
}

$specs = json_decode($producto['especificaciones_tecnicas'] ?? '{}', true);
$specs = is_array($specs) ? $specs : [];
$volver = $origen === 'admin' ? 'admin.php?module=productos' : 'producto.php?id=' . urlencode($id);

echo emxFichaRenderDocumento($producto, $specs, [
    'pdf' => false,
    'standalone' => true,
    'volver' => $volver,
]);
exit;
