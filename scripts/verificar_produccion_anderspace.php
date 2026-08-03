<?php
/**
 * Verificador de preparación para producción anderspace.online.
 *
 * Uso:
 * php scripts/verificar_produccion_anderspace.php
 */

$root = dirname(__DIR__);
$errores = 0;
$avisos = 0;

function emxProdLine($tipo, $msg) {
    echo '[' . $tipo . '] ' . $msg . PHP_EOL;
}

echo "Verificación producción anderspace.online\n";
echo "Raíz: {$root}\n\n";

$requiredFiles = [
    '.htaccess',
    '.env.example',
    '.env.production.example',
    'bootstrap/app.php',
    'app/Support/env.php',
    'app/Config/database.php',
    'app/Config/mail.php',
    'public/.htaccess',
    'public/index.php',
    'public/router.php',
    'composer.json',
];

foreach ($requiredFiles as $file) {
    if (!is_file($root . '/' . $file)) {
        emxProdLine('ERROR', "Falta {$file}");
        $errores++;
    }
}

$requiredDirs = [
    'app',
    'database',
    'views',
    'public',
    'public/assets',
    'public/uploads',
    'storage',
    'storage/logs',
    'storage/cache',
    'storage/temp',
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($root . '/' . $dir)) {
        emxProdLine('ERROR', "Falta carpeta {$dir}");
        $errores++;
    }
}

foreach (glob($root . '/*.php') ?: [] as $file) {
    emxProdLine('ERROR', 'No debe existir PHP suelto en raíz: ' . basename($file));
    $errores++;
}

foreach (glob($root . '/*.sql') ?: [] as $file) {
    emxProdLine('ERROR', 'No debe existir SQL suelto en raíz: ' . basename($file));
    $errores++;
}

$htaccess = is_file($root . '/.htaccess') ? file_get_contents($root . '/.htaccess') : '';
foreach (['RewriteRule ^(app|bootstrap|database|docs|routes|scripts|storage|views|vendor)', 'public/index.php', 'public/$1'] as $needle) {
    if (strpos($htaccess, $needle) === false) {
        emxProdLine('ERROR', ".htaccess no contiene regla esperada: {$needle}");
        $errores++;
    }
}

$bootstrap = is_file($root . '/bootstrap/app.php') ? file_get_contents($root . '/bootstrap/app.php') : '';
if (strpos($bootstrap, "emxLoadEnv(EMX_ROOT . '/.env')") === false) {
    emxProdLine('ERROR', 'bootstrap/app.php no carga .env');
    $errores++;
}

$extensions = ['pdo', 'pdo_pgsql', 'json', 'mbstring', 'openssl'];
foreach ($extensions as $ext) {
    if (!extension_loaded($ext)) {
        emxProdLine('AVISO', "Extensión PHP recomendada no cargada en este entorno: {$ext}");
        $avisos++;
    }
}

foreach (['public/uploads', 'storage', 'storage/logs', 'storage/cache', 'storage/temp'] as $dir) {
    $path = $root . '/' . $dir;
    if (is_dir($path) && !is_writable($path)) {
        emxProdLine('AVISO', "La carpeta debe ser escribible por Apache en el VPS: {$dir}");
        $avisos++;
    }
}

if (!is_file($root . '/vendor/autoload.php')) {
    emxProdLine('AVISO', 'No existe vendor/autoload.php. Ejecuta composer install para PHPMailer.');
    $avisos++;
}

if (!is_file($root . '/.env')) {
    emxProdLine('AVISO', 'No existe .env todavía. En el VPS copia .env.production.example como .env y edítalo.');
    $avisos++;
}

echo "\n";
if ($errores === 0) {
    echo "Resultado: estructura preparada para producción. Avisos: {$avisos}.\n";
    exit(0);
}

echo "Resultado: se detectaron {$errores} error(es) y {$avisos} aviso(s).\n";
exit(1);
?>
