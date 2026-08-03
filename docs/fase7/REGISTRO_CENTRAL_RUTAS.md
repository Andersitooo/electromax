# Registro central de rutas heredadas

Archivo creado:

```text
app/Support/legacy_routes.php
```

Este archivo devuelve un arreglo con dos grupos:

```text
php:
rutas antiguas PHP.

sql:
archivos SQL antiguos que ahora redirigen a database/.
```

También se creó:

```text
app/Support/legacy_helpers.php
```

Funciones disponibles:

```php
emx_legacy_routes()
emx_legacy_route_target($ruta)
emx_legacy_route_exists($ruta)
```

Estas funciones sirven para consultar la compatibilidad sin buscar manualmente en todo el proyecto.
