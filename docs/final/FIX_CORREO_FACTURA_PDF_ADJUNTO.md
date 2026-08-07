# Fix correo de facturación con PDF adjunto

## Problema corregido
El correo de factura podía enviarse con el cuerpo HTML correcto, pero sin adjuntar el PDF.

La causa más probable era que el PDF se intentaba generar en una ruta no persistente/no escribible por PHP-FPM en producción:

```text
documentos/facturacion/facturas/...
```

En el VPS el usuario web `nginx` tiene permisos preparados principalmente sobre:

```text
storage/
public/uploads/
```

Si el PDF no se creaba físicamente, PHPMailer enviaba el correo sin adjunto porque el archivo no existía.

## Cambios aplicados

- Las facturas nuevas ahora generan el PDF en:

```text
storage/facturas/YYYY/MM/factura_NUMERO.pdf
```

- Las notas de crédito nuevas ahora generan el PDF en:

```text
storage/notas_credito/YYYY/MM/nota_credito_NUMERO.pdf
```

- Antes de adjuntar, el sistema valida que el archivo exista, sea legible y pese más de 0 bytes.
- Si falta el adjunto, se registra en `error_log` para detectar el problema.
- Se agregó un script para regenerar PDFs de facturas anteriores que tengan `pdf_url` apuntando a una ruta inexistente.

## Archivos modificados

```text
app/Helpers/funciones_facturacion.php
scripts/verificar_fix_factura_correo_pdf_adjunto.php
scripts/reparar_facturas_pdf_faltantes.php
```

## Comandos después de actualizar en el VPS

```bash
cd /var/www/anderspace/electromax

git pull
composer install --no-dev --optimize-autoloader

php scripts/verificar_fix_factura_correo_pdf_adjunto.php
php scripts/reparar_facturas_pdf_faltantes.php

sudo WEB_USER=nginx PROJECT_DIR=/var/www/anderspace/electromax bash scripts/post_deploy_permissions_almalinux.sh
sudo systemctl restart php-fpm
sudo systemctl reload nginx
```

## Verificar PDFs existentes

```bash
find /var/www/anderspace/electromax/storage/facturas -type f -name "*.pdf" | tail -n 20
```

## Verificar facturas en base de datos

```bash
psql -h 127.0.0.1 -U ecommerce_user -d ecommerce_db -c "SELECT numero_factura, pdf_url, enviada_email, email_enviado_at FROM facturas ORDER BY created_at DESC LIMIT 10;"
```

## Nota
Este fix corrige facturas nuevas y permite regenerar PDFs faltantes. Si necesitas reenviar un correo viejo, primero regenera el PDF con el script y luego vuelve a generar/reenviar desde el flujo del sistema o panel correspondiente.
