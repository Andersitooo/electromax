<?php
$root = dirname(__DIR__);
$files = [
    'app/Controllers/Web/index.php',
    'app/Controllers/Api/buscar_sugerencias.php',
    'public/assets/emx_search_autocomplete.js',
];

$ok = true;
echo "Verificación fix búsqueda navbar por parentesco\n";
echo "Raíz: {$root}\n\n";

foreach ($files as $rel) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    if (!is_file($path)) {
        echo "[ERROR] Falta: {$rel}\n";
        $ok = false;
        continue;
    }
    echo "[OK] Existe: {$rel}\n";
    if (substr($rel, -4) === '.php') {
        $cmd = 'php -l ' . escapeshellarg($path) . ' 2>&1';
        $out = shell_exec($cmd);
        if (strpos((string)$out, 'No syntax errors detected') === false) {
            echo "[ERROR] Sintaxis PHP en {$rel}:\n{$out}\n";
            $ok = false;
        } else {
            echo "[OK] Sintaxis PHP: {$rel}\n";
        }
    }
}

$api = file_get_contents($root . '/app/Controllers/Api/buscar_sugerencias.php');
$js = file_get_contents($root . '/public/assets/emx_search_autocomplete.js');
$web = file_get_contents($root . '/app/Controllers/Web/index.php');

$checks = [
    'API permite búsqueda desde 1 letra' => strpos($api, 'mb_strlen($q) < 1') !== false,
    'JS permite autocompletar desde 1 letra' => strpos($js, 'q.length < 1') !== false,
    'API usa ILIKE para mayúsculas/minúsculas' => strpos($api, 'ILIKE') !== false,
    'Web usa ILIKE para mayúsculas/minúsculas' => strpos($web, 'ILIKE') !== false,
    'API tiene búsqueda por similitud pg_trgm si está disponible' => strpos($api, 'similarity(') !== false,
    'Web tiene búsqueda por similitud pg_trgm si está disponible' => strpos($web, 'similarity(') !== false,
    'API verifica si similarity existe antes de usarla' => strpos($api, "to_regprocedure('similarity(text,text)')") !== false,
    'Web verifica si similarity existe antes de usarla' => strpos($web, "to_regprocedure('similarity(text,text)')") !== false,
];

foreach ($checks as $label => $pass) {
    if ($pass) {
        echo "[OK] {$label}\n";
    } else {
        echo "[ERROR] {$label}\n";
        $ok = false;
    }
}

echo "\nResultado: " . ($ok ? 'fix de búsqueda preparado.' : 'hay errores por revisar.') . "\n";
exit($ok ? 0 : 1);
