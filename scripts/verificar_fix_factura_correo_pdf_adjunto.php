<?php
$root = dirname(__DIR__);
$checks = [
    'app/Helpers/funciones_facturacion.php' => [
        'emxFactRelativePdfPath',
        "return 'storage/' . \$tipo",
        'emxFactResolveAttachmentPath',
        '$mail->addAttachment($archivoAdjunto',
        'Archivo adjunto no encontrado',
        'file_put_contents($path, $pdf, LOCK_EX)',
    ],
    'scripts/reparar_facturas_pdf_faltantes.php' => [
        'SELECT f.* FROM facturas f',
        'emxCrearPdfBasico',
        'UPDATE facturas SET pdf_url',
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
    $lint = null;
    if (str_ends_with($file, '.php')) {
        exec('php -l ' . escapeshellarg($path) . ' 2>&1', $out, $code);
        if ($code !== 0) {
            echo "[ERROR] PHP inválido: $file\n" . implode("\n", $out) . "\n";
            $ok = false;
            continue;
        }
        echo "[OK] PHP: $file\n";
    }
    $content = file_get_contents($path);
    foreach ($needles as $needle) {
        if (strpos($content, $needle) === false) {
            echo "[ERROR] No se encontró '$needle' en $file\n";
            $ok = false;
        } else {
            echo "[OK] $needle en $file\n";
        }
    }
}

echo $ok
    ? "Resultado: fix correo/factura PDF adjunto preparado correctamente.\n"
    : "Resultado: revisar errores anteriores.\n";
exit($ok ? 0 : 1);
