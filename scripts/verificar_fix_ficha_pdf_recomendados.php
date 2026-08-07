<?php
$root = dirname(__DIR__);
$files = [
    'app/Helpers/funciones_ficha_tecnica.php',
    'app/Controllers/Web/ficha_tecnica.php',
    'app/Controllers/Web/ficha_tecnica_pdf.php',
    'views/frontend/producto_view.php',
    'composer.json',
];
$ok = true;
foreach ($files as $file) {
    $path = $root . '/' . $file;
    if (!is_file($path)) {
        echo "[ERROR] Falta: {$file}\n";
        $ok = false;
        continue;
    }
    if (str_ends_with($file, '.php')) {
        $cmd = 'php -l ' . escapeshellarg($path) . ' 2>&1';
        exec($cmd, $out, $code);
        if ($code !== 0) {
            echo "[ERROR] Sintaxis PHP en {$file}:\n" . implode("\n", $out) . "\n";
            $ok = false;
        } else {
            echo "[OK] PHP: {$file}\n";
        }
    } else {
        echo "[OK] Archivo: {$file}\n";
    }
}
$checks = [
    'app/Helpers/funciones_ficha_tecnica.php' => ['emxFichaRenderDocumento', 'spec-table', 'DejaVu Sans'],
    'app/Controllers/Web/ficha_tecnica_pdf.php' => ['Dompdf\\Dompdf', 'defaultFont', 'Attachment'],
    'views/frontend/producto_view.php' => ['Selección ElectroMax', 'fa-award', 'Productos recomendados'],
    'composer.json' => ['dompdf/dompdf'],
];
foreach ($checks as $file => $needles) {
    $text = file_get_contents($root . '/' . $file);
    foreach ($needles as $needle) {
        if (!str_contains($text, $needle)) {
            echo "[ERROR] No se encontró '{$needle}' en {$file}\n";
            $ok = false;
        } else {
            echo "[OK] {$needle} en {$file}\n";
        }
    }
}
echo $ok ? "Resultado: fix ficha/PDF/recomendados preparado correctamente.\n" : "Resultado: revisar errores anteriores.\n";
exit($ok ? 0 : 1);
