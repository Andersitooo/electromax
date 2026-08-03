# Nota de crédito por correo automático

Cambio aplicado:
- Cuando el admin ejecuta un reembolso, el sistema genera la nota de crédito y ahora también intenta enviarla automáticamente por correo al cliente.
- Si SMTP está configurado correctamente, se envía con PDF adjunto.
- Si SMTP no está configurado o falla, queda registrada como pendiente en `email_outbox`, igual que las facturas.
- El correo queda con tipo `nota_credito` para verlo en el historial de correos.
- Si la nota de crédito ya existía para esa devolución, el sistema intenta enviarla si todavía no estaba enviada.

Ejecuta una vez:

```bash
psql -d electro2 -f migracion_nota_credito_correo_auto.sql
```

Flujo:
```text
Admin ejecuta reembolso
↓
Sistema genera nota de crédito
↓
Sistema genera PDF
↓
Sistema envía correo al cliente con PDF adjunto
↓
Si no hay SMTP, queda en email_outbox como pendiente
↓
Cliente también mantiene la notificación interna de reembolso completado
```

Archivo principal modificado:
- `funciones_facturacion.php`
