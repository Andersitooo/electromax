# Explicación de funciones, cálculos y reglas

Este documento está escrito para poder explicar el proyecto en una defensa.

## 1. Cálculo de precio del cliente

Archivo:

```text
app/Services/Catalogo/PricingService.php
```

Función principal:

```php
ElectroMaxPricingService::calcularPrecioProductoCarrito()
```

### Qué hace

Calcula el precio final de un producto cuando el cliente lo agrega al carrito o pasa al checkout.

### Pasos

```text
1. Toma el precio base del producto.
2. Calcula el IVA.
3. Aplica el descuento normal del producto si está activo.
4. Aplica descuento por volumen si la cantidad comprada entra en un rango.
5. Aplica descuento por plan o membresía si el cliente tiene beneficios.
6. Devuelve el precio final y el desglose de descuentos.
```

### Fórmula del IVA

```text
precio_con_iva = precio_base * (1 + iva / 100)
```

Ejemplo:

```text
precio_base = 100
iva = 15

precio_con_iva = 100 * (1 + 15 / 100)
precio_con_iva = 115
```

### Descuentos secuenciales

Los descuentos no se suman directamente. Se aplican uno después del otro.

Ejemplo:

```text
precio_con_iva = 100
descuento_producto = 10 por ciento
descuento_volumen = 5 por ciento
```

Cálculo correcto:

```text
100 - 10 por ciento = 90
90 - 5 por ciento = 85.50
```

No se calcula como 15 por ciento directo, porque eso daría 85.00.

La razón es que el segundo descuento se aplica sobre el precio ya rebajado.

## 2. Descuento normal de producto

Función:

```php
ElectroMaxPricingService::descuentoProductoActivoPct()
```

### Regla

Un descuento de producto solo aplica si:

```text
1. El porcentaje es mayor a cero.
2. La fecha actual es mayor o igual a descuento_desde.
3. La fecha actual es menor o igual a descuento_hasta.
```

Si la fecha está fuera del rango, el descuento no se aplica.

## 3. Descuento por volumen del cliente

Función:

```php
ElectroMaxPricingService::descuentoVolumenProducto()
```

### Regla

El producto puede tener rangos configurados por el admin.

Ejemplo:

```text
5 a 10 unidades = 10 por ciento
11 o más unidades = 15 por ciento
```

Si el cliente compra 6 unidades, aplica 10 por ciento.

Si compra 12 unidades, aplica 15 por ciento.

## 4. Códigos SKU y series

Archivo:

```text
app/Services/Inventario/SerialNumberService.php
```

### Diferencia entre SKU y serie

```text
SKU:
Identifica el producto o modelo.

Serie:
Identifica una unidad física específica.
```

Ejemplo:

```text
Producto: TV Samsung 55
SKU: EMX-TEL-A1B2C3

Unidad 1:
Serie: SAM-2026-X9F2A1C4

Unidad 2:
Serie: SAM-2026-91AB88CD
```

Dos unidades pueden tener el mismo SKU, pero no la misma serie.

## 5. Validación de serie devuelta

Función:

```php
ElectroMaxSerialNumberService::validarSerieDevolucion()
```

### Regla

Cuando un cliente devuelve un producto, el sistema debe validar que la serie recibida coincida con una serie vendida en ese pedido.

Si coincide, el caso puede continuar.

Si no coincide, se considera alerta de posible fraude o error de recepción.

## 6. Capacidad de proveedor

Archivo:

```text
app/Services/Proveedor/SupplierCapacityService.php
```

### Qué controla

```text
1. Capacidad diaria.
2. Capacidad semanal.
3. Capacidad máxima por pedido.
4. Tiempo de entrega.
5. Distancia.
6. Velocidad promedio.
7. Rangos de descuento por volumen.
```

## 7. Días de producción

Función:

```php
ElectroMaxSupplierCapacityService::calcularDiasProduccion()
```

### Fórmula

```text
dias_produccion = techo(cantidad_solicitada / capacidad_diaria)
```

Ejemplo:

```text
cantidad_solicitada = 45
capacidad_diaria = 20

45 / 20 = 2.25

Se redondea hacia arriba:
dias_produccion = 3
```

Se redondea hacia arriba porque si falta una parte de producción, igual ocupa otro día operativo.

## 8. Transporte por distancia

Función:

```php
ElectroMaxSupplierCapacityService::calcularDiasTransporte()
```

### Fórmula

```text
horas = distancia_km / velocidad_promedio_kmh
dias = techo(horas / 24)
```

Ejemplo:

```text
distancia = 300 km
velocidad = 60 km/h

horas = 300 / 60 = 5
dias = techo(5 / 24) = 1
```

## 9. Por qué se separó así

La separación permite explicar el proyecto con más claridad:

```text
Las páginas muestran información.
Los servicios calculan reglas.
Los helpers antiguos conservan compatibilidad.
La base de datos guarda datos y estados.
```
