# Fix carrito sin login

Fecha: 2026-08-03 00:20:34

## Problema

Al presionar "Agregar al carrito" desde una tarjeta o desde la ficha de producto sin iniciar sesión, la interfaz mostraba:

```text
Error de conexión
```

## Causa

El endpoint `add_to_cart.php` devolvía JSON con `success=false`, pero el JavaScript trataba cualquier respuesta negativa como error de conexión.

## Corrección

1. `app/Controllers/Api/add_to_cart.php` ahora responde con HTTP 401 y JSON:

```json
{
  "success": false,
  "requires_login": true,
  "login_url": "auth.php?action=login&msg=debes_iniciar_sesion"
}
```

2. `views/frontend/index_view.php` detecta `requires_login` y redirige al login.
3. `views/frontend/producto_view.php` detecta `requires_login` y redirige al login.
4. `app/Controllers/Auth/auth.php` respeta `$_SESSION['redirect_after_login']` para volver al producto o página anterior después de iniciar sesión.
5. Se corrigió una variable interna de `add_to_cart.php` para backorder: ahora usa `$precioCalc['precio_base']`.

## Resultado esperado

Sin login:

```text
Tarjeta de producto -> Agregar al carrito -> Login
Ficha de producto   -> Agregar al carrito -> Login
Carrito directo     -> Login
```

Después de iniciar sesión como cliente, el sistema intenta volver a la página desde donde se pidió agregar al carrito.
