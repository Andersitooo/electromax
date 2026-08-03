# Seguridad del sistema

## Archivo principal

```text
app/Middleware/security.php
```

## Responsabilidades

```text
iniciar sesión de forma segura
proteger formularios POST con CSRF
validar roles
restringir rutas según usuario
validar redirecciones internas
ayudar con subida segura de archivos
```

## CSRF

El sistema usa token CSRF para proteger formularios POST.

Esto ayuda a evitar que un formulario sea enviado desde otro sitio sin autorización.

## Roles

El sistema distingue roles como:

```text
CLIENTE
PROVEEDOR
ADMIN
SUPERADMIN
INVITADO
```

## Protección de rutas

Ejemplo:

```php
emxRequireRole(['PROVEEDOR']);
```

Esto permite que solo un proveedor entre a su panel.

## Contraseñas

Las contraseñas se guardan con hash usando funciones seguras de PHP.

No deben guardarse contraseñas planas.

## Configuración sensible

El correo SMTP y la base de datos deben configurarse con cuidado.

No se deben subir contraseñas reales a repositorios públicos.
