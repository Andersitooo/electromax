# Explicación corta para defensa

ElectroMax es un sistema e-commerce para electrodomésticos desarrollado en PHP y PostgreSQL.

El sistema permite compras, carrito, checkout, facturación, tracking, devoluciones, garantías, wishlist, soporte, administración y gestión de proveedores.

Técnicamente, el proyecto fue reorganizado por capas para que sea más mantenible:

```text
app/Config:
configuración.

app/Middleware:
seguridad.

app/Helpers:
funciones compartidas.

app/Services:
lógica de negocio.

views:
interfaz visual.

database:
SQL, migraciones y hotfixes.

docs:
documentación técnica.
```

La reorganización se hizo por fases para no romper rutas antiguas. Por eso archivos como `index.php`, `admin.php` y `proveedor.php` siguen funcionando, pero internamente ya cargan vistas, servicios y configuración organizada.

Una parte importante del sistema es la lógica de negocio:

```text
precios con IVA
descuentos por volumen
membresías
stock y backorder
capacidad de proveedor
facturación
notas de crédito
devoluciones
reemplazos con trazabilidad de series
```

La base de datos se organizó en `database/` y se documentaron migraciones, hotfixes, funciones y triggers.

La seguridad se centralizó en `app/Middleware/security.php`, donde se controla sesión, roles y CSRF.

La conclusión es que el proyecto no solo tiene funcionalidades de tienda, sino que también está estructurado de forma más profesional para mantenimiento y defensa técnica.
