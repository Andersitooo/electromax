<?php
$root = dirname(__DIR__);
$checks = [
    'app/Helpers/funciones_ficha_tecnica.php' => ['emxFichaRenderDocumento', 'emx-spec-table', 'Información organizada en una sola tabla'],
    'app/Controllers/Web/ficha_tecnica.php' => ['emxFichaRenderDocumento', 'volver'],
    'app/Controllers/Web/ficha_tecnica_pdf.php' => ['Dompdf\\Dompdf', 'setPaper', 'Attachment'],
    'app/Helpers/funciones_home.php' => ['misma categoría', 'Fallback final', 'emxObtenerRecomendadosProducto'],
    'views/frontend/producto_view.php' => ['Selección ElectroMax', 'fa-award', 'Productos recomendados'],
    'composer.json' => ['dompdf/dompdf', '^3.1'],
];
$ok = true;
foreach ($checks as $file => $needles) {
    $path = $root . '/' . $file;
    if (!is_file($path)) {
        echo "[ERROR] Falta archivo: {$file}\n";
        $ok = false;
        continue;
    }
    if (str_ends_with($file, '.php')) {
        exec('php -l ' . escapeshellarg($path), $out, $code);
        echo $code === 0 ? "[OK] PHP: {$file}\n" : "[ERROR] PHP: {$file}\n" . implode("\n", $out) . "\n";
        if ($code !== 0) $ok = false;
    }
    $content = file_get_contents($path);
    foreach ($needles as $needle) {
        if (strpos($content, $needle) === false) {
            echo "[ERROR] No se encontró '{$needle}' en {$file}\n";
            $ok = false;
        } else {
            echo "[OK] {$needle} en {$file}\n";
        }
    }
}
if ($ok) {
    echo "Resultado: ficha técnica, PDF Dompdf y recomendados unificados correctamente.\n";
    exit(0);
}
echo "Resultado: hay errores por corregir.\n";
exit(1);
