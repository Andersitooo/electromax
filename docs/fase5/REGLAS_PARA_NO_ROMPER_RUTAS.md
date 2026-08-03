# Reglas usadas para no romper rutas

## Regla 1

No se eliminan archivos raíz como:

```text
index.php
admin.php
proveedor.php
auth.php
```

## Regla 2

Los archivos raíz conservan el procesamiento inicial y cargan una vista.

## Regla 3

Las vistas se guardan por área:

```text
views/frontend
views/admin
views/proveedor
views/auth
```

## Regla 4

Los componentes antiguos quedan como adaptadores.

## Regla 5

Si un archivo usa muchas variables existentes, la vista se carga con `require` directo para conservar compatibilidad.
