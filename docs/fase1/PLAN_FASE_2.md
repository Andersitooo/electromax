# Fase 1 - Plan para Fase 2

La Fase 2 debe crear una estructura nueva sin romper rutas actuales. No debe mover todavía los archivos grandes ni eliminar nada.

## Objetivo de Fase 2

Crear carpetas nuevas y archivos base para que el proyecto pueda empezar a separarse por capas, manteniendo las rutas antiguas funcionando.

## Estructura propuesta para crear en Fase 2

```text
electro2/
├── app/
│   ├── Config/
│   ├── Core/
│   ├── Helpers/
│   ├── Services/
│   │   ├── Auth/
│   │   ├── Catalog/
│   │   ├── Cart/
│   │   ├── Checkout/
│   │   ├── Orders/
│   │   ├── Returns/
│   │   ├── Billing/
│   │   ├── Inventory/
│   │   ├── Suppliers/
│   │   └── Notifications/
│   └── Repositories/
├── views/
│   ├── layouts/
│   ├── components/
│   ├── cliente/
│   ├── admin/
│   ├── proveedor/
│   └── auth/
├── database/
│   ├── schema/
│   ├── migrations/
│   ├── hotfixes/
│   ├── functions/
│   └── triggers/
├── public/
│   └── assets/
├── storage/
│   ├── logs/
│   ├── cache/
│   ├── facturas/
│   └── uploads/
└── docs/
    ├── fase1/
    └── historico/
```

## Qué se debe tocar en Fase 2

- Crear carpetas nuevas.
- Crear `bootstrap/app.php` si se decide usar carga común.
- Crear un README de estructura nueva.
- Crear archivos `.gitkeep` para carpetas vacías.
- No mover aún `admin.php`, `proveedor.php`, `mi_cuenta.php`, `checkout.php`, `carrito.php`, `producto.php`, `index.php`, `db.php` ni `seguridad.php`.

## Entregables de Fase 2

- Estructura de carpetas creada.
- `README_ESTRUCTURA_NUEVA.md`.
- `docs/fase2/PLAN_MIGRACION_CONFIG_SEGURIDAD.md`.
- Verificación de que todas las rutas antiguas siguen existiendo.

## Criterio de aceptación

- Abrir `index.php`, `auth.php`, `admin.php`, `proveedor.php`, `carrito.php`, `checkout.php`, `mi_cuenta.php` y `producto.php` debe seguir usando las mismas URLs.
- El ZIP de Fase 2 no debe eliminar archivos.
- La Fase 2 debe ser reversible copiando de vuelta el ZIP anterior.