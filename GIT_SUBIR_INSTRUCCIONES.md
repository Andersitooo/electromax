# ElectroMax - carpeta lista para subir a GitHub

Esta carpeta está preparada para GitHub. Incluye el código del sistema y excluye archivos privados o generados.

## Sí va a GitHub

- `app/`
- `bootstrap/`
- `database/`
- `docs/`
- `public/` excepto archivos reales dentro de `public/uploads/`
- `routes/`
- `scripts/`
- `views/`
- `composer.json`
- `composer.lock` si existe
- `.gitignore`
- `.env.example`
- `.env.production.example`
- `README.md`

## No va a GitHub

- `.env`
- `vendor/`
- `node_modules/`
- `backup.backup`
- `*.dump`
- backups reales de PostgreSQL
- imágenes/fotos reales subidas en `public/uploads/`
- facturas, notas de crédito, comprobantes y logs reales en `storage/`

## Comandos para subir desde Windows CMD

Entra a esta carpeta:

```cmd
cd "RUTA\A\electromax"
```

Inicializa Git y revisa:

```cmd
git init
git branch -M main
git status
```

Si en `git status` aparece `.env`, `backup.backup`, `vendor/`, `public/uploads` con imágenes reales o `storage/facturas`, no hagas commit todavía.

Luego:

```cmd
git add .
git status
git commit -m "Version produccion inicial Electromax"
git remote add origin https://github.com/TU_USUARIO/electromax.git
git push -u origin main
```

## En el VPS

Cuando el repo esté en GitHub:

```bash
sudo mkdir -p /var/www/anderspace
sudo chown -R deploy:deploy /var/www/anderspace
cd /var/www/anderspace
git clone https://github.com/TU_USUARIO/electromax.git electromax
cd electromax
cp .env.production.example .env
nano .env
composer install --no-dev --optimize-autoloader
sudo WEB_USER=nginx PROJECT_DIR=/var/www/anderspace/electromax bash scripts/post_deploy_permissions_almalinux.sh
```

En `.env` del VPS usa:

```env
APP_ENV=production
APP_URL=https://anderspace.online
APP_DEBUG=0
APP_TIMEZONE=America/Guayaquil

DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=ecommerce_db
DB_USER=ecommerce_user
DB_PASSWORD=CAMBIA_ESTA_CLAVE
```
