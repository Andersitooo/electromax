#!/usr/bin/env bash
set -euo pipefail

# Ejecutar como root o con sudo.
# Ajusta PROJECT_DIR si usas otra ruta.

PROJECT_DIR="${PROJECT_DIR:-/var/www/anderspace/electromax}"
WEB_USER="${WEB_USER:-apache}"

echo "Proyecto: $PROJECT_DIR"
echo "Usuario web: $WEB_USER"

mkdir -p "$PROJECT_DIR/public/uploads"
mkdir -p "$PROJECT_DIR/storage/logs" "$PROJECT_DIR/storage/cache" "$PROJECT_DIR/storage/temp"
mkdir -p "$PROJECT_DIR/storage/facturas" "$PROJECT_DIR/storage/notas_credito" "$PROJECT_DIR/storage/comprobantes"

chown -R "$WEB_USER:$WEB_USER" "$PROJECT_DIR/public/uploads" "$PROJECT_DIR/storage"
find "$PROJECT_DIR/public/uploads" "$PROJECT_DIR/storage" -type d -exec chmod 775 {} \;
find "$PROJECT_DIR/public/uploads" "$PROJECT_DIR/storage" -type f -exec chmod 664 {} \;

# SELinux en AlmaLinux: permite escritura a uploads/storage.
if command -v semanage >/dev/null 2>&1; then
  semanage fcontext -a -t httpd_sys_rw_content_t "$PROJECT_DIR/public/uploads(/.*)?" || true
  semanage fcontext -a -t httpd_sys_rw_content_t "$PROJECT_DIR/storage(/.*)?" || true
  restorecon -Rv "$PROJECT_DIR/public/uploads" "$PROJECT_DIR/storage" || true
else
  echo "Aviso: semanage no está instalado. Instala policycoreutils-python-utils si SELinux está activo."
fi

echo "Permisos aplicados."
