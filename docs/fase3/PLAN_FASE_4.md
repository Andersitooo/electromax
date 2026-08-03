# Plan Fase 4: separar lógica de negocio

## Objetivo

Separar reglas de negocio de archivos grandes.

## Archivos candidatos

```text
admin.php
proveedor.php
carrito.php
checkout.php
mi_cuenta.php
producto.php
flujo_admin.php
```

## Servicios propuestos

```text
app/Services/OrderService.php
app/Services/CartService.php
app/Services/CheckoutService.php
app/Services/ProductService.php
app/Services/ReturnService.php
app/Services/ReplacementService.php
app/Services/SupplierCapacityService.php
app/Services/PricingService.php
app/Services/NotificationService.php
```

## Regla de la Fase 4

No cambiar la interfaz visual todavía.

Primero se separan cálculos y reglas.

Luego, en Fase 5, se separan vistas.
