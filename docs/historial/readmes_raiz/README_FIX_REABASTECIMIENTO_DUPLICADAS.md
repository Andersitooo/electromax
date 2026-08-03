# Fix reabastecimiento duplicado

Problema corregido:
- Al aprobar una cotización, otra solicitud activa del mismo producto podía seguir en pie.
- Al crear una nueva solicitud manual, las anteriores del mismo producto podían quedar activas.

Cambios:
1. Al aprobar una cotización:
   - La cotización aprobada queda en `aprobada`.
   - Las demás cotizaciones de esa misma solicitud quedan en `rechazada`.
   - La solicitud aprobada queda en `aprobada`.
   - Otras solicitudes activas del mismo producto quedan en `reemplazada`/cerradas.
   - Sus propuestas pendientes quedan en `rechazada`.

2. Al crear una nueva solicitud manual:
   - Se cierran solicitudes activas anteriores del mismo producto.
   - Se crea la nueva solicitud.
   - Se generan nuevas cotizaciones automáticas.

3. En la vista admin:
   - Se muestra solo la solicitud activa más reciente por producto.
   - Se cambió el texto a `Cotizaciones calculadas / recibidas`.

SQL incluido:
- `fix_reabastecimiento_duplicadas.sql`

Ejecuta ese SQL una sola vez para limpiar solicitudes duplicadas que ya quedaron en tu base.
