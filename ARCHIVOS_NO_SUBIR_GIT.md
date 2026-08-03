# Archivos que NO deben subirse a Git

Estos archivos/carpetas se quedan en el VPS y se generan con el uso del sistema:

```text
.env
vendor/
node_modules/
backup.backup
*.backup
*.dump
public/uploads/*
storage/logs/*
storage/facturas/*
storage/notas_credito/*
storage/comprobantes/*
storage/backups/*
```

Las carpetas vacías quedan con `.gitkeep` para que el sistema tenga la estructura base.
