# Fix final: ficha técnica, PDF y recomendados

## Objetivo

Mejorar la ficha técnica y su PDF para que usen el mismo diseño base, colores y estructura, con especificaciones en tabla técnica profesional y sin los vistos verdes anteriores. También se mejoró el encabezado de productos recomendados con un icono distintivo.

## Cambios principales

- `ficha_tecnica.php` ahora renderiza la ficha usando `emxFichaRenderDocumento()`.
- `ficha_tecnica_pdf.php` usa el mismo renderizador que la ficha HTML.
- El PDF usa Dompdf cuando está instalado, con fuente `DejaVu Sans`, para soportar acentos y caracteres como `Página`, `técnica`, `m²`, `Sí`, etc.
- Las especificaciones se muestran como tablas con columnas `Especificación` y `Detalle del producto`.
- Se retiraron los estilos con vistos/checks verdes en especificaciones.
- El encabezado de `Productos recomendados` incluye icono y etiqueta visual.

## Importante para VPS

Como se agregó Dompdf, después de hacer `git pull` en el VPS ejecutar:

```bash
cd /var/www/anderspace/electromax
composer update dompdf/dompdf --no-dev --optimize-autoloader
```

Luego:

```bash
php scripts/verificar_fix_ficha_pdf_recomendados.php
sudo systemctl restart php-fpm
sudo systemctl reload nginx
```

## Pruebas recomendadas

```bash
curl -Ik "https://anderspace.online/ficha_tecnica.php?id=ID_DEL_PRODUCTO"
curl -Ik "https://anderspace.online/ficha_tecnica_pdf.php?id=ID_DEL_PRODUCTO"
```

En navegador revisar:

- La ficha técnica usa una tabla elegante.
- El PDF ya no muestra rutas de navegador.
- El PDF soporta acentos correctamente.
- Los recomendados tienen encabezado con icono y estilo.
