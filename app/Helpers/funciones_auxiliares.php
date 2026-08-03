<?php
/**
 * Helper de compatibilidad para SKU y números de serie.
 *
 * Fase 4:
 * La lógica se separó en:
 *
 * app/Services/Inventario/SerialNumberService.php
 *
 * Las funciones antiguas se conservan para que no se rompan llamadas existentes.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

require_once EMX_ROOT . '/app/Services/Inventario/SerialNumberService.php';

if (!function_exists('generarSKUProfesional')) {
function generarSKUProfesional($categoria_slug) {
    return ElectroMaxSerialNumberService::generarSKUProfesional($categoria_slug);
}
}

if (!function_exists('generarSerieUnica')) {
function generarSerieUnica($marca) {
    return ElectroMaxSerialNumberService::generarSerieUnica($marca);
}
}

if (!function_exists('validarSerieDevolucion')) {
function validarSerieDevolucion($pdo, $pedido_id, $producto_id, $serie_devuelta) {
    return ElectroMaxSerialNumberService::validarSerieDevolucion($pdo, $pedido_id, $producto_id, $serie_devuelta);
}
}
?>
