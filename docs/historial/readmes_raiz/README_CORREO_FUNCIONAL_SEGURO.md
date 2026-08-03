# ElectroMax - Correo funcional seguro

Este paquete deja `config_correo.php` preparado para enviar correos desde:

`abustamante831@fafi.utb.edu.ec`

hacia el correo del cliente capturado en checkout/pedido.

## Paso obligatorio

Por seguridad, la contraseña real NO va dentro del ZIP.

1. Revoca la contraseña de aplicación que compartiste en el chat.
2. Genera una nueva contraseña de aplicación.
3. Abre `config_correo.php`.
4. Cambia esta línea:

```php
putenv('EMX_SMTP_PASS=PEGA_AQUI_TU_NUEVA_APP_PASSWORD');
```

por tu nueva contraseña de aplicación.

## Modo real

El archivo queda con:

```php
putenv('EMX_MAIL_MODO_PRUEBA=0');
```

Eso significa que enviará al correo real del cliente.

## Verificar

Abre:

`http://localhost/electro2/verificar_phpmailer.php`

Debe mostrar que PHPMailer y los campos SMTP están configurados.

## Flujo

Admin aprueba pago → se genera factura PDF → PHPMailer envía al correo del cliente → queda registro en Historial de correos.
