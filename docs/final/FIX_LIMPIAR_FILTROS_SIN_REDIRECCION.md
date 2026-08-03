# Fix limpiar filtros sin redirección

Fecha: 2026-08-03 00:41:09

## Problema

El botón `Limpiar` ubicado junto al botón de `Filtros` redirigía a:

```text
index.php
```

Eso sacaba al usuario de la categoría y lo mandaba a la página principal.

## Corrección

En páginas de categoría, el botón superior `Limpiar` ahora ejecuta:

```javascript
borrarFiltros(event)
```

y ya no usa un enlace directo a `index.php`.

## Resultado esperado

Dentro de una categoría:

```text
Limpiar -> limpia especificaciones, estrellas y precio -> se queda en la misma categoría
```

No debe redirigir a la página principal.

## Mejora adicional

El filtro de estrellas ahora muestra rangos explícitos:

```text
1.0 a 1.9 estrellas
2.0 a 2.9 estrellas
3.0 a 3.9 estrellas
4.0 a 4.9 estrellas
5.0 estrellas
```
