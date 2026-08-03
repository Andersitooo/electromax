# Fase 1 - Inventario SQL actual

Este documento registra los archivos SQL actuales para preparar la Fase 6. No se cambia todavía ningún SQL.

| Archivo SQL | Líneas | CREATE TABLE | ALTER TABLE | FUNCTION | TRIGGER |
| --- | --- | --- | --- | --- | --- |
| bd.sql | 401 | 21 | 2 | 0 | 0 |
| fix_reabastecimiento_duplicadas.sql | 39 | 0 | 0 | 0 | 0 |
| hotfix_notificaciones_descuento_wishlist.sql | 21 | 0 | 5 | 0 | 0 |
| hotfix_notificaciones_wishlist_php_final.sql | 30 | 0 | 6 | 0 | 0 |
| hotfix_notificaciones_wishlist_trigger.sql | 154 | 0 | 6 | 3 | 1 |
| migracion_busqueda_inteligente.sql | 20 | 0 | 0 | 0 | 0 |
| migracion_descuentos_volumen_funcionales.sql | 15 | 0 | 2 | 0 | 0 |
| migracion_devoluciones_decision_cliente.sql | 75 | 0 | 3 | 0 | 0 |
| migracion_devoluciones_estimacion_reemplazo_prime.sql | 19 | 0 | 1 | 0 | 0 |
| migracion_devoluciones_flujo_secuencial_fraude.sql | 33 | 0 | 1 | 0 | 0 |
| migracion_email_outbox_panel.sql | 15 | 1 | 0 | 0 | 0 |
| migracion_empresa_config_admin.sql | 68 | 1 | 7 | 0 | 0 |
| migracion_empresa_simplificada_admin.sql | 68 | 1 | 7 | 0 | 0 |
| migracion_facturacion_garantias_checkout.sql | 232 | 8 | 16 | 0 | 0 |
| migracion_final_correo_facturacion.sql | 305 | 10 | 22 | 0 | 0 |
| migracion_flujo_guiado.sql | 130 | 2 | 5 | 0 | 0 |
| migracion_google_login.sql | 31 | 1 | 5 | 0 | 0 |
| migracion_nota_credito_correo_auto.sql | 30 | 1 | 2 | 0 | 0 |
| migracion_notificaciones_wishlist_mejoradas.sql | 11 | 0 | 1 | 0 | 0 |
| migracion_reemplazo_series_trazabilidad.sql | 45 | 0 | 12 | 0 | 0 |
| migracion_reformulacion_segura.sql | 141 | 5 | 32 | 0 | 0 |
| migracion_soporte_tickets.sql | 58 | 2 | 0 | 1 | 1 |

## Clasificación preliminar sugerida para Fase 6

- `bd.sql` debe revisarse como posible esquema base.
- Archivos `migracion_*.sql` deben ir a `database/migrations`.
- Archivos `hotfix_*.sql` y `fix_*.sql` deben ir a `database/hotfixes`.
- Triggers y funciones SQL deben quedar en `database/triggers` o `database/functions` si se separan de las migraciones.
- Antes de mover SQL, se debe crear un `README_EJECUCION_SQL.md` con el orden recomendado de ejecución, porque hay muchas migraciones acumuladas.