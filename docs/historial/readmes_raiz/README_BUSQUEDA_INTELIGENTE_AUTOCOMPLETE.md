# Búsqueda inteligente con autocompletado

Archivos agregados/modificados:

- `buscar_sugerencias.php`
- `assets/emx_search_autocomplete.js`
- `components/navbar.php`
- `index.php`
- `migracion_busqueda_inteligente.sql`

## Qué hace

- Autocompleta desde productos reales de la base de datos.
- Recomienda productos, categorías y marcas existentes.
- Se alimenta automáticamente de productos nuevos que agregues desde admin.
- Muestra imagen, marca, categoría, precio y descuento.
- Permite abrir directamente un producto sugerido.
- Ordena mejor los resultados de búsqueda:
  - coincidencia exacta por nombre
  - nombre que empieza igual
  - SKU exacto
  - marca
  - categoría
  - descripción
  - stock disponible
  - productos con descuento

## SQL opcional

Para mejorar rendimiento, ejecuta una vez:

```bash
psql -d electro2 -f migracion_busqueda_inteligente.sql
```

No es obligatorio para que funcione, pero ayuda cuando tengas muchos productos.
