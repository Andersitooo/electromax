<?php
$root = dirname(__DIR__);
$checks = [
    'views/components/navbar.php' => [
        '$activeCategoriaSlug',
        'data-emx-search-form autocomplete="off"',
        '$catActiva',
        'aria-current="page"',
    ],
    'app/Helpers/funciones_home.php' => [
        'function emxObtenerRecomendadosProducto',
        '$stmt->execute([$categoriaId, $marcaId, $precio, $precio, $productoId]);',
        '$stmt->execute([$productoId, $producto[\'categoria_id\'] ?? null, $producto[\'marca_id\'] ?? null]);',
    ],
    'app/Controllers/Web/producto.php' => [
        'Última red de seguridad',
        '$stmt_rel_fallback->execute',
    ],
    'views/frontend/producto_view.php' => [
        'Ajustes finales de responsividad',
        'const related = document.querySelector',
    ],
    'views/frontend/index_view.php' => [
        'Ajustes finales de responsividad para móvil',
    ],
];
$ok = true;
foreach ($checks as $file => $needles) {
    $path = $root . '/' . $file;
    if (!is_file($path)) {
        echo "[ERROR] No existe $file\n";
        $ok = false;
        continue;
    }
    $content = file_get_contents($path);
    foreach ($needles as $needle) {
        if (strpos($content, $needle) === false) {
            echo "[ERROR] Falta '$needle' en $file\n";
            $ok = false;
        }
    }
}
$lintFiles = [
    'views/components/navbar.php',
    'app/Helpers/funciones_home.php',
    'app/Controllers/Web/producto.php',
    'views/frontend/producto_view.php',
    'views/frontend/index_view.php',
];
foreach ($lintFiles as $file) {
    $cmd = 'php -l ' . escapeshellarg($root . '/' . $file) . ' 2>&1';
    exec($cmd, $out, $code);
    if ($code !== 0) {
        echo "[ERROR PHP] $file\n" . implode("\n", $out) . "\n";
        $ok = false;
    }
}
if ($ok) {
    echo "OK: fix responsive, navegación activa y recomendados verificado.\n";
    exit(0);
}
exit(1);
