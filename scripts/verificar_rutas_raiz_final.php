<?php
/**
 * Verificador de fix de rutas raíz.
 *
 * Uso:
 * php scripts/verificar_rutas_raiz_final.php
 */

$root = dirname(__DIR__);

$errores = 0;

echo "Verificación de rutas raíz con estructura final\n";
echo "Raíz: {$root}\n\n";

if (!is_file($root . '/.htaccess')) {
    echo "[ERROR] Falta .htaccess en raíz.\n";
    $errores++;
} else {
    $ht = file_get_contents($root . '/.htaccess');
    foreach (['public/index.php', 'public/$1', 'public/assets/$1', 'public/uploads/$1'] as $needle) {
        if (strpos($ht, $needle) === false) {
            echo "[ERROR] .htaccess no contiene regla esperada: {$needle}\n";
            $errores++;
        }
    }
}

foreach (['public/index.php', 'public/auth.php', 'public/admin.php', 'public/proveedor.php', 'public/assets', 'public/uploads'] as $rel) {
    $path = $root . '/' . $rel;
    if (!file_exists($path)) {
        echo "[ERROR] Falta {$rel}\n";
        $errores++;
    }
}

$rootPhp = glob($root . '/*.php') ?: [];
$rootSql = glob($root . '/*.sql') ?: [];

if (count($rootPhp) > 0) {
    echo "[ERROR] Hay PHP en raíz. La estructura final no debe tenerlos.\n";
    foreach ($rootPhp as $f) echo " - " . basename($f) . "\n";
    $errores++;
}

if (count($rootSql) > 0) {
    echo "[ERROR] Hay SQL en raíz. La estructura final no debe tenerlos.\n";
    foreach ($rootSql as $f) echo " - " . basename($f) . "\n";
    $errores++;
}

if ($errores === 0) {
    echo "Resultado: fix correcto. La raíz sigue limpia y .htaccess apunta a public/.\n";
    exit(0);
}

echo "Resultado: se detectaron {$errores} problema(s).\n";
exit(1);
?>
