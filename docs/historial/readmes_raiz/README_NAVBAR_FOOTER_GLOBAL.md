# Navbar y footer global ElectroMax

Se agregó un layout cliente reutilizable para mantener estética consistente en la mayoría de páginas del cliente.

Archivos nuevos/actualizados:
- `components/navbar.php`
- `components/footer.php`

Páginas integradas:
- `auth.php`
- `carrito.php`
- `checkout.php`
- `planes.php`
- `tracking.php`
- `garantia.php`
- `wishlist.php` (footer)
- `notificaciones.php` (footer)

No se aplicó a módulos administrativos o técnicos como:
- `admin.php`
- `proveedor.php`
- `analitica.php`
- `correos_empresa.php`
- `imprimir_guia.php`
- `generar_etiqueta.php`

El navbar mantiene:
- logo ElectroMax
- búsqueda
- categorías dinámicas
- membresías
- wishlist con contador
- notificaciones con contador
- perfil
- carrito con contador

El footer mantiene:
- logo
- categorías dinámicas
- soporte
- contacto
- newsletter
- métodos de pago visuales
