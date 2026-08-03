# Plan Fase 8: limpiar archivos duplicados o muertos

## Objetivo

Detectar qué archivos ya no se usan y cuáles pueden moverse a backup o eliminarse.

## Regla

No borrar nada sin revisar primero.

## Acciones propuestas

1. Detectar archivos README antiguos duplicados.
2. Detectar SQL adaptadores que ya no se quieran conservar.
3. Detectar imágenes o assets no referenciados.
4. Detectar PHP que no aparece en rutas ni includes.
5. Crear `storage/backups/fase8_removed`.
6. Documentar todo lo movido o eliminado.

## Archivos que no se deben eliminar todavía

```text
index.php
admin.php
proveedor.php
carrito.php
checkout.php
mi_cuenta.php
auth.php
db.php
seguridad.php
funciones_*.php
```

Aunque algunos sean adaptadores, son necesarios para compatibilidad.
