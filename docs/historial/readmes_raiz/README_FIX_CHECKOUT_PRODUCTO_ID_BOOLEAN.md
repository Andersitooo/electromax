# Parche checkout: producto_id y boolean PostgreSQL

Corrige estos errores:

```text
Warning: Undefined array key "producto_id" in checkout.php
SQLSTATE[22P02]: Invalid text representation: la sintaxis de entrada no es válida para tipo boolean: «»
```

## Causa

1. En el resumen visual de checkout se intentaba leer `producto_id`, pero el arreglo `$productos_carrito` no lo estaba incluyendo.
2. PostgreSQL no acepta cadena vacía `''` para columnas booleanas. En algunas configuraciones de PDO, `false` puede llegar como cadena vacía al insertar direcciones.

## Corrección

- El resumen del checkout ahora incluye `producto_id`.
- La consulta de backorder en la vista es segura si falta `producto_id`.
- Los booleanos enviados por `emxCheckoutInsertFlexible()` se normalizan a `true` / `false`.
- `direcciones_usuario.es_principal` se guarda como `false` válido para PostgreSQL.

No requiere SQL.
