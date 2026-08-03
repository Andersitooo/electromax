# Adaptadores SQL de compatibilidad

## Qué cambió

Los archivos SQL reales ahora viven en `database/`.

Pero los archivos antiguos en la raíz siguen existiendo como adaptadores.

## Ejemplo

Archivo antiguo:

```text
migracion_google_login.sql
```

Contenido actual:

```sql
\i database/migrations/migracion_google_login.sql
```

## Por qué se hizo

Para que comandos antiguos sigan funcionando.

Ejemplo:

```bash
psql -d electro2 -f migracion_google_login.sql
```

Esto permite organizar sin romper el flujo de trabajo anterior.

## Recomendación nueva

Usar directamente:

```bash
psql -d electro2 -f database/migrations/migracion_google_login.sql
```
