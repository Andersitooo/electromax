# Hotfix definitivo de notificaciones wishlist

Problema:
- El cliente tenía el producto en wishlist.
- El admin cambiaba el descuento y visualmente sí se aplicaba.
- Pero no llegaba la notificación.

Solución:
- Las notificaciones de wishlist por descuento, stock y precio ahora se generan desde PostgreSQL con un TRIGGER.
- Así funciona aunque el cambio venga desde admin.php u otra parte del sistema.
- Se quitó el texto visible de admin que decía `Notificaciones enviadas: X`.
- Ahora el admin solo verá `Producto actualizado`.

Ejecuta una vez:

```bash
psql -d electro2 -f hotfix_notificaciones_wishlist_trigger.sql
```

Luego prueba:

1. Entra con cliente y agrega el producto a wishlist.
2. Entra como admin.
3. Cambia el descuento de 0 a 10.
4. Guarda.
5. Revisa notificaciones del cliente.
6. Cambia de 10 a 20.
7. Debe volver a notificar.

También notifica:
- cuando un producto de wishlist vuelve a tener stock
- cuando baja el precio base
