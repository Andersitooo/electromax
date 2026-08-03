<?php
/**
 * Verificador fix carrito sin login.
 *
 * Uso:
 * php scripts/verificar_fix_carrito_login.php
 */

$root = dirname(__DIR__);
$errores = 0;

echo "Verificación fix carrito sin login\n";
echo "Raíz: {$root}\n\n";

$api = $root . '/app/Controllers/Api/add_to_cart.php';
$index = $root . '/views/frontend/index_view.php';
$producto = $root . '/views/frontend/producto_view.php';
$auth = $root . '/app/Controllers/Auth/auth.php';

$checks = [
    [$api, 'requires_login'],
    [$api, 'http_response_code(401)'],
    [$api, 'redirect_after_login'],
    [$index, 'data.requires_login'],
    [$index, 'window.location.href = data.login_url'],
    [$producto, 'data.requires_login'],
    [$producto, 'window.location.href = data.login_url'],
    [$auth, 'function emxAuthDestinoSeguro'],
    [$auth, 'redirect_after_login'],
];

foreach ($checks as [$file, $needle]) {
    if (!is_file($file)) {
        echo "[ERROR] Falta archivo: {$file}\n";
        $errores++;
        continue;
    }
    $txt = file_get_contents($file);
    if (strpos($txt, $needle) === false) {
        echo "[ERROR] No se encontró '{$needle}' en " . str_replace($root . '/', '', $file) . "\n";
        $errores++;
    }
}

if ($errores === 0) {
    echo "Resultado: fix carrito/login configurado correctamente.\n";
    exit(0);
}

echo "Resultado: se detectaron {$errores} problema(s).\n";
exit(1);
?>
