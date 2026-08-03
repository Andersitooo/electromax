# Componentes visuales

## Qué se separó

Los componentes visuales principales se copiaron a:

```text
views/components/navbar.php
views/components/footer.php
```

## Compatibilidad

Las rutas antiguas siguen existiendo:

```text
components/navbar.php
components/footer.php
```

Pero ahora funcionan como adaptadores.

Esto evita romper páginas que todavía tienen:

```php
require __DIR__ . '/components/navbar.php';
```

o llamadas equivalentes.
