<?php
/**
 * Renderizador simple de vistas.
 *
 * Responsabilidad:
 * Cargar archivos de la carpeta views sin mezclar rutas absolutas en cada página.
 *
 * Uso:
 * emx_render_view('frontend/index_view.php', ['productos' => $productos]);
 *
 * Nota de Fase 5:
 * En las páginas grandes se usa require directo para conservar todas las variables
 * existentes sin romper compatibilidad. Este helper queda preparado para fases
 * posteriores donde los controladores pasarán datos de forma más limpia.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

if (!function_exists('emx_render_view')) {
function emx_render_view(string $view, array $data = []): void {
    $view = ltrim($view, '/');
    $file = EMX_ROOT . '/views/' . $view;

    if (!is_file($file)) {
        throw new RuntimeException('Vista no encontrada: ' . $view);
    }

    extract($data, EXTR_SKIP);
    require $file;
}
}
?>
