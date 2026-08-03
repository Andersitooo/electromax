# ElectroMax - Fase 4

## Fase realizada

Se separó una primera parte de la lógica de negocio en servicios comentados.

## Servicios agregados

```text
app/Services/Catalogo/PricingService.php
app/Services/Inventario/SerialNumberService.php
app/Services/Proveedor/SupplierCapacityService.php
app/Services/ReglasNegocio/BusinessRulesReference.php
```

## Qué se documentó

```text
docs/fase4/EXPLICACION_FUNCIONES_CALCULOS_REGLAS.md
docs/fase4/COMO_EXPLICAR_FASE_4_DEFENSA.md
docs/fase4/MAPA_LOGICA_NEGOCIO_SEPARADA.md
docs/fase4/SERVICIOS_CREADOS_FASE_4.md
```

## Compatibilidad

Los helpers antiguos siguen funcionando, pero ahora delegan a servicios.

## Siguiente fase

Fase 5: separar vistas frontend, admin y proveedor.
