<?php
$root = dirname(__DIR__);
$checks = [
    'app/Helpers/funciones_ficha_tecnica.php' => [
        'display:table;width:100%;table-layout:fixed' => 'layout PDF en tabla compatible con Dompdf',
        '@page{size:A4 portrait;margin:6mm;}' => 'A4 con margen controlado sin encabezados de navegador',
        'emx-product-image-box{display:table-cell' => 'imagen lateral en PDF',
        'Descargar PDF' => 'botón PDF más claro',
        '.emx-actions{max-width:1120px;margin:28px auto 18px' => 'botones bajados',
        'emxFichaRenderDocumento' => 'renderizador único ficha/PDF',
        'table.emx-spec-table' => 'tabla única de especificaciones',
    ],
    'app/Controllers/Web/ficha_tecnica_pdf.php' => [
        'Dompdf\\Dompdf' => 'controlador usa Dompdf',
        "stream(\$filename, ['Attachment' => true])" => 'PDF se descarga sin imprimir desde navegador',
        "setPaper('A4', 'portrait')" => 'papel A4 vertical',
    ],
];
$ok = true;
foreach ($checks as $file => $needles) {
    $path = $root . '/' . $file;
    if (!is_file($path)) {
        echo "[ERROR] Falta archivo: $file\n";
        $ok = false;
        continue;
    }
    $out = [];
    exec('php -l ' . escapeshellarg($path) . ' 2>&1', $out, $code);
    if ($code !== 0) {
        echo "[ERROR] PHP: $file\n" . implode("\n", $out) . "\n";
        $ok = false;
    } else {
        echo "[OK] PHP: $file\n";
    }
    $content = file_get_contents($path);
    foreach ($needles as $needle => $desc) {
        if (strpos($content, $needle) === false) {
            echo "[ERROR] No se encontró $desc en $file\n";
            $ok = false;
        } else {
            echo "[OK] $desc\n";
        }
    }
}
echo $ok ? "Resultado: ficha técnica y PDF preparados para diseño idéntico.\n" : "Resultado: revisar errores anteriores.\n";
exit($ok ? 0 : 1);
