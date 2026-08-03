<?php
/**
 * Verificador fix filtro de estrellas exactas.
 *
 * Uso:
 * php scripts/verificar_fix_filtro_estrellas.php
 */

$root = dirname(__DIR__);
$errores = 0;

echo "Verificación filtro de estrellas exactas\n";
echo "Raíz: {$root}\n\n";

$index = $root . '/views/frontend/index_view.php';
$api = $root . '/app/Controllers/Api/api_filtrar_productos.php';
$api2 = $root . '/app/Controllers/Api/api_guardar_producto.php';

$checks = [
    [$index, 'calificacion_exacta: calificacionMin'],
    [$index, 'estrella<?= $i === 1 ? "" : "s" ?>'],
    [$api, '$calificacion_exacta'],
    [$api, 'promedio_calificacion, 0) < ?'],
    [$api, 'calificacion_exacta_aplicada'],
    [$api2, '$calificacion_exacta'],
    [$api2, 'ps.calificacion_promedio < ?'],
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

if (is_file($index) && strpos(file_get_contents($index), '>o más<') !== false) {
    echo "[ERROR] Todavía aparece el texto 'o más' en el filtro de calificación.\n";
    $errores++;
}

if ($errores === 0) {
    echo "Resultado: filtro de estrellas exactas configurado correctamente.\n";
    exit(0);
}

echo "Resultado: se detectaron {$errores} problema(s).\n";
exit(1);
?>
