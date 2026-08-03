# Ajuste premium de ficha técnica y carrito

Este paquete corrige el ajuste visual solicitado sin cambios de base de datos.

## Cambios aplicados

- Se eliminó la redundancia de ficha técnica en `producto.php`.
  - Quedan los botones principales: **Ver ficha técnica** y **Descargar PDF**.
  - La pestaña de especificaciones ya no repite otro bloque de descarga.
- La ficha técnica de cliente y admin usa el mismo diseño visual.
- Las especificaciones técnicas se muestran en secciones profesionales, con filas ordenadas y valores legibles.
- No se inventan datos: solo se renderiza lo existente en `productos.especificaciones_tecnicas`.
- Se agregó `funciones_ficha_tecnica.php` para compartir el mismo render premium entre producto y ficha.
- Se limpió la vista del carrito para que la estimación de sobrestock ocupe todo el ancho del producto.
- Se ocultaron datos internos al cliente: ranking de proveedores, proveedor ganador, costo empresa y score.
- En checkout se muestra la entrega aceptada de forma simple, sin datos internos de proveedor.

## Archivos principales tocados

- `producto.php`
- `ficha_tecnica.php`
- `funciones_ficha_tecnica.php`
- `carrito.php`
- `checkout.php`

## Sin migración SQL

No requiere modificar la base de datos.
