# Fix de rutas localhost con raíz limpia

Fecha: 2026-08-03 00:01:04

## Error detectado

Al abrir:

```text
http://localhost/electro3/index.php
```

Apache respondió:

```text
Not Found
The requested URL was not found on this server.
```

## Causa

La estructura final neta eliminó `index.php` de la raíz. La entrada real quedó en:

```text
public/index.php
```

Por eso esta URL sí debía funcionar:

```text
http://localhost/electro3/public/index.php
```

pero la URL antigua:

```text
http://localhost/electro3/index.php
```

ya no tenía archivo físico en raíz.

## Corrección aplicada

Se agregó un archivo `.htaccess` en la raíz.

Ese archivo no vuelve a crear PHP en raíz. Solo redirige internamente a `public/`.

## Resultado esperado

Ahora deberían funcionar:

```text
http://localhost/electro3/index.php
http://localhost/electro3/auth.php
http://localhost/electro3/admin.php
http://localhost/electro3/proveedor.php
```

y también:

```text
http://localhost/electro3/public/index.php
```

## Si sigue saliendo Not Found

Puede ser porque Apache no tiene activo `mod_rewrite`.

En ese caso, prueba directamente:

```text
http://localhost/electro3/public/index.php
```

o activa `mod_rewrite` en XAMPP.
