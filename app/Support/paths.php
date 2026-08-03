<?php
/**
 * Helper de rutas internas.
 *
 * Este archivo no se carga automáticamente en Fase 2.
 * Su objetivo es dejar preparada una forma única de construir rutas sin repetir strings.
 */

if (!function_exists('emx_path')) {
    function emx_path(string $relative = ''): string {
        $base = defined('EMX_ROOT') ? EMX_ROOT : dirname(__DIR__, 2);
        return rtrim($base . '/' . ltrim($relative, '/'), '/');
    }
}

if (!function_exists('emx_storage_path')) {
    function emx_storage_path(string $relative = ''): string {
        return emx_path('storage/' . ltrim($relative, '/'));
    }
}

if (!function_exists('emx_view_path')) {
    function emx_view_path(string $relative = ''): string {
        return emx_path('views/' . ltrim($relative, '/'));
    }
}

if (!function_exists('emx_config_path')) {
    function emx_config_path(string $relative = ''): string {
        return emx_path('app/Config/' . ltrim($relative, '/'));
    }
}

if (!function_exists('emx_helper_path')) {
    function emx_helper_path(string $relative = ''): string {
        return emx_path('app/Helpers/' . ltrim($relative, '/'));
    }
}

if (!function_exists('emx_middleware_path')) {
    function emx_middleware_path(string $relative = ''): string {
        return emx_path('app/Middleware/' . ltrim($relative, '/'));
    }
}

if (!function_exists('emx_public_path')) {
    function emx_public_path(string $relative = ''): string {
        return emx_path('public/' . ltrim($relative, '/'));
    }
}

if (!function_exists('emx_view_file')) {
    function emx_view_file(string $relative = ''): string {
        return emx_path('views/' . ltrim($relative, '/'));
    }
}
