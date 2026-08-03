# Mapa de lógica de negocio separada

| Regla | Antes | Ahora |
|---|---|---|
| Precio con IVA | Helper de descuentos | `app/Services/Catalogo/PricingService.php` |
| Descuento normal de producto | Helper de descuentos | `PricingService::descuentoProductoActivoPct()` |
| Descuento por volumen del cliente | Helper de descuentos | `PricingService::descuentoVolumenProducto()` |
| Precio final del carrito | Helper de descuentos | `PricingService::calcularPrecioProductoCarrito()` |
| SKU profesional | Helper auxiliar | `SerialNumberService::generarSKUProfesional()` |
| Número de serie único | Helper auxiliar | `SerialNumberService::generarSerieUnica()` |
| Validación de serie devuelta | Helper auxiliar | `SerialNumberService::validarSerieDevolucion()` |
| Descuento por rango del proveedor | `proveedor.php` | `SupplierCapacityService::calcularDescuentoPorRango()` |
| Normalizar rangos del proveedor | `proveedor.php` | `SupplierCapacityService::normalizarRangosDescuento()` |

## Idea clave

La página ya no debería tener fórmulas importantes dentro del HTML o del controlador.
La página debe pedirle el cálculo a un servicio.
