# Ajuste de calendario de sobrestock

Este ajuste cambia la estimación para que el cliente vea dos opciones claras:

- Opción A: entrega parcial con calendario visual interactivo.
- Opción B: entrega total con una sola fecha consolidada.

## Cambio importante

Aunque existan hasta 5 proveedores asociados a un producto, el cliente ya no ve varios proveedores mezclados. El sistema evalúa internamente los proveedores y selecciona el más conveniente para la empresa con base en:

- costo unitario estimado,
- tiempo estándar de entrega,
- unidades disponibles,
- capacidad diaria de producción,
- distancia logística,
- riesgo por defectos.

Con ese proveedor ganador se construyen los calendarios de cliente. Así se evita mostrar dos proveedores llegando el mismo día o información interna que no le aporta al cliente.

## Cálculo de fechas

La fecha estimada se calcula con datos de capacidad del proveedor:

```text
fecha = hoy + tiempo_entrega_estandar + días_de_producción + logística_por_distancia
```

Los días de producción se calculan así:

```text
días_de_producción = ceil(unidades_a_producir / capacidad_diaria)
```

En entrega parcial, las tandas se separan por producción acumulada para que no salgan dos despachos futuros con la misma fecha.

No requiere migración SQL.
