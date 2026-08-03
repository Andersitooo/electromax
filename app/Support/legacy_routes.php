<?php
/**
 * Registro de rutas heredadas - Fase 7.
 *
 * Este archivo no reemplaza el enrutamiento actual.
 * Sirve como mapa técnico para saber qué rutas antiguas siguen existiendo
 * y hacia dónde apuntan internamente después de separar capas.
 *
 * Regla:
 * - Las URLs antiguas se conservan.
 * - Los controladores de raíz siguen recibiendo la petición.
 * - Si ya existe vista separada, el controlador carga la vista desde views/.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

return [
    'php' => [
        'add_to_cart.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'admin.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/admin/admin_view.php'],
        'analitica.php' => ['tipo' => 'ruta_raiz_compatible', 'destino' => ''],
        'api_filtrar_productos.php' => ['tipo' => 'endpoint_api_raiz', 'destino' => ''],
        'api_filtros.php' => ['tipo' => 'endpoint_api_raiz', 'destino' => ''],
        'api_guardar_producto.php' => ['tipo' => 'endpoint_api_raiz', 'destino' => ''],
        'api_producto.php' => ['tipo' => 'endpoint_api_raiz', 'destino' => ''],
        'api_wishlist.php' => ['tipo' => 'endpoint_api_raiz', 'destino' => ''],
        'auth.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/auth/auth_view.php'],
        'banner_redirect.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'buscar_sugerencias.php' => ['tipo' => 'endpoint_api_raiz', 'destino' => ''],
        'cancelar_membresia.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'carrito.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/frontend/carrito_view.php'],
        'checkout.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/frontend/checkout_view.php'],
        'config_correo.example.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Config/mail.example.php'],
        'config_correo.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Config/mail.php'],
        'config_google.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Config/google.php'],
        'correos_empresa.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/admin/correos_empresa_view.php'],
        'crear_admin.php' => ['tipo' => 'ruta_raiz_compatible', 'destino' => ''],
        'crear_usuario_empresa.php' => ['tipo' => 'ruta_raiz_compatible', 'destino' => ''],
        'db.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Config/database.php'],
        'empresa_config.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Config/company.php'],
        'factura_pdf.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'ficha_tecnica.php' => ['tipo' => 'ruta_raiz_compatible', 'destino' => ''],
        'ficha_tecnica_pdf.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'flujo_admin.php' => ['tipo' => 'ruta_raiz_compatible', 'destino' => ''],
        'funciones_automatizacion.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_automatizacion.php'],
        'funciones_auxiliares.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_auxiliares.php'],
        'funciones_backorder.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_backorder.php'],
        'funciones_descuentos_volumen.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_descuentos_volumen.php'],
        'funciones_facturacion.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_facturacion.php'],
        'funciones_ficha_tecnica.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_ficha_tecnica.php'],
        'funciones_garantias.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_garantias.php'],
        'funciones_google_auth.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_google_auth.php'],
        'funciones_home.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_home.php'],
        'funciones_logistica.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_logistica.php'],
        'funciones_notificaciones.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_notificaciones.php'],
        'funciones_planes.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_planes.php'],
        'funciones_soporte.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_soporte.php'],
        'funciones_stock.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_stock.php'],
        'funciones_wishlist.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Helpers/funciones_wishlist.php'],
        'garantia.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/frontend/garantia_view.php'],
        'generar_etiqueta.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'google_auth.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'imprimir_guia.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'index.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/frontend/index_view.php'],
        'logout.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'mi_cuenta.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/frontend/mi_cuenta_view.php'],
        'notificaciones.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/frontend/notificaciones_view.php'],
        'planes.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/frontend/planes_view.php'],
        'probar_correo_facturacion.php' => ['tipo' => 'ruta_raiz_compatible', 'destino' => ''],
        'procesar_devolucion.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'producto.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/frontend/producto_view.php'],
        'proveedor.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/proveedor/proveedor_view.php'],
        'recibir_devolucion.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'responder_devolucion.php' => ['tipo' => 'accion_raiz_compatible', 'destino' => ''],
        'seguridad.php' => ['tipo' => 'adaptador_php', 'destino' => 'app/Middleware/security.php'],
        'simulador_sucursales.php' => ['tipo' => 'ruta_raiz_compatible', 'destino' => ''],
        'soporte.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/frontend/soporte_view.php'],
        'soporte_admin.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/admin/soporte_admin_view.php'],
        'tracking.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/frontend/tracking_view.php'],
        'verificar_phpmailer.php' => ['tipo' => 'ruta_raiz_compatible', 'destino' => ''],
        'wishlist.php' => ['tipo' => 'controlador_con_vista_separada', 'destino' => 'views/frontend/wishlist_view.php'],
        'components/footer.php' => ['tipo' => 'adaptador_componente', 'destino' => 'views/components/footer.php'],
        'components/navbar.php' => ['tipo' => 'adaptador_componente', 'destino' => 'views/components/navbar.php'],
    ],
    'sql' => [
        'bd.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/schema/bd.sql'],
        'fix_reabastecimiento_duplicadas.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/hotfixes/fix_reabastecimiento_duplicadas.sql'],
        'hotfix_notificaciones_descuento_wishlist.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/hotfixes/hotfix_notificaciones_descuento_wishlist.sql'],
        'hotfix_notificaciones_wishlist_php_final.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/hotfixes/hotfix_notificaciones_wishlist_php_final.sql'],
        'hotfix_notificaciones_wishlist_trigger.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/hotfixes/hotfix_notificaciones_wishlist_trigger.sql'],
        'migracion_busqueda_inteligente.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_busqueda_inteligente.sql'],
        'migracion_descuentos_volumen_funcionales.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_descuentos_volumen_funcionales.sql'],
        'migracion_devoluciones_decision_cliente.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_devoluciones_decision_cliente.sql'],
        'migracion_devoluciones_estimacion_reemplazo_prime.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_devoluciones_estimacion_reemplazo_prime.sql'],
        'migracion_devoluciones_flujo_secuencial_fraude.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_devoluciones_flujo_secuencial_fraude.sql'],
        'migracion_email_outbox_panel.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_email_outbox_panel.sql'],
        'migracion_empresa_config_admin.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_empresa_config_admin.sql'],
        'migracion_empresa_simplificada_admin.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_empresa_simplificada_admin.sql'],
        'migracion_facturacion_garantias_checkout.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_facturacion_garantias_checkout.sql'],
        'migracion_final_correo_facturacion.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_final_correo_facturacion.sql'],
        'migracion_flujo_guiado.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_flujo_guiado.sql'],
        'migracion_google_login.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_google_login.sql'],
        'migracion_nota_credito_correo_auto.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_nota_credito_correo_auto.sql'],
        'migracion_notificaciones_wishlist_mejoradas.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_notificaciones_wishlist_mejoradas.sql'],
        'migracion_reemplazo_series_trazabilidad.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_reemplazo_series_trazabilidad.sql'],
        'migracion_reformulacion_segura.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_reformulacion_segura.sql'],
        'migracion_soporte_tickets.sql' => ['tipo' => 'adaptador_sql', 'destino' => 'database/migrations/migracion_soporte_tickets.sql'],
    ],
];
?>
