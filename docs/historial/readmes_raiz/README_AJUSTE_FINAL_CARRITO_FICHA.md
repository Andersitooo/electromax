# Ajuste final: ficha técnica y carrito

Cambios realizados:

- `producto.php`: se retiró el bloque redundante de “Ficha técnica del producto / PDF elegante” dentro de la pestaña de especificaciones. Se mantienen los botones principales superiores “Ver ficha técnica” y “Descargar PDF”.
- `funciones_ficha_tecnica.php`: la ficha técnica completa ahora lista las especificaciones en secciones profesionales, con filas numeradas y valores más legibles. No inventa datos; solo usa `productos.especificaciones_tecnicas`.
- `funciones_backorder.php`: la estimación de sobrestock ahora consolida despachos por fecha. Si dos proveedores pueden llegar el mismo día, el cliente ve un solo despacho consolidado, no dos tarjetas con la misma fecha.
- `carrito.php`: la estimación de entrega quedó más uniforme, con tarjetas de opción A/B del mismo alto, botón “Rechazar sobrestock” y textos más claros para el cliente.

## Cómo se calcula la estimación

El sistema toma el stock inmediato disponible. Si la cantidad solicitada supera ese stock, calcula el faltante.

Para cada proveedor asociado al producto usa datos reales/simulables de `capacidad_proveedor`:

- `unidades_disponibles`
- `capacidad_diaria`
- `tiempo_entrega_estandar`
- `distancia_km`
- `tasa_defectos_fabrica`
- `descuentos_volumen`

La fecha estimada no es aleatoria. Sale de:

```text
fecha = hoy + tiempo_entrega_estandar + días_de_producción + logística_por_distancia
```

Donde:

```text
días_de_producción = ceil(cantidad_a_producir / capacidad_diaria)
logística_por_distancia = ceil(distancia_km / 500)
```

Para entrega total, se elige el proveedor con mejor puntaje para cubrir todo el faltante.

Para entrega parcial, se arman lotes con disponibilidad/capacidad de proveedores y luego se consolidan por fecha para que el cliente vea despachos ordenados.

## Nota

No requiere migración SQL.
