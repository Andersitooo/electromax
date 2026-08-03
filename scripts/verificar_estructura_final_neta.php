<?php
/**
 * Verificador de estructura final neta.
 *
 * Uso:
 * php scripts/verificar_estructura_final_neta.php
 */

$root = dirname(__DIR__);
require_once $root . '/bootstrap/app.php';

$errores = 0;

echo "Verificación estructura final neta\n";
echo "Raíz: {$root}\n\n";

$rootPhp = glob($root . '/*.php') ?: [];
$rootSql = glob($root . '/*.sql') ?: [];

if (count($rootPhp) > 0) {
    $errores++;
    echo "[ERROR] Hay PHP sueltos en raíz:\n";
    foreach ($rootPhp as $f) echo " - " . basename($f) . "\n";
}

if (count($rootSql) > 0) {
    $errores++;
    echo "[ERROR] Hay SQL sueltos en raíz:\n";
    foreach ($rootSql as $f) echo " - " . basename($f) . "\n";
}

foreach (['assets', 'uploads', 'components'] as $legacyDir) {
    if (is_dir($root . '/' . $legacyDir)) {
        $errores++;
        echo "[ERROR] Carpeta legacy en raíz: {$legacyDir}\n";
    }
}

$requiredDirs = [
    'app/Controllers',
    'app/Config',
    'app/Middleware',
    'app/Helpers',
    'app/Services',
    'views',
    'database',
    'public',
    'public/assets',
    'public/uploads',
    'bootstrap',
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($root . '/' . $dir)) {
        $errores++;
        echo "[ERROR] Falta carpeta requerida: {$dir}\n";
    }
}

$requiredFiles = [
    'public/index.php',
    'public/admin.php',
    'public/proveedor.php',
    'public/auth.php',
    'public/router.php',
    'app/Support/net_routes.php',
    'bootstrap/app.php',
];

foreach ($requiredFiles as $file) {
    if (!is_file($root . '/' . $file)) {
        $errores++;
        echo "[ERROR] Falta archivo requerido: {$file}\n";
    }
}

$mapa = require $root . '/app/Support/net_routes.php';
foreach (($mapa['routes'] ?? []) as $route => $controller) {
    if (!is_file($root . '/public/' . $route)) {
        $errores++;
        echo "[ERROR] Falta entrada public/{$route}\n";
    }
    if (!is_file($root . '/' . $controller)) {
        $errores++;
        echo "[ERROR] Falta controlador {$controller}\n";
    }
}

if ($errores === 0) {
    echo "Resultado: estructura final neta correcta. No hay PHP/SQL legacy en raíz.\n";
    exit(0);
}

echo "Resultado: se detectaron {$errores} problema(s).\n";
exit(1);
?>
