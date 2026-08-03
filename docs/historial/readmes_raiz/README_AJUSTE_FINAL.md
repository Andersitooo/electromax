# Ajuste final ElectroMax

Este paquete corrige los errores visuales y de flujo detectados después de la reformulación anterior, sin exigir cambios nuevos en la base de datos.

## Correcciones principales

### Admin
- Corregidos formularios de modales donde el campo CSRF quedó dentro del atributo `action`, lo que hacía que aparecieran líneas HTML visibles y dañaba los modales.
- Corregido el botón de solicitud de reabastecimiento en productos cuando el ID del producto es UUID.
- El modal de producto vuelve a tener más espacio para seleccionar campos y editar especificaciones.
- En la tabla de productos se agregó el botón de ojito para ver la ficha técnica desde admin.
- Las cotizaciones de proveedores se muestran más ordenadas con score, costo, tiempo, disponibilidad, capacidad y riesgo.
- Al aprobar una cotización, el stock se actualiza inmediatamente porque el flujo es simulado/académico.

### Proveedores y reabastecimiento
- Cuando admin solicita reabastecimiento, el sistema notifica a los proveedores asociados y también genera cotizaciones simuladas a partir de la capacidad registrada del proveedor.
- Así el admin puede comparar de inmediato hasta 5 proveedores y aprobar la opción más conveniente.
- El score recomendado considera costo, tiempo, cantidad/cobertura, riesgo por defectos y disponibilidad inmediata.

### Ficha técnica
- Se integró el logo real de ElectroMax en `assets/electromax_logo.png`.
- La ficha técnica HTML se rediseñó para verse más profesional y ordenada.
- La ficha técnica PDF ahora usa colores correctos, texto legible, logo JPG para PDF y paginación básica si hay muchas especificaciones.
- Como se consulta dinámicamente desde la base de datos, si admin edita las especificaciones del producto, la ficha se actualiza automáticamente al abrirla o descargarla.

### Carrito / checkout
- La estimación de sobrestock mantiene dos opciones: entrega parcial y entrega total.
- Checkout muestra la opción aceptada por el cliente para que no quede desconectada del pago.

## Instalación

1. Haz backup de tu carpeta actual.
2. Copia los archivos de este paquete sobre tu proyecto `electro2`.
3. No ejecutes SQL nuevo para este ajuste.
4. Prueba primero:
   - Admin > Productos > editar producto.
   - Admin > Productos > ojito de ficha técnica.
   - Admin > Productos > solicitar reabastecimiento.
   - Admin > Producto-Proveedores > ver cotizaciones y aprobar.
   - Carrito > cantidad mayor al stock > aceptar entrega parcial o total > checkout.

## Nota sobre el cálculo de proveedor ganador

Menor score = mejor opción para la empresa.

Fórmula simulada:

- Costo unitario estimado: 50%.
- Tiempo de entrega: 35%.
- Cantidad ofrecida / cobertura: 10%.
- Riesgo por defectos: 5%.
- Disponibilidad inmediata: bonificación adicional.

Esto permite comparar proveedores aunque todavía sea un sistema académico y no haya integraciones reales con proveedores externos.
