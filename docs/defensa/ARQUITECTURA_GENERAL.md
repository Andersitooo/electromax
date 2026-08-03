# Arquitectura general del proyecto

## Idea principal

ElectroMax usa una arquitectura organizada por capas.

La idea es separar responsabilidades:

```text
Rutas antiguas:
Reciben la petición del navegador y mantienen compatibilidad.

Config:
Carga conexión, correo, Google y datos de empresa.

Middleware:
Controla seguridad, sesión, roles y CSRF.

Helpers:
Conservan funciones compartidas que todavía usa el sistema.

Services:
Guardan reglas de negocio y cálculos.

Views:
Contienen el HTML separado por tipo de usuario.

Database:
Organiza estructura, migraciones y correcciones SQL.

Docs:
Guarda documentación técnica y explicación por fases.
```

## Por qué se hizo por fases

No se movió todo de golpe porque el proyecto ya tenía muchas rutas, formularios e includes funcionando.

Mover todo al mismo tiempo podía romper:

```text
formularios POST
redirecciones
rutas de XAMPP
includes PHP
URLs guardadas
comandos SQL antiguos
```

Por eso se usaron adaptadores.

## Qué es un adaptador

Un adaptador es un archivo que conserva una ruta antigua, pero carga el archivo nuevo.

Ejemplo:

```php
require_once __DIR__ . '/bootstrap/app.php';
require_once EMX_ROOT . '/app/Config/database.php';
```

Así `db.php` sigue existiendo, pero la configuración real está en `app/Config/database.php`.

## Ventaja

El sistema se mantiene funcional mientras se mejora la estructura interna.
