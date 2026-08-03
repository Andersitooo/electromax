<?php
/**
 * Cargador opcional de helpers.
 *
 * No se carga automáticamente en Fase 3 para evitar redeclaraciones en archivos
 * que todavía hacen `require_once` individual.
 *
 * Uso recomendado en fases posteriores:
 * require_once EMX_HELPERS_PATH . '/helpers_loader.php';
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

$emx_helpers = [
    EMX_ROOT . '/app/Helpers/funciones_automatizacion.php',
    EMX_ROOT . '/app/Helpers/funciones_auxiliares.php',
    EMX_ROOT . '/app/Helpers/funciones_backorder.php',
    EMX_ROOT . '/app/Helpers/funciones_descuentos_volumen.php',
    EMX_ROOT . '/app/Helpers/funciones_facturacion.php',
    EMX_ROOT . '/app/Helpers/funciones_ficha_tecnica.php',
    EMX_ROOT . '/app/Helpers/funciones_garantias.php',
    EMX_ROOT . '/app/Helpers/funciones_google_auth.php',
    EMX_ROOT . '/app/Helpers/funciones_home.php',
    EMX_ROOT . '/app/Helpers/funciones_logistica.php',
    EMX_ROOT . '/app/Helpers/funciones_notificaciones.php',
    EMX_ROOT . '/app/Helpers/funciones_planes.php',
    EMX_ROOT . '/app/Helpers/funciones_soporte.php',
    EMX_ROOT . '/app/Helpers/funciones_stock.php',
    EMX_ROOT . '/app/Helpers/funciones_wishlist.php',
];

foreach ($emx_helpers as $helper) {
    if (is_file($helper)) {
        require_once $helper;
    }
}
?>
