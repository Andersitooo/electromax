# Fix final: ficha técnica, PDF y productos recomendados

## Cambios aplicados

1. `ficha_tecnica.php` y `ficha_tecnica_pdf.php` ahora usan el mismo renderizador visual: `emxFichaRenderDocumento()`.
2. Las especificaciones técnicas se muestran en una sola tabla cuadriculada, sin dividir por secciones.
3. Se eliminó el diseño con vistos/listas verdes y se cambió por una tabla técnica más formal.
4. El PDF usa Dompdf 3.1 mediante Composer para evitar caracteres corruptos y para no depender del imprimir del navegador.
5. El PDF ya no debe mostrar rutas del navegador ni fecha/hora del navegador, porque no usa `window.print()`.
6. El PDF usa tamaño A4, márgenes controlados y aprovecha mejor el ancho de la hoja.
7. Se corrigió la función de productos recomendados para que use correctamente parámetros SQL y tenga fallback.
8. La sección de productos recomendados ahora tiene encabezado visual con icono y tarjetas tipo carrusel.

## Archivos modificados

- `composer.json`
- `app/Helpers/funciones_ficha_tecnica.php`
- `app/Controllers/Web/ficha_tecnica.php`
- `app/Controllers/Web/ficha_tecnica_pdf.php`
- `app/Helpers/funciones_home.php`
- `views/frontend/producto_view.php`
- `scripts/verificar_fix_ficha_pdf_recomendados_unificado.php`

## Comandos VPS

Después de hacer `git pull`:

```bash
cd /var/www/anderspace/electromax
composer update dompdf/dompdf --no-dev --optimize-autoloader
php scripts/verificar_fix_ficha_pdf_recomendados_unificado.php
sudo systemctl restart php-fpm
sudo systemctl reload nginx
```

Si ya existe `composer.lock` actualizado en GitHub, usar preferentemente:

```bash
composer install --no-dev --optimize-autoloader
```

