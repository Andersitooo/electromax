# Mapa de vistas separadas

| Ruta antigua | Vista nueva | Estado | Nota |
|---|---|---|---|
| `auth.php` | `views/auth/auth_view.php` | separado | controlador conserva ruta antigua |
| `index.php` | `views/frontend/index_view.php` | separado | controlador conserva ruta antigua |
| `producto.php` | `views/frontend/producto_view.php` | separado | controlador conserva ruta antigua |
| `carrito.php` | `views/frontend/carrito_view.php` | separado | controlador conserva ruta antigua |
| `checkout.php` | `views/frontend/checkout_view.php` | separado | controlador conserva ruta antigua |
| `mi_cuenta.php` | `views/frontend/mi_cuenta_view.php` | separado | controlador conserva ruta antigua |
| `planes.php` | `views/frontend/planes_view.php` | separado | controlador conserva ruta antigua |
| `wishlist.php` | `views/frontend/wishlist_view.php` | separado | controlador conserva ruta antigua |
| `notificaciones.php` | `views/frontend/notificaciones_view.php` | separado | controlador conserva ruta antigua |
| `tracking.php` | `views/frontend/tracking_view.php` | separado | controlador conserva ruta antigua |
| `garantia.php` | `views/frontend/garantia_view.php` | separado | controlador conserva ruta antigua |
| `soporte.php` | `views/frontend/soporte_view.php` | separado | controlador conserva ruta antigua |
| `admin.php` | `views/admin/admin_view.php` | separado | controlador conserva ruta antigua |
| `soporte_admin.php` | `views/admin/soporte_admin_view.php` | separado | controlador conserva ruta antigua |
| `correos_empresa.php` | `views/admin/correos_empresa_view.php` | separado | controlador conserva ruta antigua |
| `proveedor.php` | `views/proveedor/proveedor_view.php` | separado | controlador conserva ruta antigua |
| `components/navbar.php` | `views/components/navbar.php` | separado | adaptador conservado |
| `components/footer.php` | `views/components/footer.php` | separado | adaptador conservado |


## Cómo leer este mapa

La columna `Ruta antigua` indica el archivo que el navegador sigue visitando.

La columna `Vista nueva` indica dónde quedó el HTML principal.

Ejemplo:

```text
index.php
```

prepara datos y luego carga:

```text
views/frontend/index_view.php
```
