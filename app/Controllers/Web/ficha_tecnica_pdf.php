<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_CONFIG_PATH . '/company.php';
require_once EMX_HELPERS_PATH . '/funciones_ficha_tecnica.php';

$id = trim($_GET['id'] ?? '');
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
$html = emxFichaRenderDocumento($producto, $specs, [
    'pdf' => true,
    'standalone' => true,
]);

$autoload = EMX_ROOT . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

$filenameBase = preg_replace('/[^a-z0-9]+/i', '-', strtolower((string)($producto['sku'] ?: $producto['nombre'])));
$filenameBase = trim($filenameBase, '-') ?: 'ficha-tecnica';
$filename = 'ficha-tecnica-' . $filenameBase . '.pdf';

if (class_exists('Dompdf\\Dompdf')) {
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    if (defined('EMX_PUBLIC_PATH')) {
        $options->setChroot([EMX_PUBLIC_PATH, EMX_ROOT]);
    }
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($filename, ['Attachment' => true]);
    exit;
}

// Fallback seguro si Dompdf no está instalado: muestra la misma ficha en HTML.
// Se evita generar PDF manual antiguo para no romper acentos ni mostrar rutas del navegador.
header('Content-Type: text/html; charset=UTF-8');
echo $html;
exit;
