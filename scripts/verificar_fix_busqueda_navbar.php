<?php
$root = dirname(__DIR__);
$files = [
    $root . '/app/Controllers/Web/index.php',
    $root . '/app/Controllers/Api/buscar_sugerencias.php',
    $root . '/public/assets/emx_search_autocomplete.js',
    $root . '/views/components/navbar.php',
];

$errors = [];
foreach ($files as $file) {
    if (!is_file($file)) {
        $errors[] = "No existe: {$file}";
    }
}

$index = is_file($files[0]) ? file_get_contents($files[0]) : '';
$api = is_file($files[1]) ? file_get_contents($files[1]) : '';
$js = is_file($files[2]) ? file_get_contents($files[2]) : '';
$nav = is_file($files[3]) ? file_get_contents($files[3]) : '';

foreach ([':busq_nombre', ':score_exact_nombre', ':score_prefix_nombre'] as $needle) {
    if (strpos($index, $needle) === false) {
        $errors[] = "index.php no contiene {$needle}";
    }
}

foreach ([':match_nombre', ':score_exact_nombre', ':cat_match', ':marca_match'] as $needle) {
    if (strpos($api, $needle) === false) {
        $errors[] = "buscar_sugerencias.php no contiene {$needle}";
    }
}

if (strpos($js, 'buscar_sugerencias.php?q=') === false) {
    $errors[] = 'El JS de autocomplete no llama a buscar_sugerencias.php?q=';
}
if (strpos($nav, 'data-emx-search-form') === false || strpos($nav, 'data-emx-search-input') === false) {
    $errors[] = 'El navbar no conserva los atributos de búsqueda/autocomplete.';
}

if ($errors) {
    echo "Resultado: ERROR\n";
    foreach ($errors as $e) echo "- {$e}\n";
    exit(1);
}

echo "Resultado: OK\n";
echo "Búsqueda navbar corregida: parámetros nombrados y autocomplete conservado.\n";
