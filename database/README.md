# database

Carpeta central de SQL del proyecto ElectroMax.

## Estructura

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

## Qué contiene cada carpeta

```text
schema:
Script base de instalación o estructura principal de la base de datos.

migrations:
Cambios evolutivos de la base de datos. Ejemplo: agregar columnas, crear tablas nuevas o índices.

hotfixes:
Correcciones puntuales para errores detectados durante pruebas.

functions:
Inventario y documentación de funciones SQL/PLpgSQL detectadas.

triggers:
Inventario y documentación de triggers detectados.

queries:
Consultas SQL sueltas o de apoyo.

seeds:
Datos iniciales o datos de prueba, si se agregan en fases posteriores.

scripts:
Scripts de apoyo para ejecución ordenada o revisión.
```

## Compatibilidad con archivos antiguos

Los archivos `.sql` antiguos de la raíz no fueron eliminados. Ahora son adaptadores.

Ejemplo:

```bash
psql -d electro2 -f migracion_google_login.sql
```

sigue funcionando si se ejecuta desde la raíz del proyecto, porque ese archivo llama al SQL real en:

```text
database/migrations/migracion_google_login.sql
```

## Recomendación

Para trabajo nuevo, usar siempre las rutas dentro de `database/`.
