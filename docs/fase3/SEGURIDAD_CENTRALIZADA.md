# Seguridad centralizada

## Nueva ubicación

```text
app/Middleware/security.php
```

## Responsabilidad

Este archivo centraliza:

- inicio seguro de sesión
- token CSRF
- validación de roles
- protección de rutas
- redirecciones internas
- subida segura de archivos

## Compatibilidad

La ruta antigua sigue funcionando:

```php
require_once 'seguridad.php';
```

Pero ahora `seguridad.php` solo carga:

```php
app/Middleware/security.php
```
