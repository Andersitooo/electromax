# Fase 3: configuración, seguridad y helpers

Fecha de generación: 2026-08-02 23:07:55

## Objetivo

Separar configuración, seguridad y helpers compartidos sin romper rutas antiguas.

## Qué se hizo

1. Se creó configuración centralizada en `app/Config`.
2. Se movió la seguridad base a `app/Middleware/security.php`.
3. Se movieron los helpers `funciones_*.php` a `app/Helpers`.
4. Se dejaron adaptadores en la raíz para conservar compatibilidad.
5. Se documentó qué archivo quedó en qué capa.
6. Se validó sintaxis PHP.

## Qué no se hizo todavía

No se separó toda la lógica de negocio de archivos grandes como:

- `admin.php`
- `proveedor.php`
- `carrito.php`
- `checkout.php`
- `mi_cuenta.php`
- `producto.php`

Eso corresponde a la Fase 4 y Fase 5.
