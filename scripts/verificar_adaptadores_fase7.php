<?php
/**
 * Verificador de adaptadores - Fase 7.
 *
 * Uso:
 * php scripts/verificar_adaptadores_fase7.php
 *
 * Este script no se conecta a la base de datos y no ejecuta rutas web.
 * Solo comprueba que los archivos destino existan.
 */

$root = dirname(__DIR__);
require_once $root . '/bootstrap/app.php';

$mapa = require $root . '/app/Support/legacy_routes.php';
$errores = 0;

echo "Verificación de adaptadores Fase 7\n";
echo "Raíz: {$root}\n\n";

foreach ($mapa['php'] as $ruta => $info) {
    $rutaAbs = $root . '/' . $ruta;
    $destino = $info['destino'] ?? '';
    $okRuta = is_file($rutaAbs);
    $okDestino = true;

    if ($destino !== '') {
        $okDestino = is_file($root . '/' . ltrim($destino, '/'));
    }

    if (!$okRuta || !$okDestino) {
        $errores++;
        echo "[ERROR] PHP {$ruta} -> {$destino}\n";
    }
}

foreach ($mapa['sql'] as $ruta => $info) {
    $rutaAbs = $root . '/' . $ruta;
    $destino = $info['destino'] ?? '';
    $okRuta = is_file($rutaAbs);
    $okDestino = $destino !== '' && is_file($root . '/' . ltrim($destino, '/'));

    if (!$okRuta || !$okDestino) {
        $errores++;
        echo "[ERROR] SQL {$ruta} -> {$destino}\n";
    }
}

if ($errores === 0) {
    echo "Resultado: todos los adaptadores tienen archivo origen y destino.\n";
    exit(0);
}

echo "Resultado: se detectaron {$errores} problema(s).\n";
exit(1);
?>
