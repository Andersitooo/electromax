# Devoluciones: fecha estimada recalculada para reemplazos y prioridad Prime

Este parche corrige el flujo de cambio por producto igual:

1. Cuando el admin crea el reemplazo (`crear_reemplazo`), el sistema crea un nuevo pedido de reemplazo.
2. Ese nuevo pedido recibe una fecha estimada propia, calculada desde el momento del reemplazo, no desde la compra original.
3. La fecha considera:
   - distancia del pedido original,
   - stock disponible para el reemplazo,
   - corte operativo después de las 17:00,
   - prioridad Prime si el cliente tiene plan Prime.
4. En `tracking.php` el cliente ve:
   - fecha estimada actualizada,
   - etiqueta de prioridad Prime o estándar,
   - mensaje logístico del reemplazo.
5. Cuando el admin marca el reemplazo como `reemplazo_en_transito`, se recalcula otra vez la fecha estimada como fecha de tránsito restante.

SQL incluido:
- `migracion_devoluciones_estimacion_reemplazo_prime.sql`

Ejecutar una sola vez:

```bash
psql -d electro2 -f migracion_devoluciones_estimacion_reemplazo_prime.sql
```
