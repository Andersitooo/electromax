# ElectroMax - Corrección CRUD productos / subida de imágenes

Este paquete parte del último proyecto con reserva, backorder y punto de reorden, y solo ajusta el CRUD del admin.

## Archivos tocados

- `admin.php`
- `seguridad.php`

## Correcciones

### Productos

- Se corrigió el guardado de productos para que sea más compatible con tu base actual.
- Las columnas opcionales (`modelo`, `costo_unitario`, `descuentos_volumen_rangos`, etc.) se usan solo si existen en PostgreSQL.
- La subida de imágenes ahora valida formato real, pero no se rompe si XAMPP no tiene `finfo` activo.
- Se aumentó el límite por imagen a 12MB.
- Se muestra una previsualización antes de guardar, para confirmar que las imágenes sí fueron seleccionadas.
- Si la subida falla, ahora el mensaje indica la razón real: permisos, tamaño, formato o error temporal.
- Se agregó rollback de transacción si algo falla durante el guardado.
- Se evita borrar imágenes de otro producto por manipulación de formulario.

### Seguridad de subida

- Solo se aceptan JPG, PNG, WEBP y GIF reales.
- El nombre del archivo se genera internamente.
- No se usa el nombre original del archivo.
- Se mantiene la carpeta `uploads`.

## Si todavía no sube imágenes

Revisa en XAMPP/PHP:

```ini
file_uploads = On
upload_max_filesize = 12M
post_max_size = 32M
```

Y confirma que la carpeta tenga permisos de escritura:

```text
C:\xampp\htdocs\electro2\uploads
```

No requiere migración SQL.
