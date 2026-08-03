<?php
/**
 * Catálogo explicativo de reglas de negocio.
 *
 * Este archivo no reemplaza toda la lógica del proyecto.
 * Sirve como mapa técnico para entender qué reglas existen y en qué módulo viven.
 *
 * Se incluye en Fase 4 para que el proyecto sea más fácil de defender y explicar.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

if (!class_exists('ElectroMaxBusinessRulesReference')) {
class ElectroMaxBusinessRulesReference
{
    /**
     * Devuelve un resumen de reglas principales del sistema.
     *
     * Cada entrada indica:
     * - modulo: parte del negocio.
     * - regla: decisión que toma el sistema.
     * - archivo_actual: dónde vive hoy o qué servicio la está centralizando.
     */
    public static function reglasPrincipales(): array
    {
        return [
            [
                'modulo' => 'Precios',
                'regla' => 'El precio final se calcula con IVA, oferta de producto, descuento por volumen y membresía.',
                'archivo_actual' => 'app/Services/Catalogo/PricingService.php',
            ],
            [
                'modulo' => 'Proveedores',
                'regla' => 'La capacidad de producción define cuántas unidades puede ofrecer un proveedor y qué descuento aplica por rango.',
                'archivo_actual' => 'app/Services/Proveedor/SupplierCapacityService.php',
            ],
            [
                'modulo' => 'Series',
                'regla' => 'Cada unidad física tiene una serie distinta. En reemplazos se conserva la factura original y se asigna nueva serie.',
                'archivo_actual' => 'app/Services/Inventario/SerialNumberService.php y flujo_admin.php',
            ],
            [
                'modulo' => 'Devoluciones',
                'regla' => 'El sistema valida la serie devuelta para detectar errores o posible fraude.',
                'archivo_actual' => 'flujo_admin.php',
            ],
            [
                'modulo' => 'Backorder',
                'regla' => 'Si no hay stock suficiente, el sistema estima abastecimiento con proveedores.',
                'archivo_actual' => 'app/Helpers/funciones_backorder.php',
            ],
            [
                'modulo' => 'Facturación',
                'regla' => 'La factura se genera después de aprobar pago. Un reemplazo del mismo producto no genera nueva factura.',
                'archivo_actual' => 'app/Helpers/funciones_facturacion.php y flujo_admin.php',
            ],
        ];
    }
}
}
?>
