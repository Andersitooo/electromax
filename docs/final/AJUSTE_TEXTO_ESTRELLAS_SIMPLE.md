# Ajuste texto simple en filtro de estrellas

Fecha: 2026-08-03 00:44:41

## Cambio aplicado

Se quitó el texto visual con rangos:

```text
4.0 a 4.9 estrellas
```

y se volvió al texto simple:

```text
4 estrellas
```

## Importante

Solo cambió el texto visible del filtro.

La lógica interna sigue funcionando por rango exacto:

```text
1 estrella  = promedio >= 1 y < 2
2 estrellas = promedio >= 2 y < 3
3 estrellas = promedio >= 3 y < 4
4 estrellas = promedio >= 4 y < 5
5 estrellas = promedio >= 5
```

## Archivo modificado

```text
views/frontend/index_view.php
```
