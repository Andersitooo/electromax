# Fix búsqueda del navbar por parentesco

## Problema

El buscador del navbar no ayudaba lo suficiente cuando el cliente escribía una sola letra, mezclaba mayúsculas/minúsculas o cometía errores pequeños al escribir.

Ejemplos esperados:

- `L` debe sugerir productos como Lavadora, Laptop o productos que comiencen con L.
- `l`, `L`, `LaV` deben comportarse igual.
- Si PostgreSQL tiene disponible `pg_trgm`, errores pequeños como `lavadra` pueden encontrar productos parecidos como `Lavadora`.

## Archivos modificados

- `app/Controllers/Api/buscar_sugerencias.php`
- `app/Controllers/Web/index.php`
- `public/assets/emx_search_autocomplete.js`
- `scripts/verificar_fix_busqueda_navbar_parentesco.php`

## Cambios aplicados

1. El autocompletado ahora funciona desde 1 letra.
2. La búsqueda usa `ILIKE`, por eso no diferencia mayúsculas y minúsculas.
3. Se agregaron coincidencias por prefijo: si escribe `L`, prioriza productos que empiezan con `L`.
4. Se agregaron coincidencias por marca, categoría, SKU y descripción corta.
5. Si la función `similarity(text,text)` existe en PostgreSQL, se activa búsqueda por parecido para tolerar errores pequeños.
6. Si `similarity` no existe, el sistema sigue funcionando con `ILIKE` sin romper la página.

## Comando de verificación

```bash
php scripts/verificar_fix_busqueda_navbar_parentesco.php
```

## Pruebas recomendadas

En navegador probar:

- `https://anderspace.online/index.php?q=L`
- `https://anderspace.online/index.php?q=l`
- `https://anderspace.online/index.php?q=Lavadora`
- `https://anderspace.online/index.php?q=lavadra`

En el navbar escribir una sola letra, por ejemplo `L`, y verificar que aparecen sugerencias.
