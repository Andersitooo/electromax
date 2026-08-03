# Adaptadores y compatibilidad en Fase 4

## Por qué se conservaron funciones antiguas

El proyecto todavía tiene archivos que llaman funciones antiguas.

Ejemplo:

```php
emxCalcularPrecioProductoCarrito()
generarSerieUnica()
emxProveedorCalcularDescuentoRango()
```

Si se eliminaban esas funciones, se rompían páginas.

## Solución

Las funciones antiguas se mantienen, pero ahora llaman a servicios.

Ejemplo:

```php
function emxCalcularPrecioProductoCarrito(...) {
    return ElectroMaxPricingService::calcularPrecioProductoCarrito(...);
}
```

Así el sistema sigue funcionando y la lógica queda mejor organizada.
