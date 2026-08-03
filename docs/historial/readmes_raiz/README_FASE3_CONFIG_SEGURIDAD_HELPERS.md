# ElectroMax - Fase 3

## Fase 3 realizada

Se movieron configuración, seguridad y helpers compartidos a carpetas internas organizadas.

## Nuevas capas

```text
app/Config
app/Middleware
app/Helpers
```

## Compatibilidad

Las rutas antiguas se mantienen como adaptadores.

Ejemplo:

```php
require_once 'db.php';
```

Sigue funcionando, pero carga:

```php
app/Config/database.php
```

## No requiere SQL

Esta fase no modifica tablas ni datos.

## Validación

Se validó sintaxis PHP de los archivos del proyecto.

## Siguiente fase

Fase 4: separar lógica de negocio.
