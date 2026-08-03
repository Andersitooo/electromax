<?php
/**
 * Configuración centralizada SMTP.
 *
 * En producción se recomienda usar variables de entorno.
 * No guardar contraseñas reales en repositorios públicos.
 */

/**
 * Configuración SMTP para ElectroMax.
 * 1) Copia este archivo como config_correo.php
 * 2) Cambia los valores por los datos reales de tu correo.
 * 3) NO subas config_correo.php a repositorios públicos.
 */
putenv('EMX_SMTP_HOST=smtp.tudominio.com');
putenv('EMX_SMTP_PORT=587');
putenv('EMX_SMTP_SECURE=tls'); // tls para 587, ssl para 465
putenv('EMX_SMTP_USER=facturacion@tudominio.com');
putenv('EMX_SMTP_PASS=TU_PASSWORD_O_APP_PASSWORD');
putenv('EMX_SMTP_FROM_EMAIL=facturacion@tudominio.com');
putenv('EMX_SMTP_FROM_NAME=ElectroMax Facturación');
