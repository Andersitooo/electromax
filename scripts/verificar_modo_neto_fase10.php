<?php
/**
 * Verificador de modo neto - Fase 10.
 *
 * Uso:
 * php scripts/verificar_modo_neto_fase10.php
 *
 * Revisa que public/ cargue app/Controllers y no los archivos heredados de raíz.
 */

$root = dirname(__DIR__);
require_once $root . '/bootstrap/app.php';

$mapa = require $root . '/app/Support/net_routes.php';
$routes = $mapa['routes'] ?? [];

$errores = 0;

echo "Verificación modo neto Fase 10\n";
echo "Raíz: {$root}\n\n";

foreach ($routes as $ruta => $controller) {
    $publicFile = $root . '/public/' . $ruta;
    $controllerFile = $root . '/' . $controller;

    if (!is_file($publicFile)) {
        echo "[ERROR] Falta entrada pública: public/{$ruta}\n";
        $errores++;
        continue;
    }

    if (!is_file($controllerFile)) {
        echo "[ERROR] Falta controlador: {$controller}\n";
        $errores++;
        continue;
    }

    $publicTxt = file_get_contents($publicFile);
    if (strpos($publicTxt, $controller) === false) {
        echo "[ERROR] public/{$ruta} no apunta al controlador neto {$controller}\n";
        $errores++;
    }

    $controllerTxt = file_get_contents($controllerFile);
    $patronesProhibidos = [
        "require_once 'db.php'",
        "require_once 'seguridad.php'",
        "require_once 'config_google.php'",
        "require_once 'funciones_",
        "require __DIR__ . '/views",
    ];

    foreach ($patronesProhibidos as $patron) {
        if (strpos($controllerTxt, $patron) !== false) {
            echo "[ERROR] {$controller} conserva dependencia heredada: {$patron}\n";
            $errores++;
        }
    }
}

if (!is_dir($root . '/public/assets')) {
    echo "[ERROR] Falta public/assets\n";
    $errores++;
}

if (!is_dir($root . '/public/uploads')) {
    echo "[ERROR] Falta public/uploads\n";
    $errores++;
}

if ($errores === 0) {
    echo "Resultado: modo neto public/app preparado correctamente.\n";
    exit(0);
}

echo "Resultado: se detectaron {$errores} problema(s).\n";
exit(1);
?>
