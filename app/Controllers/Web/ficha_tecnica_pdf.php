<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_CONFIG_PATH . '/company.php';
require_once EMX_HELPERS_PATH . '/funciones_ficha_tecnica.php';

$autoload = EMX_ROOT . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

$id = trim($_GET['id'] ?? '');
if (!emxIsUuid($id)) { http_response_code(400); exit('Producto inválido'); }

$stmt = $pdo->prepare("\n    SELECT p.*, c.nombre AS categoria, m.nombre AS marca, m.pais_origen, pm.url AS imagen\n    FROM productos p\n    LEFT JOIN categorias c ON c.id = p.categoria_id\n    LEFT JOIN marcas m ON m.id = p.marca_id\n    LEFT JOIN producto_multimedia pm ON pm.producto_id = p.id AND pm.orden = 1\n    WHERE p.id = ? AND p.deleted_at IS NULL AND p.is_active = TRUE\n");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$producto) { http_response_code(404); exit('Producto no encontrado'); }

$specs = json_decode($producto['especificaciones_tecnicas'] ?? '{}', true);
$specs = is_array($specs) ? $specs : [];

$datosProducto = [];
foreach ([
    'Modelo' => $producto['modelo'] ?? '',
    'Marca' => $producto['marca'] ?? '',
    'Categoría' => $producto['categoria'] ?? '',
    'Precio base' => isset($producto['precio_base']) ? '$' . number_format((float)$producto['precio_base'], 2) : '',
] as $label => $value) {
    if (trim((string)$value) !== '') $datosProducto[] = [$label, $value];
}

$html = emxFichaRenderDocumento($producto, $specs, $datosProducto, [
    'pdf' => true,
    'logo' => defined('EMX_EMPRESA_LOGO') ? EMX_EMPRESA_LOGO : 'assets/electromax_logo.png',
]);

$filename = 'ficha-tecnica-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($producto['modelo'] ?: $producto['nombre'])) . '.pdf';

if (class_exists('Dompdf\\Dompdf')) {
    $options = new Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->setChroot(EMX_ROOT);

    $dompdf = new Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($filename, ['Attachment' => false]);
    exit;
}

/* Fallback: si todavía no se instaló dompdf, se muestra la misma ficha en HTML.
 * En el VPS ejecuta: composer update dompdf/dompdf --no-dev --optimize-autoloader
 */
header('Content-Type: text/html; charset=UTF-8');
echo $html;
?>
