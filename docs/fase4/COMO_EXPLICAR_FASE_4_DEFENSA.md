# Cómo explicar la Fase 4 en una defensa

Puede explicarse así:

En esta fase separé la lógica de negocio de las páginas. Antes, algunos cálculos estaban mezclados con helpers o archivos de pantalla. Eso hacía más difícil mantener el sistema y explicar de dónde salía cada resultado.

Ahora los cálculos principales están en servicios:

```text
PricingService:
Calcula precios, IVA y descuentos.

SerialNumberService:
Genera SKU, genera series y valida series devueltas.

SupplierCapacityService:
Calcula reglas relacionadas con la capacidad de producción del proveedor.
```

La ventaja es que las páginas ya no tienen que saber cómo se calcula todo. Solo llaman al servicio correspondiente.

También dejé compatibilidad con las funciones antiguas para que el sistema no se rompa mientras se reorganiza por fases.
