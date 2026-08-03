# ElectroMax - facturación simulada, garantías y checkout guiado

## 1. Instalar base de datos

Haz respaldo primero:

```bash
pg_dump -Fc electro2 > backup_antes_facturacion_garantias.dump
```

Luego ejecuta:

```bash
psql -d electro2 -f migracion_facturacion_garantias_checkout.sql
```

La migración es idempotente y no borra datos.

## 2. Flujo simple final

### Pedido y pago

1. Cliente acepta entrega normal, parcial o total.
2. Cliente pasa a checkout.
3. Checkout tiene 3 fases: dirección, facturación/pago, confirmación.
4. Al confirmar, el pedido queda en estado `Pendiente` y `estado_pago = pendiente_aprobacion`.
5. El stock se reserva/descuenta para que no lo compre otro cliente.
6. El cliente puede cancelar mientras el admin no apruebe el pago.
7. Admin ejecuta `Confirmar pago`.
8. Pedido pasa a `Pago confirmado`.
9. Se genera factura simulada PDF.
10. Se intenta enviar por PHPMailer al correo de facturación.

### Cancelación

- Cliente solo puede cancelar en estado `Pendiente` + `estado_pago = pendiente_aprobacion`.
- Al cancelar, se libera stock reservado/descontado y se cancelan backorders/cronogramas asociados.
- Si el admin ya confirmó el pago, el cliente no cancela desde cuenta. Debe entrar por devolución/incidencia.

### Factura

- No se emite factura antes de la aprobación del admin.
- Al aprobar pago se crea la factura.
- Si luego hay reembolso por devolución, se genera nota de crédito total simulada.
- La factura original no se borra.

### Garantía

- Durante los primeros 30 días se usa devolución normal.
- Después de 30 días se usa caso de garantía.
- Cada venta guarda snapshot de garantía en `detalle_pedidos`.
- Si cambias la garantía del producto en el futuro, las ventas pasadas conservan la garantía capturada al comprar.

## 3. PHPMailer

Instala dependencias en la carpeta del proyecto:

```bash
composer install
```

O si ya tienes Composer en el proyecto:

```bash
composer require phpmailer/phpmailer:^6.9
```

Configura variables de entorno en Apache/XAMPP o en tu hosting:

```env
EMX_SMTP_HOST=smtp.tudominio.com
EMX_SMTP_PORT=587
EMX_SMTP_SECURE=tls
EMX_SMTP_USER=facturacion@tudominio.com
EMX_SMTP_PASS=CONTRASENA_O_APP_PASSWORD
EMX_SMTP_FROM_EMAIL=facturacion@tudominio.com
EMX_SMTP_FROM_NAME=ElectroMax
```

Si SMTP no está configurado, el sistema no se rompe: guarda el correo en `email_outbox`.

## 4. Archivos principales modificados

- `checkout.php`
- `admin.php`
- `flujo_admin.php`
- `mi_cuenta.php`
- `funciones_home.php`

## 5. Archivos nuevos

- `migracion_facturacion_garantias_checkout.sql`
- `funciones_facturacion.php`
- `funciones_garantias.php`
- `factura_pdf.php`
- `garantia.php`
- `composer.json`

## 6. Casos cubiertos

- Pedido pendiente: cliente puede cancelar.
- Pedido aprobado por admin: no se puede cancelar desde cliente.
- Factura simulada: se genera al aprobar pago.
- Reembolso: genera nota de crédito.
- Cambio por mismo producto: se gestiona como reemplazo, no nueva factura.
- Garantía posterior a 30 días: se registra como caso de garantía, no devolución común.
- Recomendados: se limitan a la misma categoría para evitar recomendar lavadoras cuando el cliente mira televisores.
