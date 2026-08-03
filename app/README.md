# Carpeta app

Contendrá el backend organizado por capas.

En Fase 2 solo se crea la estructura. Los archivos críticos todavía no se mueven para evitar romper XAMPP, rutas, formularios o `require_once` existentes.

Uso futuro:
- `Controllers`: reciben la petición y llaman servicios.
- `Services`: reglas de negocio y cálculos.
- `Repositories`: consultas SQL y acceso a datos.
- `Helpers`: utilidades generales.
- `Middleware`: seguridad, sesión, roles y permisos.
- `Config`: configuración de aplicación, correo, Google y base de datos.
