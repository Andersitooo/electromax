# Hotfix final notificaciones wishlist por PHP

Este parche vuelve a activar la notificación desde `admin.php`, porque ahí es donde guardas el producto.

Qué corrige:
- Si el cliente tiene el producto en wishlist y el admin cambia el descuento, ahora se crea notificación.
- Funciona para 0→10, 10→20, 20→30, etc.
- Si el descuento está guardado como `0.10` o `0.1`, la notificación muestra `10%`, no `0.1%`.
- Si baja el precio base, notifica.
- Si vuelve stock, notifica.
- Se quitó el mensaje visible `Notificaciones enviadas: 1`.
- El admin solo verá: `Producto actualizado`.

SQL:
Ejecuta una vez:

```bash
psql -d electro2 -f hotfix_notificaciones_wishlist_php_final.sql
```

Prueba:
1. Cliente agrega producto a wishlist.
2. Admin cambia descuento.
3. El admin verá solo `Producto actualizado`.
4. Cliente entra a notificaciones y debe ver la alerta.
