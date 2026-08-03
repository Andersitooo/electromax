# Fix filtro de estrellas exactas

Fecha: 2026-08-03 00:31:52

## Problema

El filtro de calificación decía:

```text
1 o más
2 o más
3 o más
4 o más
5 o más
```

Eso hacía que al seleccionar 4 estrellas también pudieran aparecer productos de 5 estrellas.

## Corrección

Ahora el filtro funciona por bloque exacto:

```text
1 estrella  = promedio >= 1 y < 2
2 estrellas = promedio >= 2 y < 3
3 estrellas = promedio >= 3 y < 4
4 estrellas = promedio >= 4 y < 5
5 estrellas = promedio >= 5
```

## Archivos modificados

```text
views/frontend/index_view.php
app/Controllers/Api/api_filtrar_productos.php
app/Controllers/Api/api_guardar_producto.php
scripts/verificar_fix_filtro_estrellas.php
```

## Resultado esperado

Si marcas:

```text
1 estrella
```

se muestran productos con promedio de 1 estrella.

Si marcas:

```text
4 estrellas
```

se muestran productos del bloque de 4 estrellas, no los de 5.

Si marcas:

```text
5 estrellas
```

se muestran productos de 5 estrellas.
