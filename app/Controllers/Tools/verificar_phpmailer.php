<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

/**
 * Verificador de PHPMailer para ElectroMax.
 * Puedes ejecutarlo en consola: php verificar_phpmailer.php
 * O abrirlo en navegador: http://localhost/electro2/verificar_phpmailer.php
 */
$isCli = PHP_SAPI === 'cli';
if (!$isCli) {
    require_once EMX_MIDDLEWARE_PATH . '/security.php';
    emxRequireRole(['SUPERADMIN', 'ADMIN']);
}
function emxOut($msg) {
    global $isCli;
    echo $isCli ? $msg . PHP_EOL : '<div style="padding:8px 10px;margin:6px 0;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;font-family:Arial">' . $msg . '</div>';
}
if (!$isCli) {
    echo '<!doctype html><meta charset="utf-8"><title>Verificar PHPMailer - ElectroMax</title>';
    echo '<body style="font-family:Arial;background:#eef4ff;padding:24px;color:#0f172a">';
    echo '<div style="max-width:860px;margin:auto;background:white;border-radius:20px;padding:24px;box-shadow:0 20px 50px rgba(15,23,42,.12)">';
    echo '<h1 style="margin-top:0">ElectroMax - Verificación de correo</h1>';
}

$autoload = EMX_ROOT . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!is_file($autoload)) {
    emxOut(' No existe vendor/autoload.php. Falta instalar PHPMailer. Ejecuta instalar_phpmailer_windows.bat.');
    if (!$isCli) echo '</div><script src="assets/emx_modales.js"></script></body>';
    exit(1);
}
require_once $autoload;

if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    emxOut(' vendor/autoload.php existe, pero la clase PHPMailer no carga. Reinstala con Composer.');
    if (!$isCli) echo '</div></body>';
    exit(1);
}
emxOut(' PHPMailer está instalado y carga correctamente.');

$config = EMX_ROOT . DIRECTORY_SEPARATOR . 'config_correo.php';
if (is_file($config)) {
    require_once $config;
    emxOut(' config_correo.php existe.');
} else {
    emxOut(' No existe config_correo.php. Copia config_correo.example.php como config_correo.php o usa el archivo incluido en el ZIP.');
}

$placeholders = [
    'smtp.tudominio.com',
    'facturacion@tudominio.com',
    'TU_PASSWORD_O_APP_PASSWORD',
    'CAMBIA_ESTA_PASSWORD_SMTP',
    'tu_correo_de_prueba@tudominio.com',
];
$required = [
    'EMX_SMTP_HOST',
    'EMX_SMTP_PORT',
    'EMX_SMTP_SECURE',
    'EMX_SMTP_USER',
    'EMX_SMTP_PASS',
    'EMX_SMTP_FROM_EMAIL',
    'EMX_SMTP_FROM_NAME',
];
$okReal = true;
foreach ($required as $key) {
    $val = getenv($key);
    if ($val === false || trim($val) === '') {
        emxOut(' Falta configurar: ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8'));
        $okReal = false;
    } elseif (in_array(trim($val), $placeholders, true)) {
        emxOut(' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . ' todavía tiene valor de ejemplo.');
        $okReal = false;
    } else {
        $mostrar = $key === 'EMX_SMTP_PASS' ? str_repeat('*', min(10, strlen($val))) : $val;
        emxOut(' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . ': ' . htmlspecialchars($mostrar, ENT_QUOTES, 'UTF-8'));
    }
}

$modoPrueba = getenv('EMX_MAIL_MODO_PRUEBA') ?: '0';
$correoPrueba = getenv('EMX_MAIL_CORREO_PRUEBA') ?: '';
if ($modoPrueba === '1') {
    emxOut('ℹ Modo prueba activado. Los correos reales se redirigirán a: ' . htmlspecialchars($correoPrueba, ENT_QUOTES, 'UTF-8'));
}

if ($okReal) {
    emxOut(' Resultado: configuración lista para enviar facturas por correo.');
    emxOut('Para probar envío real desde consola: php probar_correo_facturacion.php tu_correo@dominio.com');
} else {
    emxOut('Resultado: mientras falten datos reales, las facturas se guardarán en email_outbox como simulación.');
}

if (!$isCli) {
    echo '<p style="margin-top:20px;color:#475569">No compartas config_correo.php públicamente porque contiene contraseña SMTP.</p>';
    echo '</div></body>';
}
