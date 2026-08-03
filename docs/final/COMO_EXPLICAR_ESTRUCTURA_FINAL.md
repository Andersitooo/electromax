# Cómo explicar la estructura final

La estructura final usa `public/` como entrada web.

Esto significa que el navegador solo debe entrar por archivos dentro de `public`.

La raíz del proyecto ya no contiene los antiguos archivos PHP como `index.php`, `admin.php` o `proveedor.php`.

El flujo queda así:

```text
Navegador
  -> public/index.php
  -> app/Controllers/Web/index.php
  -> app/Config, app/Middleware, app/Helpers, app/Services
  -> views/frontend/index_view.php
```

Esta estructura es más limpia porque separa:

```text
public:
entrada web y archivos públicos.

app:
lógica, configuración, seguridad y controladores.

views:
interfaz.

database:
SQL organizado.

storage:
archivos internos y respaldos.

docs:
documentación.
```
