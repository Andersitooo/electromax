<?php
/**
 * Herramienta opcional para futuro: NO se ejecuta desde navegador.
 * Por ahora solo informa imágenes de productos que están fuera de la nueva estructura.
 * Uso:
 *   php scripts/reorganizar_uploads_existentes.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Esta herramienta solo se ejecuta por consola.');
}
require_once __DIR__ . '/../seguridad.php';
require_once __DIR__ . '/../db.php';

$stmt = $pdo->query("SELECT pm.id, pm.producto_id, pm.url, p.nombre, p.sku FROM producto_multimedia pm JOIN productos p ON p.id = pm.producto_id WHERE pm.url IS NOT NULL ORDER BY p.nombre, pm.orden");
$fuera = 0;
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $url = str_replace('\\', '/', $row['url']);
    $esperado = emxCarpetaProductoUploads($pdo, $row['producto_id']);
    if (!str_starts_with($url, $esperado . '/')) {
        $fuera++;
        echo "[PENDIENTE] {$row['nombre']} | {$url}\n";
        echo "           nueva carpeta sugerida: {$esperado}\n";
    }
}
echo "\nTotal de imágenes fuera de la estructura nueva: {$fuera}\n";
echo "No se movió nada. Mover archivos requiere actualizar producto_multimedia.url en la base de datos.\n";
