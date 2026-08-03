# Fase 4: separar lógica de negocio

Fecha de generación: 2026-08-02 23:12:17

## Objetivo

Separar reglas y cálculos que antes estaban mezclados con archivos de página.

## Qué se separó en esta fase

```text
Precios y descuentos       -> app/Services/Catalogo/PricingService.php
SKU y números de serie     -> app/Services/Inventario/SerialNumberService.php
Capacidad de proveedores   -> app/Services/Proveedor/SupplierCapacityService.php
Mapa de reglas principales -> app/Services/ReglasNegocio/BusinessRulesReference.php
```

## Qué se mantuvo compatible

Las funciones antiguas siguen existiendo:

```text
emxCalcularPrecioProductoCarrito()
emxDescuentoVolumenProducto()
generarSKUProfesional()
generarSerieUnica()
validarSerieDevolucion()
emxProveedorCalcularDescuentoRango()
```

Pero ahora esas funciones llaman internamente a servicios.

## Qué no se movió todavía

No se movió todo `admin.php`, `carrito.php`, `checkout.php`, `proveedor.php` ni `mi_cuenta.php`.
Mover páginas completas corresponde a la Fase 5.

En Fase 4 se separan reglas y cálculos. En Fase 5 se separan vistas.
