# Fix PDF idéntico a ficha técnica

## Objetivo
Ajustar la ficha técnica y el PDF para que el documento generado se vea como la ficha `ficha_tecnica.php`: encabezado azul con logo, datos del producto a la izquierda, imagen a la derecha, resumen, tabla única de especificaciones y footer corporativo.

## Cambios
- Se bajaron un poco los botones de `Volver` y `Descargar PDF`.
- Se mantuvo el encabezado con información a un lado e imagen a otro lado.
- Se dejó una sola tabla cuadriculada para las especificaciones.
- Se ajustó el CSS PDF para A4 vertical con margen controlado.
- Se reemplazó el grid en PDF por estructura compatible con Dompdf para evitar descuadres.
- El PDF se genera con Dompdf y se descarga directamente, evitando encabezados/pies del navegador con fecha, ruta o ID.

## Archivos modificados
- `app/Helpers/funciones_ficha_tecnica.php`
- `app/Controllers/Web/ficha_tecnica_pdf.php` se mantiene usando Dompdf.
- `scripts/verificar_fix_pdf_identico_ficha.php`

## Verificación
```bash
php scripts/verificar_fix_pdf_identico_ficha.php
```

## Prueba en navegador
Usar un UUID real del producto:

```text
https://anderspace.online/ficha_tecnica.php?id=ID_REAL_DEL_PRODUCTO
https://anderspace.online/ficha_tecnica_pdf.php?id=ID_REAL_DEL_PRODUCTO
```

Para obtener un UUID:

```bash
psql -h 127.0.0.1 -U ecommerce_user -d ecommerce_db -c "SELECT id, nombre FROM productos ORDER BY nombre LIMIT 10;"
```
