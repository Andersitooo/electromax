<?php
/**
 * Verificador favicon global.
 *
 * Uso:
 * php scripts/verificar_favicon_global.php
 */

$root = dirname(__DIR__);
$errores = 0;

echo "Verificación favicon global\n";
echo "Raíz: {$root}\n\n";

$required = [
    'public/assets/favicon/favicon.ico',
    'public/assets/favicon/favicon.svg',
    'public/assets/favicon/favicon-16x16.png',
    'public/assets/favicon/favicon-32x32.png',
    'public/assets/favicon/apple-touch-icon.png',
    'public/assets/favicon/android-chrome-192x192.png',
    'public/assets/favicon/android-chrome-512x512.png',
    'public/assets/favicon/site.webmanifest',
    'views/components/favicon.php',
];

foreach ($required as $file) {
    if (!is_file($root . '/' . $file)) {
        echo "[ERROR] Falta {$file}\n";
        $errores++;
    }
}

$views = glob($root . '/views/**/*.php') ?: [];
$sinFavicon = [];
foreach ($views as $view) {
    $txt = file_get_contents($view);
    if (stripos($txt, '<head') !== false && strpos($txt, 'assets/favicon/favicon.ico') === false) {
        $sinFavicon[] = str_replace($root . '/', '', $view);
    }
}

if ($sinFavicon) {
    echo "[ERROR] Hay vistas con <head> sin favicon:\n";
    foreach ($sinFavicon as $v) echo " - {$v}\n";
    $errores++;
}

if ($errores === 0) {
    echo "Resultado: favicon global configurado correctamente.\n";
    exit(0);
}

echo "Resultado: se detectaron {$errores} problema(s).\n";
exit(1);
?>
