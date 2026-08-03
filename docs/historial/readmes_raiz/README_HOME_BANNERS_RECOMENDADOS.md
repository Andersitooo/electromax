# Ajuste Home Modular, Más Vendidos y Recomendados

## Qué se cambió

- `index.php` ya no imprime todos los banners debajo del hero.
- El hero principal se mantiene arriba.
- Los demás grids/banners se insertan entre bloques del home:
  - después de categorías,
  - entre productos destacados,
  - después de más vendidos,
  - antes del footer.
- `Productos más vendidos` ahora se ordena por ventas reales desde `detalle_pedidos` + `pedidos`.
- Si no hay ventas reales, la sección no inventa datos.
- `producto.php` ahora muestra productos recomendados con criterio: misma categoría, misma marca, precio parecido, ventas y calificación.
- Se agregó `funciones_home.php` para concentrar esta lógica sin tocar la base de datos.

## Cómo acomodar banners desde Admin

Usa `Admin > Banners > Secciones`:

1. La primera sección tipo `carousel` o con nombre `Hero Principal` se usa como hero superior.
2. Las demás secciones se reparten automáticamente según su nombre y posición.
3. Reglas simples:
   - nombre con `Categoría` aparece después de categorías.
   - nombre con `Producto`, `Destacado` o `Vendido` aparece entre bloques de productos.
   - nombre con `Oferta`, `Promoción` o `Campaña` aparece después de más vendidos.
   - nombre con `Final`, `Footer` o `Membresía` aparece antes del footer.

No requiere migración SQL.
