<?php
$root = dirname(__DIR__);
$checks = [
    'app/Helpers/funciones_ficha_tecnica.php' => [
        'emx-hero-layout',
        'emx-product-image-box',
        'emx-spec-table',
        'emxFichaRenderDocumento',
        'DejaVu Sans',
    ],
    'app/Controllers/Web/ficha_tecnica.php' => [
        'emxFichaRenderDocumento',
        "'pdf' => false",
    ],
    'app/Controllers/Web/ficha_tecnica_pdf.php' => [
        'Dompdf\\Dompdf',
        "'pdf' => true",
        'setPaper',
        'Attachment',
    ],
    'views/frontend/producto_view.php' => [
        'Productos recomendados',
        'Selección ElectroMax',
        'related-swiper',
        'swiper-wrapper',
        'swiper-button-next-rel',
        'swiper-button-prev-rel',
        'slidesPerView: 4.1',
    ],
    'composer.json' => [
        'dompdf/dompdf',
    ],
];

$ok = true;
foreach ($checks as $file => $needles) {
    $path = $root . DIRECTORY_SEPARATOR . $file;
    if (!is_file($path)) {
        echo "[ERROR] No existe: {$file}\n";
        $ok = false;
        continue;
    }
    $lint = null;
    if (str_ends_with($file, '.php')) {
        exec('php -l ' . escapeshellarg($path) . ' 2>&1', $out, $code);
        if ($code !== 0) {
            echo "[ERROR] PHP inválido: {$file}\n" . implode("\n", $out) . "\n";
            $ok = false;
        } else {
            echo "[OK] PHP: {$file}\n";
        }
    } else {
        echo "[OK] Archivo: {$file}\n";
    }
    $content = file_get_contents($path);
    foreach ($needles as $needle) {
        if (strpos($content, $needle) === false) {
            echo "[ERROR] Falta '{$needle}' en {$file}\n";
            $ok = false;
        } else {
            echo "[OK] {$needle} en {$file}\n";
        }
    }
}

echo $ok ? "Resultado: ficha técnica, PDF y carrusel final preparados correctamente.\n" : "Resultado: revisar errores anteriores.\n";
exit($ok ? 0 : 1);
