# Ajuste: sobrestock, reserva y punto de reorden

Este paquete aplica la regla definida para pedidos con cantidad mayor al stock disponible.

## Regla funcional

- Aceptar entrega parcial o total en el carrito solo confirma el calendario.
- El stock se separa únicamente después del pago en checkout.
- Si el cliente rechaza el sobrestock, el producto se retira del carrito.
- Si elige entrega parcial:
  - Se descuentan/despachan las unidades disponibles ahora.
  - Se crea backorder por el faltante.
  - Se genera solicitud a proveedores por: faltante del cliente + punto de reorden.
- Si elige entrega total:
  - Las unidades disponibles se reservan para ese pedido.
  - No se despachan todavía.
  - Se crea backorder por el faltante.
  - Se genera solicitud a proveedores por: faltante del cliente + punto de reorden.
- Si el producto tiene stock 0, el carrito igual estima calendario usando proveedores asociados.
- Cuando admin aprueba una cotización vinculada a un backorder:
  - Primero se cubre el faltante del cliente.
  - Solo el excedente entra al stock vendible.
  - Si el producto pasa de 0 a stock disponible, se notifica a usuarios que lo tienen en wishlist.

## Ejemplo

Stock disponible: 22
Cliente pide: 100
Faltante: 78
Punto de reorden: 10

Solicitud interna a proveedor: 78 + 10 = 88

Si el proveedor entrega 88:
- 78 completan el pedido del cliente.
- 10 entran al stock general.

## Archivos modificados

- funciones_backorder.php
- funciones_stock.php
- checkout.php
- carrito.php

## Base de datos

No exige migración obligatoria. Si tu base tiene `inventario_sucursal.stock_reservado`, se usa para reservar entrega total. Si no existe, el sistema descuenta el stock como respaldo para evitar reventa.

Si tu tabla `solicitudes_reabastecimiento` tiene `backorder_id`, las solicitudes quedan vinculadas al pedido pendiente. Si no existe, el flujo sigue funcionando, pero sin vínculo directo.
