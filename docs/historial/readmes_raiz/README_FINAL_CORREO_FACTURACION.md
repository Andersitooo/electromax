# ElectroMax - versión con facturación, correo e historial admin

Este paquete mantiene la versión estable del proyecto y agrega el panel interno de historial de correos.

## Flujo de correo de facturación

1. El cliente registra o confirma su correo en checkout.
2. El pedido se crea con ese correo en la tabla `pedidos.email` y/o en `facturacion_datos`.
3. Cuando el admin aprueba el pago, el sistema genera la factura simulada.
4. Se genera el PDF de factura.
5. El sistema intenta enviar el PDF al correo del cliente con PHPMailer.
6. Si SMTP está configurado, el correo se envía y se registra como `enviado`.
7. Si SMTP no está configurado o falla, no se rompe el pedido: queda registrado como `pendiente` en `email_outbox` para simulación.

## Panel interno de correos

Entrar como ADMIN o SUPERADMIN a:

`http://localhost/electro2/correos_empresa.php`

También aparece en el menú de admin como **Historial de correos**.

El panel muestra:

- destino del correo,
- asunto,
- tipo de documento,
- estado: enviado, pendiente o error,
- fecha,
- vista previa del HTML,
- ruta del adjunto PDF si existe.

## Configurar correo real

Editar `config_correo.php` y cambiar solamente los datos SMTP:

```php
putenv('EMX_SMTP_HOST=smtp.gmail.com');
putenv('EMX_SMTP_PORT=587');
putenv('EMX_SMTP_SECURE=tls');
putenv('EMX_SMTP_USER=tu_correo@gmail.com');
putenv('EMX_SMTP_PASS=TU_APP_PASSWORD');
putenv('EMX_SMTP_FROM_EMAIL=tu_correo@gmail.com');
putenv('EMX_SMTP_FROM_NAME=ElectroMax Facturación');
```

No uses la contraseña normal si Gmail te pide contraseña de aplicación.

## Verificar PHPMailer

Abrir:

`http://localhost/electro2/verificar_phpmailer.php`

Si PHPMailer ya está instalado y `vendor/autoload.php` existe, debe cargar correctamente.

## SQL

Ejecutar en este orden si aún no lo hiciste:

```bash
psql -d electro2 -f migracion_facturacion_garantias_checkout.sql
psql -d electro2 -f migracion_empresa_config_admin.sql
psql -d electro2 -f migracion_email_outbox_panel.sql
```

También se incluye `migracion_final_correo_facturacion.sql`, que concatena esas migraciones para ejecutarlas en un solo paso.

Antes de ejecutar cualquier migración:

```bash
pg_dump -Fc electro2 > backup_antes_correo_facturacion.dump
```

## Datos de empresa

Entrar a:

`http://localhost/electro2/admin.php?module=empresa`

Ahí se configuran los datos usados en facturas y notas de crédito:

- razón social,
- nombre comercial,
- RUC,
- dirección matriz,
- teléfono,
- correo de facturación,
- establecimiento,
- punto de emisión,
- logo.

## Nota

Este paquete no incluye facturación electrónica real del SRI. Es facturación simulada académica con PDF, clave simulada y envío por correo.
