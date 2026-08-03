# Cómo ejecutar el proyecto

## Requisitos

```text
XAMPP o Apache con PHP
PostgreSQL
Extensión PDO PostgreSQL habilitada
Navegador web
```

## Ubicación recomendada

```text
C:\xampp\htdocs\electro2
```

## Base de datos

Crear la base:

```bash
createdb electro2
```

Ejecutar el script base si es instalación nueva:

```bash
psql -d electro2 -f database/schema/bd.sql
```

Ejecutar migraciones necesarias:

```bash
psql -d electro2 -f database/migrations/nombre_migracion.sql
```

## Configuración

Revisar:

```text
app/Config/database.php
app/Config/mail.php
app/Config/google.php
```

## Abrir el sistema

```text
http://localhost/electro2/index.php
```

## Panel admin

```text
http://localhost/electro2/admin.php
```

## Panel proveedor

```text
http://localhost/electro2/proveedor.php
```

## Validaciones disponibles

Validar adaptadores:

```bash
php scripts/verificar_adaptadores_fase7.php
```

Validar limpieza:

```bash
php scripts/verificar_limpieza_fase8.php
```
