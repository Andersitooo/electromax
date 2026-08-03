# Reemplazos con nueva serie y trazabilidad

Cambio agregado:
- Cuando el admin crea un reemplazo por el mismo producto, el sistema asigna nuevas series al producto enviado como reemplazo.
- La factura original se conserva.
- No se genera nueva factura.
- Se guarda la relación:
  - serie original vendida/devuelta
  - serie nueva enviada
  - pedido original
  - pedido de reemplazo
  - devolución relacionada

Archivos modificados:
- `flujo_admin.php`

SQL incluido:
- `migracion_reemplazo_series_trazabilidad.sql`

Ejecutar una vez:

```bash
psql -d electro2 -f migracion_reemplazo_series_trazabilidad.sql
```

Flujo resultante:

```text
Factura original:
Producto vendido con serie ABC-001

Cliente devuelve:
Serie recibida ABC-001

Admin crea reemplazo:
Sistema crea pedido de reemplazo
Sistema asigna nueva serie ABC-089
Sistema guarda trazabilidad en detalle_pedidos y devoluciones
Cliente puede ver la nueva serie en el tracking del pedido de reemplazo
```

Notas:
- Si el técnico registra la serie devuelta, el sistema identifica el producto correcto del pedido.
- Si la serie devuelta no coincide con ninguna serie vendida, se mantiene la alerta de fraude.
- Si el caso no tiene serie registrada, el sistema genera reemplazo para los productos del pedido original como comportamiento heredado.
