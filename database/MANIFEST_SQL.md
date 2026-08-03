# Manifiesto SQL organizado

Este archivo lista dónde quedó cada archivo SQL después de la Fase 6.

| Archivo antiguo | Nueva ubicación | Categoría | Funciones | Triggers |
|---|---|---|---|---|
| `bd.sql` | `database/schema/bd.sql` | schema | - | - |
| `fix_reabastecimiento_duplicadas.sql` | `database/hotfixes/fix_reabastecimiento_duplicadas.sql` | hotfixes | - | - |
| `hotfix_notificaciones_descuento_wishlist.sql` | `database/hotfixes/hotfix_notificaciones_descuento_wishlist.sql` | hotfixes | - | - |
| `hotfix_notificaciones_wishlist_php_final.sql` | `database/hotfixes/hotfix_notificaciones_wishlist_php_final.sql` | hotfixes | - | - |
| `hotfix_notificaciones_wishlist_trigger.sql` | `database/hotfixes/hotfix_notificaciones_wishlist_trigger.sql` | hotfixes | emx_pct_descuento, emx_fmt_pct, emx_notificar_wishlist_producto_update | trg_emx_wishlist_producto_update |
| `migracion_busqueda_inteligente.sql` | `database/migrations/migracion_busqueda_inteligente.sql` | migrations | - | - |
| `migracion_descuentos_volumen_funcionales.sql` | `database/migrations/migracion_descuentos_volumen_funcionales.sql` | migrations | - | - |
| `migracion_devoluciones_decision_cliente.sql` | `database/migrations/migracion_devoluciones_decision_cliente.sql` | migrations | - | - |
| `migracion_devoluciones_estimacion_reemplazo_prime.sql` | `database/migrations/migracion_devoluciones_estimacion_reemplazo_prime.sql` | migrations | - | - |
| `migracion_devoluciones_flujo_secuencial_fraude.sql` | `database/migrations/migracion_devoluciones_flujo_secuencial_fraude.sql` | migrations | - | - |
| `migracion_email_outbox_panel.sql` | `database/migrations/migracion_email_outbox_panel.sql` | migrations | - | - |
| `migracion_empresa_config_admin.sql` | `database/migrations/migracion_empresa_config_admin.sql` | migrations | - | - |
| `migracion_empresa_simplificada_admin.sql` | `database/migrations/migracion_empresa_simplificada_admin.sql` | migrations | - | - |
| `migracion_facturacion_garantias_checkout.sql` | `database/migrations/migracion_facturacion_garantias_checkout.sql` | migrations | - | - |
| `migracion_final_correo_facturacion.sql` | `database/migrations/migracion_final_correo_facturacion.sql` | migrations | - | - |
| `migracion_flujo_guiado.sql` | `database/migrations/migracion_flujo_guiado.sql` | migrations | - | - |
| `migracion_google_login.sql` | `database/migrations/migracion_google_login.sql` | migrations | - | - |
| `migracion_nota_credito_correo_auto.sql` | `database/migrations/migracion_nota_credito_correo_auto.sql` | migrations | - | - |
| `migracion_notificaciones_wishlist_mejoradas.sql` | `database/migrations/migracion_notificaciones_wishlist_mejoradas.sql` | migrations | - | - |
| `migracion_reemplazo_series_trazabilidad.sql` | `database/migrations/migracion_reemplazo_series_trazabilidad.sql` | migrations | - | - |
| `migracion_reformulacion_segura.sql` | `database/migrations/migracion_reformulacion_segura.sql` | migrations | - | - |
| `migracion_soporte_tickets.sql` | `database/migrations/migracion_soporte_tickets.sql` | migrations | soporte_touch_updated_at | trg_soporte_tickets_updated_at |
