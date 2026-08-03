# Hotfix reabastecimiento

Corrige el error:

`Call to undefined function emxCerrarSolicitudesDuplicadasProducto()`

Causa:
El parche anterior llamaba esa función al aprobar cotizaciones, pero la definición no quedó insertada en `funciones_stock.php`.

Qué hace este hotfix:
- Agrega `emxCerrarSolicitudesDuplicadasProducto()`.
- Cierra solicitudes activas duplicadas del mismo producto al aprobar una cotización.
- Rechaza propuestas pendientes de solicitudes viejas.
- Mantiene visible solo la solicitud activa más reciente por producto.
- Incluye `fix_reabastecimiento_duplicadas.sql` para limpiar duplicadas existentes.

Después de copiar archivos, ejecuta una vez:

`psql -d electro2 -f fix_reabastecimiento_duplicadas.sql`
