# Instalar PHPMailer en ElectroMax sin equivocarse

PHPMailer se instala recomendado por Composer. Este paquete ya trae `composer.json` y el código de facturación ya busca `vendor/autoload.php` automáticamente.

## Opción recomendada en Windows/XAMPP

1. Copia el proyecto a:

```text
C:\xampp\htdocs\electro2
```

2. Abre **XAMPP Control Panel**.

3. Abre **Shell** o abre CMD en la carpeta del proyecto.

4. Ejecuta:

```bat
instalar_phpmailer_windows.bat
```

Ese archivo:

- verifica que PHP esté disponible,
- descarga Composer local como `composer.phar`,
- instala `phpmailer/phpmailer`,
- crea la carpeta `vendor/`,
- ejecuta `verificar_phpmailer.php`.

## Si ya tienes Composer instalado

Puedes ejecutar:

```bat
instalar_phpmailer_si_tengo_composer.bat
```

O manualmente:

```bash
composer require phpmailer/phpmailer:^6.9
```

## Configurar correo

Copia:

```text
config_correo.example.php
```

como:

```text
config_correo.php
```

y llena tus datos SMTP.

## Verificar

Abre en el navegador:

```text
http://localhost/electro2/verificar_phpmailer.php
```

Debe mostrar:

```text
PHPMailer está instalado y carga correctamente.
```

## Producción

En producción sube también la carpeta `vendor/`. No subas contraseñas a repositorios públicos.

Si el SMTP no está configurado o falla, ElectroMax no se rompe: guarda el correo en `email_outbox` como simulación académica.
