<?php
/**
 * Router público neto - Fase 10.
 *
 * Permite usar public/ como raíz web sin depender de los archivos PHP heredados
 * que viven en la raíz del proyecto.
 */

require_once dirname(__DIR__) . '/bootstrap/app.php';
chdir(EMX_PUBLIC_PATH);

$mapa = require EMX_APP_PATH . '/Support/net_routes.php';
$routes = $mapa['routes'] ?? [];
$aliases = $mapa['aliases'] ?? [];

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$scriptDir = rtrim($scriptDir, '/');

if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($path, $scriptDir)) {
    $path = substr($path, strlen($scriptDir));
}

$path = trim($path, '/');
$routeKey = $path === '' ? 'index.php' : basename($path);

if (isset($aliases[$path])) {
    $routeKey = $aliases[$path];
} elseif (isset($aliases[$routeKey])) {
    $routeKey = $aliases[$routeKey];
}

if (!isset($routes[$routeKey])) {
    http_response_code(404);
    echo 'Ruta no encontrada en modo neto: ' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    exit;
}

require EMX_ROOT . '/' . $routes[$routeKey];
?>
