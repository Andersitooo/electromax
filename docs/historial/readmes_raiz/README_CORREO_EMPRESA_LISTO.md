# ElectroMax - correo y usuario de empresa listo para configurar

Este paquete ya trae `config_correo.php` creado. Solo debes cambiar los datos de ejemplo por los datos SMTP reales.

## 1. Qué debes cambiar en `config_correo.php`

Abre:

```text
C:\xampp\htdocs\electro2\config_correo.php
```

Cambia estos valores:

```php
putenv('EMX_SMTP_HOST=smtp.tudominio.com');
putenv('EMX_SMTP_PORT=587');
putenv('EMX_SMTP_SECURE=tls');
putenv('EMX_SMTP_USER=facturacion@tudominio.com');
putenv('EMX_SMTP_PASS=CAMBIA_ESTA_PASSWORD_SMTP');
putenv('EMX_SMTP_FROM_EMAIL=facturacion@tudominio.com');
putenv('EMX_SMTP_FROM_NAME=ElectroMax Facturación');
```

Mientras esos valores sigan como ejemplo, el sistema **no intentará enviar correo real**. Guardará el correo en `email_outbox` como simulación.

## 2. Verificar PHPMailer

Abre en navegador:

```text
http://localhost/electro2/verificar_phpmailer.php
```

O desde XAMPP Shell:

```bat
php verificar_phpmailer.php
```

Si PHPMailer no está instalado, ejecuta:

```bat
instalar_phpmailer_windows.bat
```

## 3. Probar envío real

Cuando ya pongas datos SMTP reales, prueba desde consola:

```bat
php probar_correo_facturacion.php tu_correo@dominio.com
```

Si llega ese correo, entonces las facturas podrán enviarse al cliente.

## 4. Modo prueba

En `config_correo.php` viene activado:

```php
putenv('EMX_MAIL_MODO_PRUEBA=1');
putenv('EMX_MAIL_CORREO_PRUEBA=tu_correo_de_prueba@tudominio.com');
```

Con eso, aunque una factura diga que va para el cliente, se enviará al correo de prueba. Cuando ya estés seguro, cambia:

```php
putenv('EMX_MAIL_MODO_PRUEBA=0');
```

## 5. Usuario interno de empresa

Si quieres crear un usuario interno para la empresa/facturación, usa:

```text
crear_usuario_empresa.php
```

Antes de ejecutarlo, edita estos datos dentro del archivo:

```php
$email = 'facturacion@tudominio.com';
$passwordPlano = 'CAMBIAR_ESTA_CLAVE_SEGURA_123';
$telefonoEmpresa = '04-273-0000';
$nombres = 'ElectroMax';
$apellidos = 'Facturación';
$cedulaRuc = '0999999999001';
$rolNombre = 'ADMIN';
```

Luego ejecuta desde XAMPP Shell:

```bat
php crear_usuario_empresa.php
```

Ese usuario sirve para iniciar sesión en el sistema como cuenta de empresa/admin. No reemplaza el correo SMTP.

## 6. Sobre el número de celular/teléfono

Si una plataforma externa como Gmail u otro proveedor te pide celular para crear el correo, eso depende del proveedor y no se debe saltar. Para ElectroMax puedes usar un teléfono corporativo o fijo en el usuario de empresa.

## 7. Datos de empresa para factura

Los datos legales/simulados de la empresa se editan desde:

```text
http://localhost/electro2/admin.php?module=empresa
```

Ahí configuras razón social, RUC, dirección, correo, establecimiento, punto de emisión, logo y datos que salen en factura.
