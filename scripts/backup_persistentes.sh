#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="${PROJECT_DIR:-/var/www/anderspace/electromax}"
BACKUP_DIR="${BACKUP_DIR:-$HOME/electromax_backups}"
STAMP="$(date +%Y%m%d_%H%M%S)"

mkdir -p "$BACKUP_DIR"

tar -czf "$BACKUP_DIR/uploads_storage_$STAMP.tar.gz" \
  -C "$PROJECT_DIR" public/uploads storage

echo "Backup creado: $BACKUP_DIR/uploads_storage_$STAMP.tar.gz"
