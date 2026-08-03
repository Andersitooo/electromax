# Adaptadores SQL conservados

Los SQL de raíz se conservan para compatibilidad con comandos antiguos.

| Archivo raíz | Destino | Motivo |
|---|---|---|
| `bd.sql` | `database/schema/bd.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `fix_reabastecimiento_duplicadas.sql` | `database/hotfixes/fix_reabastecimiento_duplicadas.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `hotfix_notificaciones_descuento_wishlist.sql` | `database/hotfixes/hotfix_notificaciones_descuento_wishlist.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `hotfix_notificaciones_wishlist_php_final.sql` | `database/hotfixes/hotfix_notificaciones_wishlist_php_final.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `hotfix_notificaciones_wishlist_trigger.sql` | `database/hotfixes/hotfix_notificaciones_wishlist_trigger.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_busqueda_inteligente.sql` | `database/migrations/migracion_busqueda_inteligente.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_descuentos_volumen_funcionales.sql` | `database/migrations/migracion_descuentos_volumen_funcionales.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_devoluciones_decision_cliente.sql` | `database/migrations/migracion_devoluciones_decision_cliente.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_devoluciones_estimacion_reemplazo_prime.sql` | `database/migrations/migracion_devoluciones_estimacion_reemplazo_prime.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_devoluciones_flujo_secuencial_fraude.sql` | `database/migrations/migracion_devoluciones_flujo_secuencial_fraude.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_email_outbox_panel.sql` | `database/migrations/migracion_email_outbox_panel.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_empresa_config_admin.sql` | `database/migrations/migracion_empresa_config_admin.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_empresa_simplificada_admin.sql` | `database/migrations/migracion_empresa_simplificada_admin.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_facturacion_garantias_checkout.sql` | `database/migrations/migracion_facturacion_garantias_checkout.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_final_correo_facturacion.sql` | `database/migrations/migracion_final_correo_facturacion.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_flujo_guiado.sql` | `database/migrations/migracion_flujo_guiado.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_google_login.sql` | `database/migrations/migracion_google_login.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_nota_credito_correo_auto.sql` | `database/migrations/migracion_nota_credito_correo_auto.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_notificaciones_wishlist_mejoradas.sql` | `database/migrations/migracion_notificaciones_wishlist_mejoradas.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_reemplazo_series_trazabilidad.sql` | `database/migrations/migracion_reemplazo_series_trazabilidad.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_reformulacion_segura.sql` | `database/migrations/migracion_reformulacion_segura.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
| `migracion_soporte_tickets.sql` | `database/migrations/migracion_soporte_tickets.sql` | Se conserva para que comandos antiguos psql -f archivo.sql sigan funcionando. |
