# Archivos de raíz conservados

Estos archivos se conservaron para no romper rutas antiguas:

```text
db.php
seguridad.php
config_google.php
config_correo.php
empresa_config.php
funciones_*.php
```

Ahora funcionan como adaptadores.

## Por qué no se eliminan

Porque muchos archivos del proyecto todavía los llaman directamente.

Ejemplo:

```php
require_once 'db.php';
require_once 'funciones_backorder.php';
```

Eliminar esas rutas en esta fase rompería el proyecto.
