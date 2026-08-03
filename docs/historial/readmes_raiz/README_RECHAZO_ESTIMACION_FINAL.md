# Ajuste final: rechazo de sobrestock y estimación ordenada

## Cambios incluidos

- Cuando el cliente rechaza el sobrestock, el producto se retira del carrito. La cantidad queda conceptualmente en 0 y no puede pasar al checkout.
- La lista de entrega parcial del carrito se reorganizó en filas más claras: fecha, unidades, días estimados y explicación.
- La estimación usa un solo proveedor ganador interno. El cliente no ve datos de proveedor, costos internos ni ranking.
- Las fechas se calculan con datos del proveedor: `capacidad_diaria`, `unidades_disponibles`, `tiempo_entrega_estandar` y `distancia_km`.
- Los despachos parciales se separan por una ventana mínima calculada desde el tiempo estándar del proveedor para evitar fechas poco realistas.

## Fórmula de estimación

Para entrega total:

```text
fecha = hoy + tiempo_entrega_estandar + dias_produccion + logistica_por_distancia
```

Donde:

```text
dias_produccion = ceil(unidades_a_producir / capacidad_diaria)
logistica_por_distancia = ceil(distancia_km / 500)
```

Para entrega parcial:

- Primero se toma el stock inmediato de tienda/sucursal.
- Luego se usa el proveedor ganador interno.
- Si el proveedor tiene unidades disponibles, se programa un primer despacho con logística.
- Si debe producir, se divide en tandas calculadas por capacidad diaria.
- Las tandas se separan por un intervalo derivado de `tiempo_entrega_estandar`.

## Archivos modificados

- `carrito.php`
- `funciones_backorder.php`

No requiere migración SQL.
