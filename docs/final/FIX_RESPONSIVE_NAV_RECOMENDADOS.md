# Fix final: responsividad, navegación activa y productos recomendados

Cambios aplicados:

1. Navbar responsive:
   - Se añadió buscador móvil debajo del logo/acciones.
   - Se redujo el logo en móvil para evitar cortes.
   - El menú de categorías mantiene scroll horizontal en pantallas pequeñas.

2. Estado activo en menú:
   - Inicio se marca solo en la portada.
   - Cada categoría se marca cuando se visita `index.php?categoria=slug`.
   - En la ficha de producto también se marca la categoría del producto.

3. Productos recomendados en ficha:
   - Se corrigieron parámetros sobrantes en `emxObtenerRecomendadosProducto`.
   - Se añadió fallback para mostrar productos activos si el recomendador principal falla.
   - Se ajustó el carrusel para móvil, tablet y escritorio.

Archivos modificados:

- `views/components/navbar.php`
- `views/frontend/index_view.php`
- `views/frontend/producto_view.php`
- `app/Helpers/funciones_home.php`
- `app/Controllers/Web/producto.php`
- `scripts/verificar_fix_responsive_nav_recomendados.php`
