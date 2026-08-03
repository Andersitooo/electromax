# Ajuste: múltiples imágenes en productos

Este paquete permite subir varias imágenes al crear o editar productos desde `admin.php`.

## Qué cambia

- El input de producto queda como `name="imagenes[]"` y `multiple`.
- Puedes seleccionar varias imágenes a la vez con Ctrl/Shift.
- También puedes presionar “Agregar imágenes” varias veces y la selección se acumula, no se reemplaza.
- Se muestra previsualización de todas las imágenes antes de guardar.
- Puedes quitar imágenes individuales antes de guardar.
- La primera imagen seleccionada queda marcada como principal para el orden inicial.
- El backend guarda cada imagen en `producto_multimedia` con orden consecutivo.
- En edición, las imágenes nuevas se agregan sin borrar las existentes, salvo que marques una existente para eliminar.

## Archivos modificados

- `admin.php`

No requiere migración SQL.

## Nota XAMPP

Si seleccionas muchas imágenes grandes y no llegan al servidor, revisa `php.ini`:

```ini
file_uploads = On
upload_max_filesize = 12M
post_max_size = 64M
max_file_uploads = 20
```

Luego reinicia Apache.
