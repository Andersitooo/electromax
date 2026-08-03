# Adaptadores de compatibilidad

## Qué es un adaptador

Un adaptador es un archivo pequeño que conserva una ruta antigua, pero carga el archivo nuevo.

Ejemplo:

```php
// db.php
require_once __DIR__ . '/bootstrap/app.php';
require_once EMX_ROOT . '/app/Config/database.php';
```

## Por qué se usan

El proyecto todavía tiene muchas rutas que hacen:

```php
require_once 'db.php';
require_once 'seguridad.php';
require_once 'funciones_stock.php';
```

Si se eliminaran esos archivos de raíz, muchas páginas dejarían de cargar.

## Beneficio

Se puede organizar el código por capas sin romper el sistema actual.
