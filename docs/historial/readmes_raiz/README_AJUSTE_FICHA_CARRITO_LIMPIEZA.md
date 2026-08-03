# Ajuste de fichas técnicas, carrito y limpieza HTML

## Archivos ajustados

- `producto.php`
- `carrito.php`
- `funciones_backorder.php`
- `ficha_tecnica.php`
- `ficha_tecnica_pdf.php`

## Qué se corrigió

1. **Ficha técnica más clara**
   - Las especificaciones se muestran por grupos.
   - Cada dato aparece en una tarjeta pareja con título y valor.
   - Los arreglos se muestran como etiquetas, no como texto corrido.
   - No se agregan datos ficticios ni datos que no estén en `especificaciones_tecnicas`.
   - La ficha que ve admin usa el mismo diseño que la del cliente.

2. **PDF de ficha técnica**
   - Conserva el logo con protagonismo.
   - Organiza las especificaciones en tarjetas de dos columnas.
   - No imprime campos inventados; solo usa datos reales del producto.

3. **Carrito y estimación de sobrestock**
   - La estimación se activa con cualquier cantidad que supere el stock inmediato, no solo cantidades enormes.
   - Se muestran de forma uniforme: cantidad solicitada, stock inmediato, faltante y proveedores evaluados.
   - Se ofrecen dos opciones: entrega parcial y entrega total.
   - La entrega total muestra el proveedor ganador según score de conveniencia para la empresa.
   - La entrega parcial muestra lotes ordenados con fecha y proveedor.

4. **Cálculo de stock inmediato**
   - Se revisa `productos.stock_actual_global` y `inventario_sucursal`.
   - Para no prometer más de lo disponible, si ambas fuentes existen se usa el menor valor.
   - Si una fuente todavía no está sincronizada, se usa la fuente que tenga dato positivo.

5. **Limpieza de HTML sobrante**
   - Se corrigió el formulario de wishlist en `producto.php`, donde el campo CSRF había quedado dentro del atributo `action`.
   - Se revisó que no queden patrones similares de `emxCsrfCampo()` dentro de atributos HTML.

## Fórmula de proveedor ganador

Menor puntaje = mejor opción para la empresa:

- Costo unitario estimado: 50%.
- Tiempo de entrega: 35%.
- Riesgo por defectos: 10%.
- Bonificación por disponibilidad inmediata: 5%.

El objetivo es que el sistema pueda simular una decisión razonable cuando existan hasta 5 proveedores asociados al producto.

## Base de datos

Este ajuste no requiere cambios en la base de datos.

## Validación realizada

Se ejecutó `php -l` sobre todos los archivos PHP del proyecto y no se detectaron errores de sintaxis.
