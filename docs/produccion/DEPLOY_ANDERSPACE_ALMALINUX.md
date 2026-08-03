# Despliegue ElectroMax en anderspace.online / AlmaLinux

Fecha: 2026-08-03 00:52:04

## Objetivo

Dejar el proyecto listo para subirlo al VPS AlmaLinux y servirlo en:

```text
https://anderspace.online
```

## Estructura recomendada en el VPS

```text
/var/www/anderspace/electromax/
  app/
  bootstrap/
  database/
  public/
  storage/
  views/
  .env
```

## DocumentRoot recomendado

Lo más seguro es apuntar Apache directamente a:

```text
/var/www/anderspace/electromax/public
```

Así el navegador no podrá acceder a:

```text
app/
database/
storage/
views/
docs/
scripts/
```

## VirtualHost Apache recomendado

Archivo sugerido:

```text
/etc/httpd/conf.d/anderspace.online.conf
```

Contenido base:

```apache
<VirtualHost *:80>
    ServerName anderspace.online
    ServerAlias www.anderspace.online

    DocumentRoot /var/www/anderspace/electromax/public

    <Directory /var/www/anderspace/electromax/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    ErrorLog /var/log/httpd/anderspace_error.log
    CustomLog /var/log/httpd/anderspace_access.log combined
</VirtualHost>
```

Cuando agregues SSL, Certbot normalmente creará o ajustará el VirtualHost HTTPS.

## Paquetes útiles en AlmaLinux

```bash
sudo dnf update -y
sudo dnf install -y httpd unzip git composer
sudo dnf install -y php php-cli php-pdo php-pgsql php-mbstring php-json php-xml php-gd php-intl php-opcache
sudo dnf install -y policycoreutils-python-utils
```

## Subida del proyecto

Sube el ZIP a:

```text
/var/www/anderspace/
```

y descomprímelo como:

```text
/var/www/anderspace/electromax
```

## Configuración .env

En el VPS:

```bash
cd /var/www/anderspace/electromax
cp .env.production.example .env
nano .env
```

Edita:

```text
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASSWORD
EMX_SMTP_USER
EMX_SMTP_PASS
EMX_SMTP_FROM_EMAIL
```

## Composer / PHPMailer

Para enviar correos con facturas y notas de crédito:

```bash
cd /var/www/anderspace/electromax
composer install --no-dev --optimize-autoloader
```

Eso instalará PHPMailer dentro de:

```text
vendor/
```

## Permisos

```bash
cd /var/www/anderspace/electromax
sudo bash scripts/post_deploy_permissions_almalinux.sh
```

Si tu ruta es distinta:

```bash
sudo PROJECT_DIR=/ruta/real/electromax bash scripts/post_deploy_permissions_almalinux.sh
```

## Verificación

```bash
php scripts/verificar_produccion_anderspace.php
php scripts/verificar_estructura_final_neta.php
php scripts/verificar_rutas_raiz_final.php
php scripts/verificar_favicon_global.php
```

## Carpetas que nunca debes borrar en actualizaciones

```text
public/uploads/
storage/
.env
vendor/
```

Antes de actualizar:

```bash
bash scripts/backup_persistentes.sh
```

## Si el hosting/VPS no apunta a public

Este proyecto también trae `.htaccess` en la raíz para redirigir internamente hacia `public/` y bloquear carpetas internas.

Aun así, para VPS propio, lo recomendado es:

```text
DocumentRoot = /var/www/anderspace/electromax/public
```
