# Ajuste devoluciones: costos visibles y flujos por motivo

Cambios aplicados:

1. Se eliminó el texto visible que decía `$5.00` en devoluciones.
2. Para motivos por decisión del cliente ahora se muestra como `retorno sujeto a revisión logística`, sin hablar de cobro directo.
3. El backend ya no guarda automáticamente `5.00` ni `reembolso_parcial` para esos motivos; queda como `pendiente_definir` hasta que admin revise.
4. Se agregó validación de soluciones permitidas por tipo de caso.
5. En admin, al ofrecer solución, se muestra el flujo del caso y solo aparecen las soluciones permitidas.

Flujos cubiertos:

Responsabilidad de ElectroMax:
- defectuoso
- producto incorrecto
- faltan piezas
- caja abierta / sello roto
- dañado durante envío

Flujo:
pendiente_revision -> autorizada_retorno -> en_camino_retorno -> recibido_almacen -> en_inspeccion -> esperando_decision_cliente -> reembolso o cambio -> cierre.

Decisión del cliente:
- no me gusta / arrepentimiento
- encontré mejor precio
- ya no lo necesito
- otro por decisión del cliente

Flujo:
pendiente_revision -> autorizada_retorno -> en_camino_retorno -> recibido_almacen -> en_inspeccion -> solución normalmente reembolso -> cierre.

Talla/color/variante:
Flujo de decisión del cliente, pero permite reembolso, cambio o ambas opciones porque puede aplicar cambio de variante.

Courier / no recibido:
investigacion_courier -> courier_reembolso o courier_reenvio -> ejecución de reembolso o reemplazo -> cierre.

Otro sin clasificar:
requiere revisión manual, evidencia o inspección antes de ofrecer solución.
