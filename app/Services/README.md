# app/Services

Esta carpeta contiene servicios de lógica de negocio.

Un servicio no debe encargarse de dibujar HTML. Su responsabilidad es calcular,
validar o decidir reglas del negocio.

En Fase 4 se empezaron a separar reglas puras y fáciles de aislar:

```text
app/Services/Catalogo/PricingService.php
app/Services/Inventario/SerialNumberService.php
app/Services/Proveedor/SupplierCapacityService.php
app/Services/ReglasNegocio/BusinessRulesReference.php
```

Los archivos antiguos siguen existiendo como adaptadores o helpers de compatibilidad.
