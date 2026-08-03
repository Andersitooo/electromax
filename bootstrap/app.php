<?php
/**
 * Bootstrap base de ElectroMax.
 *
 * Fase 2: este archivo solo define rutas internas del proyecto.
 * No reemplaza todavía a db.php, seguridad.php ni a las rutas actuales.
 * Se usará en fases posteriores para cargar configuración, servicios y repositorios.
 */

if (!defined('EMX_ROOT')) {
    define('EMX_ROOT', dirname(__DIR__));
}

if (!defined('EMX_APP_PATH')) {
    define('EMX_APP_PATH', EMX_ROOT . '/app');
}

if (!defined('EMX_VIEWS_PATH')) {
    define('EMX_VIEWS_PATH', EMX_ROOT . '/views');
}

if (!defined('EMX_DATABASE_PATH')) {
    define('EMX_DATABASE_PATH', EMX_ROOT . '/database');
}

if (!defined('EMX_STORAGE_PATH')) {
    define('EMX_STORAGE_PATH', EMX_ROOT . '/storage');
}

if (!defined('EMX_PUBLIC_PATH')) {
    define('EMX_PUBLIC_PATH', EMX_ROOT . '/public');
}

if (!defined('EMX_CONFIG_PATH')) {
    define('EMX_CONFIG_PATH', EMX_APP_PATH . '/Config');
}

if (!defined('EMX_HELPERS_PATH')) {
    define('EMX_HELPERS_PATH', EMX_APP_PATH . '/Helpers');
}

if (!defined('EMX_MIDDLEWARE_PATH')) {
    define('EMX_MIDDLEWARE_PATH', EMX_APP_PATH . '/Middleware');
}

require_once EMX_APP_PATH . '/Support/paths.php';
require_once EMX_APP_PATH . '/Support/env.php';
emxLoadEnv(EMX_ROOT . '/.env');
require_once EMX_APP_PATH . '/Support/legacy_helpers.php';

if (!defined('EMX_NET_MODE')) {
    define('EMX_NET_MODE', is_file(EMX_APP_PATH . '/Support/net_routes.php'));
}

if (!defined('EMX_FINAL_NET_STRUCTURE')) {
    define('EMX_FINAL_NET_STRUCTURE', true);
}

return [
    'root' => EMX_ROOT,
    'app' => EMX_APP_PATH,
    'views' => EMX_VIEWS_PATH,
    'database' => EMX_DATABASE_PATH,
    'storage' => EMX_STORAGE_PATH,
    'public' => EMX_PUBLIC_PATH,
    'config' => EMX_CONFIG_PATH,
    'helpers' => EMX_HELPERS_PATH,
    'middleware' => EMX_MIDDLEWARE_PATH,
];
