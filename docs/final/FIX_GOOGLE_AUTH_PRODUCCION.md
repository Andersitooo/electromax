# Fix Google Auth en producción

## Problema

Al intentar iniciar sesión con Google en producción, la página podía quedar en blanco o fallar al redirigir a `google_auth.php`.

## Causa

`app/Helpers/funciones_google_auth.php` intentaba cargar `EMX_ROOT/config_google.php`, pero en la estructura final del proyecto la configuración real está en:

```text
app/Config/google.php
```

Además, la vista de registro enviaba el formulario a:

```text
google_auth.php?action=registro
```

pero el controlador público solo aceptaba `action=login`, `link` y `unlink`.

## Corrección

- `app/Helpers/funciones_google_auth.php` ahora carga `app/Config/google.php`.
- `app/Controllers/Auth/google_auth.php` ahora acepta `action=login` y `action=registro` con el mismo flujo seguro.
- Se agregó `scripts/verificar_fix_google_auth.php`.

## URLs recomendadas en Google Cloud

Para el sistema actual con Google Identity Services usando botón JS y callback POST, lo importante es:

### Orígenes autorizados de JavaScript

```text
https://anderspace.online
https://www.anderspace.online
```

### URIs de redireccionamiento autorizados

El flujo actual no depende de OAuth redirect clásico, pero puedes dejar estas por compatibilidad:

```text
https://anderspace.online/google_auth.php
https://www.anderspace.online/google_auth.php
```

No son necesarias las variantes con `?action=login` o `?action=registro`, porque esas rutas se usan como `action` de formulario interno, no como redirección directa de Google.

## Verificación en VPS

```bash
cd /var/www/anderspace/electromax
php scripts/verificar_fix_google_auth.php
php -l app/Helpers/funciones_google_auth.php
php -l app/Controllers/Auth/google_auth.php
curl -Ik https://anderspace.online/google_auth.php
```

`curl` puede redirigir o mostrar error controlado si se abre por GET; lo importante es que no haya HTTP 500 ni pantalla blanca.
