# ElectroMax - Fase 6

## Fase realizada

Se organizaron los archivos SQL del proyecto.

## Nueva estructura

```text
database/schema
database/migrations
database/hotfixes
database/functions
database/triggers
database/queries
database/seeds
database/scripts
```

## Archivos SQL organizados

```text
22
```

## Compatibilidad

Los archivos `.sql` antiguos de raíz quedaron como adaptadores.

Ejemplo:

```bash
psql -d electro2 -f migracion_google_login.sql
```

sigue funcionando desde la raíz del proyecto.

## Uso recomendado nuevo

```bash
psql -d electro2 -f database/migrations/migracion_google_login.sql
```

## SQL requerido para esta fase

Ninguno. Esta fase solo organiza archivos.

## Siguiente fase

Fase 7: dejar y revisar adaptadores para que las rutas antiguas no se rompan.
