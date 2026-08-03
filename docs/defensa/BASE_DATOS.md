# Base de datos

La base de datos está organizada en la carpeta `database`.

## Estructura

### hotfixes

- `database/hotfixes/fix_reabastecimiento_duplicadas.sql`
- `database/hotfixes/hotfix_notificaciones_descuento_wishlist.sql`
- `database/hotfixes/hotfix_notificaciones_wishlist_php_final.sql`
- `database/hotfixes/hotfix_notificaciones_wishlist_trigger.sql`

### migrations

- `database/migrations/migracion_busqueda_inteligente.sql`
- `database/migrations/migracion_descuentos_volumen_funcionales.sql`
- `database/migrations/migracion_devoluciones_decision_cliente.sql`
- `database/migrations/migracion_devoluciones_estimacion_reemplazo_prime.sql`
- `database/migrations/migracion_devoluciones_flujo_secuencial_fraude.sql`
- `database/migrations/migracion_email_outbox_panel.sql`
- `database/migrations/migracion_empresa_config_admin.sql`
- `database/migrations/migracion_empresa_simplificada_admin.sql`
- `database/migrations/migracion_facturacion_garantias_checkout.sql`
- `database/migrations/migracion_final_correo_facturacion.sql`
- `database/migrations/migracion_flujo_guiado.sql`
- `database/migrations/migracion_google_login.sql`
- `database/migrations/migracion_nota_credito_correo_auto.sql`
- `database/migrations/migracion_notificaciones_wishlist_mejoradas.sql`
- `database/migrations/migracion_reemplazo_series_trazabilidad.sql`
- `database/migrations/migracion_reformulacion_segura.sql`
- `database/migrations/migracion_soporte_tickets.sql`

### schema

- `database/schema/bd.sql`

### scripts

- `database/scripts/ORDEN_REFERENCIAL_SQL.sql`

## Script base

```text
database/schema/bd.sql
```

Este archivo representa la estructura base de instalación.

## Migraciones

Las migraciones modifican la base de datos con cambios incrementales.

Ejemplo:

```bash
psql -d electro2 -f database/migrations/migracion_google_login.sql
```

## Hotfixes

Los hotfixes son correcciones puntuales.

No todos se ejecutan siempre. Se ejecutan cuando aplica el problema.

## Funciones y triggers

La carpeta `database/functions` y `database/triggers` documenta funciones y triggers detectados en los SQL.

## Adaptadores SQL

Los SQL antiguos de raíz siguen existiendo como adaptadores para no romper comandos anteriores.
