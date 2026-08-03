# Hotfix notificaciones de descuento en wishlist

Problema corregido:
- Un cliente tenía un producto en wishlist.
- El admin aplicaba descuento desde productos.
- No llegaba la notificación al cliente.

Qué cambia:
1. `crearNotificacion()` ahora es flexible:
   - Detecta columnas reales de la tabla `notificaciones`.
   - Usa `producto_id`, `enlace_accion`, `tipo_enlace`, `leida` y fecha solo si existen.
   - Evita que la notificación falle silenciosamente por una columna faltante.

2. Admin productos:
   - Después de actualizar el producto, vuelve a leer el valor real guardado en BD.
   - Compara descuento anterior vs descuento nuevo.
   - Notifica si el descuento cambia de 0 a 10, de 10 a 20, de 15 a 25, etc.
   - También notifica si baja el precio base o si vuelve stock.
   - Muestra en el mensaje del admin:
     - `Notificaciones enviadas: X`
     - o `Sin clientes con este producto en wishlist`

3. SQL incluido:
   - `hotfix_notificaciones_descuento_wishlist.sql`

Ejecuta una vez:
```bash
psql -d electro2 -f hotfix_notificaciones_descuento_wishlist.sql
```

Prueba:
1. Entra como cliente y agrega un producto a wishlist.
2. Entra como admin y cambia descuento de 0 a 10.
3. Debe salir mensaje: `Notificaciones enviadas: 1`.
4. Cambia descuento de 10 a 20.
5. Debe volver a notificar.
