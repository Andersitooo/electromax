<?php
/**
 * Helpers de rutas heredadas - Fase 7.
 *
 * Permiten consultar el mapa de compatibilidad sin abrir manualmente los docs.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

if (!function_exists('emx_legacy_routes')) {
function emx_legacy_routes(): array {
    return require EMX_ROOT . '/app/Support/legacy_routes.php';
}
}

if (!function_exists('emx_legacy_route_target')) {
function emx_legacy_route_target(string $ruta): ?array {
    $mapa = emx_legacy_routes();
    if (isset($mapa['php'][$ruta])) return $mapa['php'][$ruta];
    if (isset($mapa['sql'][$ruta])) return $mapa['sql'][$ruta];
    return null;
}
}

if (!function_exists('emx_legacy_route_exists')) {
function emx_legacy_route_exists(string $ruta): bool {
    return emx_legacy_route_target($ruta) !== null;
}
}
?>
