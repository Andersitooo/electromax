# Modales, devoluciones secuenciales y limpieza visual

Este parche deja el flujo de devoluciones más cerrado y elimina los diálogos nativos del navegador.

## Modales del sistema

Se agregó `assets/emx_modales.js` y se incluyó en las páginas principales. Ahora las validaciones, advertencias y confirmaciones usan modales visuales del sistema, no `alert()` ni `confirm()` nativos de JavaScript.

Se cambiaron confirmaciones de acciones como eliminar, cancelar pedido, cancelar membresía, aprobar cotización, enviar confirmación de entrega y enviar devolución.

## Emojis removidos

Se limpiaron emojis literales de opciones, mensajes y textos visibles. Se mantienen los iconos de FontAwesome porque pertenecen al diseño del sistema.

Ejemplos corregidos:
- Motivos de devolución sin emojis.
- Opciones de confirmación de entrega sin emojis.
- Opciones de banners/admin sin emojis.
- Mensajes de validación sin símbolos de celular.

## Cobertura del flujo de devoluciones antes de 30 días

Flujo principal:

pendiente_revision
→ autorizada_retorno
→ en_camino_retorno
→ recibido_almacen
→ en_inspeccion
→ esperando_decision_cliente
→ cliente_eligio_reembolso o cliente_eligio_cambio

Rama reembolso:

cliente_eligio_reembolso
→ reembolsado
→ cerrada

Rama cambio por otro igual:

cliente_eligio_cambio
→ cambio_despachado
→ reemplazo_en_transito
→ reemplazo_entregado
→ cerrada

## Casos especiales cubiertos

- Falta de evidencia: pendiente_revision → requiere_mas_evidencia → pendiente_revision.
- Rechazo: pendiente_revision / en_inspeccion → rechazada; luego puede reabrirse con justificación.
- No recibido o extravío courier: investigacion_courier → aprobado_reembolso o aprobado_cambio.
- Daño por transporte: reclamo_courier → aprobado_reembolso o aprobado_cambio.
- Defecto de fábrica/proveedor: garantia_proveedor → aprobado_reembolso o aprobado_cambio.

## Fraude y series

Se mantiene la detección por:
- Muchas devoluciones del mismo cliente en menos de 30 días.
- Devoluciones demasiado rápidas después de la compra.
- Serie física devuelta que no coincide con la serie vendida.

El admin puede ver la serie vendida al cliente y escribir la serie física recibida. Si no coincide, el sistema marca alerta de fraude y no permite avanzar sin revisión/rechazo con comentario.

## Ajuste importante

`cambio_despachado` ya no se trata como estado terminal al detectar incidencias duplicadas. El caso queda activo hasta `reemplazo_entregado` y finalmente `cerrada`.
