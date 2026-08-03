# Plan Fase 5: separar vistas frontend, admin y proveedor

## Objetivo

Separar el HTML de los archivos grandes.

## Carpetas objetivo

```text
views/frontend
views/admin
views/proveedor
views/auth
views/components
```

## Archivos candidatos

```text
index.php
producto.php
carrito.php
checkout.php
mi_cuenta.php
admin.php
proveedor.php
auth.php
```

## Regla de seguridad

No eliminar rutas antiguas.

Cada ruta antigua debe quedar como controlador o adaptador que cargue la vista nueva.
