# Ajuste de carrito: calendario de sobrestock ordenado

## Cambios realizados

- Se corrigió la presentación del calendario de entrega parcial para que no se vea comprimido.
- Las opciones A y B ahora ocupan todo el ancho del bloque de sobrestock, en vez de quedar una al lado de la otra en columnas angostas.
- Cada despacho se muestra como una fila horizontal clara: fecha, título, unidades, tiempo estimado y descripción.
- El botón "Rechazar sobrestock" mantiene la lógica acordada: elimina el producto del carrito y evita que pase al checkout.
- La estimación sigue conectada a los datos del proveedor ganador interno: capacidad diaria, unidades disponibles, tiempo estándar de entrega y distancia.
- El cliente no ve proveedor ganador, ranking, costo interno ni score de empresa.

## Cálculo de estimación

El sistema evalúa hasta 5 proveedores asociados al producto. Internamente escoge el mejor para la empresa por costo, tiempo, disponibilidad y riesgo. El cliente solo ve el calendario final.

Fecha estimada aproximada:

hoy + tiempo_entrega_estandar + días de producción + logística por distancia

Días de producción:

ceil(cantidad_a_producir / capacidad_diaria)

## Archivos modificados

- carrito.php

No requiere cambios en la base de datos.
