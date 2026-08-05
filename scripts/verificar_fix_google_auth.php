<?php
$root = dirname(__DIR__);
$errores = [];

$helper = $root . '/app/Helpers/funciones_google_auth.php';
$controller = $root . '/app/Controllers/Auth/google_auth.php';
$config = $root . '/app/Config/google.php';
$public = $root . '/public/google_auth.php';

foreach ([$helper, $controller, $config, $public] as $file) {
    if (!is_file($file)) {
        $errores[] = 'Falta archivo: ' . str_replace($root . '/', '', $file);
    }
}

if (is_file($helper)) {
    $s = file_get_contents($helper);
    if (strpos($s, "EMX_ROOT . '/config_google.php'") !== false && strpos($s, '$googleConfigNuevo') === false) {
        $errores[] = 'El helper todavía depende de config_google.php en raíz.';
    }
    if (strpos($s, "EMX_CONFIG_PATH") === false || strpos($s, "google.php") === false) {
        $errores[] = 'El helper no carga app/Config/google.php.';
    }
}

if (is_file($controller)) {
    $s = file_get_contents($controller);
    if (strpos($s, "\$action === 'login' || \$action === 'registro'") === false) {
        $errores[] = 'google_auth.php no acepta action=registro.';
    }
    if (strpos($s, 'emxGoogleAutenticar') === false) {
        $errores[] = 'google_auth.php no llama emxGoogleAutenticar.';
    }
}

foreach ([$helper, $controller, $config, $public] as $file) {
    if (is_file($file)) {
        $cmd = 'php -l ' . escapeshellarg($file) . ' 2>&1';
        $out = shell_exec($cmd);
        if (strpos($out, 'No syntax errors detected') === false) {
            $errores[] = "Error de sintaxis en " . str_replace($root . '/', '', $file) . ":
" . $out;
        }
    }
}

echo "Verificación fix Google Auth
";
if ($errores) {
    foreach ($errores as $e) echo "[ERROR] $e
";
    exit(1);
}

echo "OK: Google Auth apunta a app/Config/google.php y acepta login/registro.
";
exit(0);
