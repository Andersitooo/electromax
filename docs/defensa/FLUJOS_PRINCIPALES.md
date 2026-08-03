# Flujos principales del sistema

## Flujo de compra

```text
1. Cliente navega el catálogo.
2. Cliente entra a la ficha del producto.
3. Cliente agrega producto al carrito.
4. Sistema calcula precio, IVA y descuentos.
5. Cliente pasa al checkout.
6. Sistema valida carrito y datos.
7. Se registra el pedido.
8. Admin puede revisar o aprobar.
9. Sistema genera factura cuando corresponde.
10. Cliente consulta tracking.
```

## Flujo de descuentos

```text
1. El producto tiene precio base.
2. Se calcula IVA.
3. Se revisa si hay descuento normal activo por fecha.
4. Se revisan rangos de descuento por volumen.
5. Se revisa si el cliente tiene membresía o beneficio.
6. Se calcula precio final.
```

## Flujo de proveedor

```text
1. Proveedor inicia sesión.
2. Registra o edita capacidad de producción.
3. Configura unidades disponibles, tiempos y rangos de descuento.
4. Sistema usa esa información para estimaciones.
5. Proveedor puede enviar propuestas.
6. Admin revisa propuestas.
```

## Flujo de devolución

```text
1. Cliente reporta problema.
2. Admin revisa solicitud.
3. Cliente devuelve producto.
4. Técnico valida la serie física.
5. Si la serie coincide, el flujo continúa.
6. Si la serie no coincide, se genera alerta de posible fraude o error.
7. Admin decide reembolso, reemplazo o rechazo según el caso.
```

## Flujo de reemplazo

```text
1. Producto original ya fue facturado.
2. Cliente solicita cambio por el mismo producto.
3. Sistema conserva factura original.
4. Se valida serie original devuelta.
5. Se genera pedido de reemplazo.
6. Se asigna una nueva serie al producto enviado.
7. Queda trazabilidad entre serie original y serie nueva.
```

## Flujo de reembolso

```text
1. Producto ya fue facturado.
2. Cliente solicita devolución con reembolso.
3. Admin aprueba.
4. Sistema genera nota de crédito.
5. Sistema envía o registra correo con documento.
```

## Flujo de soporte

```text
1. Cliente abre ticket.
2. Admin revisa.
3. Admin responde o cambia estado.
4. Cliente puede responder.
5. Ticket se cierra cuando el caso termina.
```
