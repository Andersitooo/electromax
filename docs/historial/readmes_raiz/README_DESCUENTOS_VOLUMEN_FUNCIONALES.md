# Descuentos por volumen funcionales

Revisión realizada:

## Productos / cliente

Antes:
- El admin sí guardaba `productos.descuentos_volumen_rangos`.
- Pero el carrito no lo estaba usando para calcular el precio.
- Por eso configurar rangos en Productos solo quedaba guardado/visible en admin, pero no afectaba la compra.

Ahora:
- `add_to_cart.php` aplica el rango según la cantidad final del carrito.
- `carrito.php` recalcula al subir/bajar cantidad.
- El descuento se mantiene para checkout porque la sesión del carrito queda recalculada.
- `producto.php` muestra los rangos disponibles al cliente en la ficha del producto.
- Se creó `funciones_descuentos_volumen.php` para centralizar el cálculo.

Orden de aplicación:
```text
precio con IVA
↓
descuento general del producto
↓
descuento por volumen según cantidad
↓
descuento de membresía/plan si aplica
```

Ejemplo:
```text
Rango: 5-10 unidades = 10%
Cliente pone 5 unidades en carrito
Sistema aplica el 10% automáticamente
```

## Proveedores

Ya estaba funcional en la parte automática:
- El proveedor guarda rangos en `capacidad_proveedor.descuentos_volumen`.
- `funciones_stock.php` usa esos rangos al generar cotizaciones simuladas.
- `funciones_backorder.php` usa esos rangos para estimaciones/backorder.

También se añadió una mejora pequeña:
- Cuando el proveedor envía una propuesta manual, el sistema agrega en notas qué descuento por rango configurado aplicaría a esa cantidad.
- No cambia el precio manual del proveedor porque ese precio lo escribe él directamente.

SQL:
```bash
psql -d electro2 -f migracion_descuentos_volumen_funcionales.sql
```
