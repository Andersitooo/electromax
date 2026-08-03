<?php
/**
 * Helper de compatibilidad para descuentos por volumen.
 *
 * Fase 4:
 * La lógica de negocio ya no vive directamente en este helper.
 * Ahora se delega a:
 *
 * app/Services/Catalogo/PricingService.php
 *
 * Motivo:
 * Las páginas antiguas siguen llamando funciones como emxCalcularPrecioProductoCarrito.
 * Para no romper esas llamadas, se conservan las funciones, pero cada una llama
 * al servicio nuevo donde está la regla explicada y comentada.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

require_once EMX_ROOT . '/app/Services/Catalogo/PricingService.php';

if (!function_exists('emxNormalizarPct')) {
function emxNormalizarPct($valor) {
    return ElectroMaxPricingService::normalizarPorcentaje($valor);
}
}

if (!function_exists('emxDescuentoProductoActivoPct')) {
function emxDescuentoProductoActivoPct(array $producto) {
    return ElectroMaxPricingService::descuentoProductoActivoPct($producto);
}
}

if (!function_exists('emxDescuentoVolumenProducto')) {
function emxDescuentoVolumenProducto($rangosJson, $cantidad) {
    return ElectroMaxPricingService::descuentoVolumenProducto($rangosJson, (int)$cantidad);
}
}

if (!function_exists('emxCalcularPrecioProductoCarrito')) {
function emxCalcularPrecioProductoCarrito($pdo, array $producto, int $cantidad, $usuario_id = null) {
    return ElectroMaxPricingService::calcularPrecioProductoCarrito($pdo, $producto, $cantidad, $usuario_id);
}
}
?>
