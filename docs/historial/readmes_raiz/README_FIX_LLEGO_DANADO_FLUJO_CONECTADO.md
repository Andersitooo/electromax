# Fix flujo: cliente confirma que llegó dañado

Problema corregido:
- En la confirmación de entrega, la opción C "Llegó dañado o incompleto" solo cambiaba el pedido a revisión, pero no dejaba conectado el caso al flujo completo de devoluciones.
- El cliente no llegaba al punto donde, después de inspección, el admin le habilita reembolso, cambio por otro igual o ambas opciones.

Cambio aplicado:
1. Si el cliente marca "Llegó dañado o incompleto" desde Mi cuenta o Tracking:
   - El pedido pasa a `En Revisión`.
   - `confirmacion_cliente_estado` queda en `llego_danado`.
   - Se crea automáticamente un caso en `devoluciones` con:
     - motivo: `danado_envio`
     - estado inicial: `pendiente_revision`
     - tipo de caso: `incidencia_entrega`

2. El flujo queda conectado así:
   pendiente_revision
   -> autorizada_retorno
   -> en_camino_retorno
   -> recibido_almacen
   -> en_inspeccion
   -> esperando_decision_cliente
   -> cliente_eligio_reembolso o cliente_eligio_cambio

3. Cuando el admin usa "Ofrecer solución al cliente", puede habilitar:
   - solo reembolso
   - solo cambio por otro igual
   - reembolso o cambio, cliente elige

4. Si el admin habilita ambas opciones, el cliente ve ambos botones en Mis devoluciones.

No requiere SQL nuevo si ya ejecutaste las migraciones anteriores de devoluciones.
