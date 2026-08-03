# Explicación de la carpeta database

La carpeta `database` guarda todo lo relacionado con la base de datos.

## schema

Contiene la estructura base.

Ejemplo:

```text
database/schema/bd.sql
```

Este archivo sirve como punto de partida para crear la base de datos.

## migrations

Contiene cambios incrementales.

Ejemplo:

```text
database/migrations/migracion_google_login.sql
```

Una migración modifica la base ya existente.

## hotfixes

Contiene correcciones específicas.

Ejemplo:

```text
database/hotfixes/hotfix_notificaciones_wishlist_php_final.sql
```

Un hotfix no siempre se ejecuta. Solo se usa cuando corresponde a un problema.

## functions

Aquí se documentan las funciones SQL detectadas.

Una función SQL es lógica que vive dentro de PostgreSQL.

## triggers

Aquí se documentan los triggers detectados.

Un trigger es una acción automática de la base de datos que se ejecuta ante eventos como INSERT, UPDATE o DELETE.
