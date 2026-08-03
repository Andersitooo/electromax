<?php
/**
 * Configuración centralizada SMTP.
 *
 * Producción:
 * - Usa el archivo .env o variables de entorno del servidor.
 * - No guardes claves reales en repositorios públicos.
 * - Si usas Gmail, usa contraseña de aplicación.
 */

if (!function_exists('emxMailEnvDefault')) {
function emxMailEnvDefault($key, $value) {
    $current = getenv($key);
    if ($current === false || $current === '') {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
}

// Valores por defecto seguros.
// Estos valores NO sobrescriben lo que pongas en .env.
emxMailEnvDefault('EMX_SMTP_HOST', 'smtp.gmail.com');
emxMailEnvDefault('EMX_SMTP_PORT', '587');
emxMailEnvDefault('EMX_SMTP_SECURE', 'tls');

// Deja usuario/clave/remitente en .env para producción.
emxMailEnvDefault('EMX_SMTP_USER', '');
emxMailEnvDefault('EMX_SMTP_PASS', '');
emxMailEnvDefault('EMX_SMTP_FROM_EMAIL', getenv('EMX_SMTP_USER') ?: '');
emxMailEnvDefault('EMX_SMTP_FROM_NAME', 'ElectroMax Facturación');
emxMailEnvDefault('EMX_SMTP_BCC_EMPRESA', '');

// 0 = enviar al correo real del cliente.
// 1 = redirigir todo al correo de prueba.
emxMailEnvDefault('EMX_MAIL_MODO_PRUEBA', '0');
emxMailEnvDefault('EMX_MAIL_CORREO_PRUEBA', '');
?>
