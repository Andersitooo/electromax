# Ajuste de logo y fichas técnicas ElectroMax

## Cambios aplicados

- Se recortó el logo PNG para quitar el espacio transparente excesivo y darle mayor protagonismo.
- Se colocó el logo real en las cabeceras principales del proyecto: tienda, producto, carrito, checkout, cuenta, auth, planes, wishlist, notificaciones, admin y footer.
- El logo ya no va dentro de una caja blanca. Se usa directamente sobre fondos claros azulados o fondos oscuros con sombra para que se vea mejor.
- La ficha técnica HTML de admin y usuario usa el mismo diseño.
- La ficha técnica ya no inventa datos. Si un dato no está registrado, no se agrega como especificación.
- Se quitó peso, stock y precio de la ficha técnica para que no aparezcan como especificaciones si no están dentro de `productos.especificaciones_tecnicas`.
- Las especificaciones ahora se agrupan por secciones: pantalla, audio, conectividad, sistema, capacidad, energía, diseño y otras especificaciones.
- Los valores tipo arreglo se muestran como etiquetas ordenadas, no como texto corrido.
- El PDF usa el mismo criterio: logo visible, fondo no blanco puro, datos reales y especificaciones ordenadas.

## Archivos principales modificados

- `assets/electromax_logo.png`
- `assets/electromax_logo_web.png`
- `assets/electromax_logo_pdf.jpg`
- `ficha_tecnica.php`
- `ficha_tecnica_pdf.php`
- `components/navbar.php`
- `components/footer.php`
- `admin.php`
- `index.php`
- `producto.php`
- `checkout.php`
- `carrito.php`
- `mi_cuenta.php`
- `auth.php`
- `planes.php`
- `wishlist.php`
- `notificaciones.php`
- `simulador_sucursales.php`
- `analitica.php`

No requiere cambios en la base de datos.
