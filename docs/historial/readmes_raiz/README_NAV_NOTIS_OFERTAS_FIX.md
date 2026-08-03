# Navbar, notificaciones y ofertas corregidas

Cambios principales:

1. Navbar:
- Se cambió a un color bajo claro/azul suave para que el logo se vea bien.
- Se quitó la cápsula blanca detrás del logo.
- Se quitó el contador del corazón de wishlist.
- Se quitó `Seguir pedido` al lado de Membresías.
- Se mantiene `Soporte` en navbar y footer.

2. Notificaciones:
- Si un producto de wishlist pasa de 10% a 20% de descuento, ahora sí notifica.
- Si aparece un descuento por primera vez, notifica.
- Si baja el precio base de un producto en wishlist, notifica.
- Si un producto sin stock vuelve a tener stock, mantiene la notificación existente.

3. Ofertas:
- `index.php?descuento_min=10` ahora incluye productos con exactamente 10%.
- Funciona si el descuento está guardado como `10` o como `0.10`.
- Respeta fechas de descuento desde/hasta.

SQL opcional:
- `migracion_notificaciones_wishlist_mejoradas.sql`
