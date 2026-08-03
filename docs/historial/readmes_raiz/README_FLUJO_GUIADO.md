# Cambios: flujo guiado de pedidos, devoluciones e incidencias

## Objetivo

Esta versión reemplaza el cambio libre de estados por acciones secuenciales. El administrador ya no debería escoger cualquier estado desde una lista, sino ejecutar botones como:

- Autorizar retorno
- Investigar courier
- Recibir en almacén
- Iniciar inspección
- Aprobar reembolso
- Aprobar cambio
- Enviar a garantía proveedor
- Reabrir con justificación

Cada acción valida si el estado actual permite avanzar hacia ese punto. Si no corresponde, el sistema bloquea el cambio.

## Archivos modificados

- `admin.php`
- `tracking.php`
- `mi_cuenta.php`
- `procesar_devolucion.php`
- `responder_devolucion.php`
- `recibir_devolucion.php`

## Archivo nuevo

- `flujo_admin.php`

Centraliza la máquina de estados de pedidos y devoluciones.

## SQL necesario

Ejecuta antes de probar los nuevos archivos:

```bash
psql -d electro2 -f migracion_flujo_guiado.sql
```

Este SQL agrega campos de historial, clasificación de caso, tipo de daño, campos técnicos y corrige `pedido_reemplazo_id` como UUID.

## Flujo de pedidos

Ruta normal:

```text
Pendiente → Pago confirmado → En Preparación → Despachado → En Tránsito → En Reparto → Entregado → Cerrado
```

Excepciones:

```text
En Tránsito / En Reparto → Extravío courier → En Revisión + incidencia courier
Entregado → No recibido → En Revisión + incidencia courier
Entregado → Llegó dañado → En Revisión + incidencia de entrega
```

## Flujo de devoluciones/incidencias

Ruta con retorno físico:

```text
pendiente_revision
→ autorizada_retorno
→ en_camino_retorno
→ recibido_almacen
→ en_inspeccion
→ aprobado_reembolso / aprobado_cambio / reclamo_courier / garantia_proveedor / rechazada
→ reembolsado / cambio_despachado / cerrada
```

Ruta sin producto físico, por courier:

```text
investigacion_courier
→ aprobado_reembolso / aprobado_cambio / rechazada
```

## Regla de diseño

Los estados normales no retroceden. Si hay un problema, se abre una incidencia. Si se necesita corregir algo, se hace con acción de reapertura o evidencia recibida y queda registrado en historial.
