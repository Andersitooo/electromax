# Fix búsqueda del navbar y autocompletado

## Problema corregido

Al buscar desde el navbar, `app/Controllers/Web/index.php` podía lanzar:

```text
SQLSTATE[HY093]: Invalid parameter number / Número de parámetro no válido
```

El error ocurría al ejecutar la consulta principal de productos porque la búsqueda combinaba varios placeholders posicionales `?` dentro del cálculo de relevancia y del filtro `WHERE`.

## Cambios realizados

- `app/Controllers/Web/index.php`
  - La consulta principal de búsqueda usa parámetros nombrados únicos.
  - Se eliminó la mezcla riesgosa de placeholders posicionales en la consulta dinámica.
  - Se agregó búsqueda por tokens/palabras para encontrar productos similares aunque la frase exacta no coincida.
  - Se mantiene orden por relevancia, stock y descuento.

- `app/Controllers/Api/buscar_sugerencias.php`
  - El endpoint de autocompletado usa parámetros nombrados únicos.
  - Busca por nombre, descripción corta, SKU, marca y categoría.
  - Agrega coincidencias por tokens para sugerir productos similares.

- `scripts/verificar_fix_busqueda_navbar.php`
  - Verificador rápido de estructura del fix.

## Pruebas sugeridas

En local:

```bash
php -l app/Controllers/Web/index.php
php -l app/Controllers/Api/buscar_sugerencias.php
php scripts/verificar_fix_busqueda_navbar.php
```

Luego probar en navegador:

```text
http://localhost/electro/index.php?q=tv
http://localhost/electro/index.php?q=samsung
http://localhost/electro/index.php?q=lavadora
```

Y escribir en el buscador del navbar al menos 2 letras para ver sugerencias.
