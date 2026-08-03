# Devoluciones: flujo secuencial, decisión cliente y alertas de fraude

## Flujo normal antes de 30 días

1. `pendiente_revision`
2. `requiere_mas_evidencia` si falta información, o `autorizada_retorno`
3. `en_camino_retorno`
4. `recibido_almacen`
5. `en_inspeccion`
6. `esperando_decision_cliente`
7. El cliente elige:
   - `cliente_eligio_reembolso`
   - `cliente_eligio_cambio`
8. Si eligió reembolso:
   - `reembolsado`
   - `cerrada`
9. Si eligió cambio:
   - `cambio_despachado`
   - `reemplazo_en_transito`
   - `reemplazo_entregado`
   - `cerrada`

## Garantía / proveedor

Cuando la inspección determina defecto de fábrica, el admin puede mandar el caso a `garantia_proveedor`.
Desde ahí el proveedor/admin decide reembolso, cambio o rechazo.

## Alertas de fraude

Se mantienen/añaden estas alertas:
- Muchas devoluciones en menos de 30 días.
- Devoluciones muy rápidas después de compra.
- Serie física devuelta que no coincide con la serie vendida al cliente.

## Serie del producto

El admin ahora ve en la tabla de devoluciones:
- Series vendidas al cliente por pedido/producto.
- Serie física recibida en almacén.
- Alerta si la serie recibida no coincide.

## SQL

Ejecutar:
`migracion_devoluciones_flujo_secuencial_fraude.sql`
