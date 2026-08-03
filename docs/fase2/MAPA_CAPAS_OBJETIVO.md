# Mapa de capas objetivo

La arquitectura final quedará separada así:

```text
Rutas antiguas o públicas
    ↓
Controladores
    ↓
Servicios de negocio
    ↓
Repositorios SQL
    ↓
PostgreSQL
```

## Controladores

Reciben la petición, validan datos mínimos y llaman servicios.

Ejemplo futuro:

```text
admin.php
    llama a
app/Controllers/Admin/ProductController.php
```

## Servicios

Contienen reglas de negocio, cálculos y decisiones.

Ejemplos:

```text
app/Services/Carrito
app/Services/Checkout
app/Services/Devoluciones
app/Services/Reabastecimiento
app/Services/Facturacion
```

## Repositorios

Contienen consultas SQL.

Ejemplos:

```text
app/Repositories/Productos
app/Repositories/Pedidos
app/Repositories/Proveedores
```

## Vistas

Contienen HTML y presentación.

Ejemplos:

```text
views/cliente
views/admin
views/proveedor
views/auth
```

## Database

Contiene SQL ordenado.

```text
database/schema
database/migrations
database/hotfixes
database/functions
database/triggers
database/seeds
database/queries
```
