# Reglas de negocio principales

## Precio e IVA

El precio visible al cliente se calcula partiendo del precio base del producto.

```text
precio_con_iva = precio_base * (1 + iva / 100)
```

## Descuentos secuenciales

Los descuentos se aplican paso a paso, no sumados directamente.

Orden:

```text
1. Descuento normal del producto.
2. Descuento por volumen.
3. Descuento por membresía o plan.
```

Ejemplo:

```text
precio con IVA = 100
descuento producto = 10 por ciento
precio queda = 90

descuento volumen = 5 por ciento
precio queda = 85.50
```

## Descuento por volumen del cliente

Si el admin configura rangos de cantidad, el sistema aplica el descuento cuando el cliente compra una cantidad dentro del rango.

Ejemplo:

```text
5 a 10 unidades = 10 por ciento
11 o más unidades = 15 por ciento
```

## Descuento por volumen del proveedor

El proveedor puede registrar rangos según unidades ofrecidas.

Esto ayuda a estimar qué descuento corresponde cuando se solicitan muchas unidades.

## SKU y número de serie

```text
SKU:
Identifica el producto o modelo.

Número de serie:
Identifica una unidad física específica.
```

Dos productos del mismo modelo pueden tener el mismo SKU, pero no la misma serie.

## Devoluciones

Para aceptar una devolución, el sistema debe poder validar que la serie devuelta pertenezca al pedido original.

## Reemplazos

Si se cambia por el mismo producto:

```text
No se genera nueva factura.
Se conserva la factura original.
Se asigna una nueva serie al reemplazo.
Se guarda trazabilidad.
```

## Reembolsos

Si se devuelve dinero después de facturar:

```text
Se genera nota de crédito.
La factura original no se elimina.
```

## Proveedores

La capacidad del proveedor permite estimar:

```text
unidades disponibles
capacidad diaria
capacidad semanal
tiempo de entrega
rango de descuento
```
