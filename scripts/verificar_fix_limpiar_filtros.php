<?php
/**
 * Verificador fix limpiar filtros sin redirección.
 *
 * Uso:
 * php scripts/verificar_fix_limpiar_filtros.php
 */

$root = dirname(__DIR__);
$errores = 0;

echo "Verificación limpiar filtros sin redirección\n";
echo "Raíz: {$root}\n\n";

$index = $root . '/views/frontend/index_view.php';

if (!is_file($index)) {
    echo "[ERROR] Falta views/frontend/index_view.php\n";
    exit(1);
}

$txt = file_get_contents($index);

$required = [
    'function borrarFiltros(event)',
    'event.preventDefault()',
    'onclick="borrarFiltros(event)"',
    '<?php if ($categoria_actual_id): ?><button type="button" onclick="borrarFiltros(event)"',
    'estrella<?= $i === 1 ? "" : "s" ?>',
    'calificacion_exacta: calificacionMin',
];

foreach ($required as $needle) {
    if (strpos($txt, $needle) === false) {
        echo "[ERROR] No se encontró: {$needle}\n";
        $errores++;
    }
}

if (strpos($txt, '>o más<') !== false) {
    echo "[ERROR] Todavía aparece 'o más' en el filtro de estrellas.\n";
    $errores++;
}

if ($errores === 0) {
    echo "Resultado: limpiar filtros sin redirección configurado correctamente.\n";
    exit(0);
}

echo "Resultado: se detectaron {$errores} problema(s).\n";
exit(1);
?>
