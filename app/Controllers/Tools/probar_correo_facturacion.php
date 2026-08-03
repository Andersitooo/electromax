<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

/**
 * Prueba de envío SMTP ElectroMax.
 * Uso desde consola:
 *   php probar_correo_facturacion.php destino@correo.com
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ejecuta esta prueba desde consola: php probar_correo_facturacion.php destino@correo.com');
}

$destino = $argv[1] ?? '';
if (!filter_var($destino, FILTER_VALIDATE_EMAIL)) {
    exit("Indica un correo destino válido. Ejemplo: php probar_correo_facturacion.php cliente@correo.com\n");
}

require_once EMX_CONFIG_PATH . '/mail.php';
$autoload = EMX_ROOT . '/vendor/autoload.php';
if (!is_file($autoload)) {
    exit("No existe vendor/autoload.php. Instala PHPMailer primero.\n");
}
require_once $autoload;

$host = getenv('EMX_SMTP_HOST') ?: '';
$user = getenv('EMX_SMTP_USER') ?: '';
$pass = getenv('EMX_SMTP_PASS') ?: '';
$from = getenv('EMX_SMTP_FROM_EMAIL') ?: $user;
$fromName = getenv('EMX_SMTP_FROM_NAME') ?: 'ElectroMax';

$placeholders = ['smtp.tudominio.com', 'facturacion@tudominio.com', 'CAMBIA_ESTA_PASSWORD_SMTP'];
if (in_array($host, $placeholders, true) || in_array($user, $placeholders, true) || in_array($pass, $placeholders, true)) {
    exit("config_correo.php todavía tiene datos de ejemplo. Cámbialos antes de probar envío real.\n");
}

try {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->SMTPAuth = true;
    $mail->Username = $user;
    $mail->Password = $pass;
    $secure = strtolower(getenv('EMX_SMTP_SECURE') ?: 'tls');
    $mail->SMTPSecure = $secure === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)(getenv('EMX_SMTP_PORT') ?: 587);
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($from, $fromName);
    $mail->addAddress($destino);
    $bcc = getenv('EMX_SMTP_BCC_EMPRESA') ?: '';
    if (filter_var($bcc, FILTER_VALIDATE_EMAIL)) $mail->addBCC($bcc);
    $mail->isHTML(true);
    $mail->Subject = 'Prueba de correo ElectroMax';
    $mail->Body = '<h2>ElectroMax</h2><p>Si recibiste este correo, PHPMailer y SMTP están funcionando correctamente.</p>';
    $mail->AltBody = 'ElectroMax: si recibiste este correo, PHPMailer y SMTP funcionan correctamente.';
    $mail->send();
    echo "Correo de prueba enviado a {$destino}\n";
} catch (Throwable $e) {
    echo "No se pudo enviar: " . $e->getMessage() . "\n";
    exit(1);
}
