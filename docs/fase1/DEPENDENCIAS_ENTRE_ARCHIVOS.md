# Fase 1 - Dependencias entre archivos

Este documento identifica qué archivos dependen de otros mediante `require`, `require_once`, `include` o `include_once`. Sirve para saber qué no se debe mover todavía sin crear adaptadores.

## Dependencias más usadas

| Archivo incluido | Cantidad de archivos que lo usan | Usado por |
| --- | --- | --- |
| `db.php` | 37 | `add_to_cart.php`, `admin.php`, `analitica.php`, `api_filtrar_productos.php`, `api_filtros.php`, `api_guardar_producto.php`, `api_producto.php`, `api_wishlist.php` ... |
| `seguridad.php` | 37 | `add_to_cart.php`, `admin.php`, `analitica.php`, `api_filtrar_productos.php`, `api_filtros.php`, `api_guardar_producto.php`, `api_producto.php`, `api_wishlist.php` ... |
| `flujo_admin.php` | 6 | `admin.php`, `mi_cuenta.php`, `procesar_devolucion.php`, `recibir_devolucion.php`, `responder_devolucion.php`, `tracking.php` |
| `funciones_wishlist.php` | 6 | `admin.php`, `api_wishlist.php`, `index.php`, `notificaciones.php`, `producto.php`, `wishlist.php` |
| `funciones_backorder.php` | 4 | `add_to_cart.php`, `carrito.php`, `checkout.php`, `producto.php` |
| `funciones_descuentos_volumen.php` | 4 | `add_to_cart.php`, `carrito.php`, `checkout.php`, `producto.php` |
| `funciones_facturacion.php` | 4 | `admin.php`, `checkout.php`, `correos_empresa.php`, `mi_cuenta.php` |
| `config_google.php` | 3 | `auth.php`, `funciones_google_auth.php`, `mi_cuenta.php` |
| `empresa_config.php` | 3 | `ficha_tecnica.php`, `ficha_tecnica_pdf.php`, `generar_etiqueta.php` |
| `funciones_planes.php` | 3 | `add_to_cart.php`, `carrito.php`, `producto.php` |
| `funciones_ficha_tecnica.php` | 2 | `ficha_tecnica.php`, `producto.php` |
| `funciones_garantias.php` | 2 | `checkout.php`, `garantia.php` |
| `funciones_home.php` | 2 | `index.php`, `producto.php` |
| `funciones_logistica.php` | 2 | `checkout.php`, `funciones_automatizacion.php` |
| `funciones_notificaciones.php` | 2 | `soporte.php`, `soporte_admin.php` |
| `funciones_soporte.php` | 2 | `soporte.php`, `soporte_admin.php` |
| `funciones_stock.php` | 2 | `admin.php`, `checkout.php` |
| `components/footer.php` | 1 | `garantia.php` |
| `components/navbar.php` | 1 | `garantia.php` |
| `funciones_auxiliares.php` | 1 | `checkout.php` |
| `funciones_google_auth.php` | 1 | `google_auth.php` |

## Dependencias por archivo PHP

| Archivo | Dependencias directas |
| --- | --- |
| `add_to_cart.php` | `seguridad.php`, `db.php`, `funciones_planes.php`, `funciones_backorder.php`, `funciones_descuentos_volumen.php` |
| `admin.php` | `seguridad.php`, `db.php`, `funciones_stock.php`, `funciones_wishlist.php`, `funciones_facturacion.php`, `flujo_admin.php` |
| `analitica.php` | `seguridad.php`, `db.php` |
| `api_filtrar_productos.php` | `seguridad.php`, `db.php` |
| `api_filtros.php` | `seguridad.php`, `db.php` |
| `api_guardar_producto.php` | `seguridad.php`, `db.php` |
| `api_producto.php` | `seguridad.php`, `db.php` |
| `api_wishlist.php` | `seguridad.php`, `db.php`, `funciones_wishlist.php` |
| `auth.php` | `seguridad.php`, `db.php`, `config_google.php` |
| `banner_redirect.php` | `seguridad.php`, `db.php` |
| `buscar_sugerencias.php` | `seguridad.php`, `db.php` |
| `cancelar_membresia.php` | `seguridad.php`, `db.php` |
| `carrito.php` | `seguridad.php`, `db.php`, `funciones_planes.php`, `funciones_backorder.php`, `funciones_descuentos_volumen.php` |
| `checkout.php` | `seguridad.php`, `db.php`, `funciones_logistica.php`, `funciones_auxiliares.php`, `funciones_backorder.php`, `funciones_descuentos_volumen.php`, `funciones_stock.php`, `funciones_garantias.php`, `funciones_facturacion.php` |
| `correos_empresa.php` | `seguridad.php`, `db.php`, `funciones_facturacion.php` |
| `crear_admin.php` | `db.php` |
| `factura_pdf.php` | `seguridad.php`, `db.php` |
| `ficha_tecnica.php` | `seguridad.php`, `db.php`, `empresa_config.php`, `funciones_ficha_tecnica.php` |
| `ficha_tecnica_pdf.php` | `seguridad.php`, `db.php`, `empresa_config.php` |
| `funciones_automatizacion.php` | `funciones_logistica.php` |
| `funciones_google_auth.php` | `config_google.php` |
| `garantia.php` | `seguridad.php`, `db.php`, `funciones_garantias.php`, `components/navbar.php`, `components/footer.php` |
| `generar_etiqueta.php` | `seguridad.php`, `db.php`, `empresa_config.php` |
| `google_auth.php` | `seguridad.php`, `db.php`, `funciones_google_auth.php` |
| `imprimir_guia.php` | `seguridad.php`, `db.php` |
| `index.php` | `seguridad.php`, `db.php`, `funciones_wishlist.php`, `funciones_home.php` |
| `logout.php` | `seguridad.php` |
| `mi_cuenta.php` | `seguridad.php`, `db.php`, `config_google.php`, `funciones_facturacion.php`, `flujo_admin.php` |
| `notificaciones.php` | `seguridad.php`, `db.php`, `funciones_wishlist.php` |
| `planes.php` | `seguridad.php`, `db.php` |
| `procesar_devolucion.php` | `seguridad.php`, `db.php`, `flujo_admin.php` |
| `producto.php` | `seguridad.php`, `db.php`, `funciones_planes.php`, `funciones_wishlist.php`, `funciones_backorder.php`, `funciones_ficha_tecnica.php`, `funciones_home.php`, `funciones_descuentos_volumen.php` |
| `proveedor.php` | `seguridad.php`, `db.php` |
| `recibir_devolucion.php` | `seguridad.php`, `db.php`, `flujo_admin.php` |
| `responder_devolucion.php` | `seguridad.php`, `db.php`, `flujo_admin.php` |
| `simulador_sucursales.php` | `seguridad.php`, `db.php` |
| `soporte.php` | `seguridad.php`, `db.php`, `funciones_notificaciones.php`, `funciones_soporte.php` |
| `soporte_admin.php` | `seguridad.php`, `db.php`, `funciones_notificaciones.php`, `funciones_soporte.php` |
| `tracking.php` | `seguridad.php`, `db.php`, `flujo_admin.php` |
| `wishlist.php` | `seguridad.php`, `db.php`, `funciones_wishlist.php` |

## Funciones declaradas por archivo principal

La tabla muestra funciones declaradas. No reemplaza una prueba funcional; es un mapa estático para planificar la separación.

| Archivo | Cantidad | Funciones detectadas |
| --- | --- | --- |
| `admin.php` | 26 | `enviarNotificacionCliente`, `validarSerieDevolucion`, `generarSKUProfesional`, `generarSerieUnica`, `procesarRangosVolumen`, `calcularTiempoEntregaRealista`, `aplicarDescuentoVolumen`, `calcularScorePropuesta`, `detectarFraudeDevoluciones`, `toggleRedirectFields`, `calcIVAProd`, `addSpecRow` ... |
| `analitica.php` | 14 | `emxACol`, `emxATable`, `emxQAll`, `emxQOne`, `emxMoney`, `emxNum`, `emxPct`, `emxVar`, `emxCleanLabel`, `emxJsonNum`, `emxCsv`, `hasCanvas` ... |
| `auth.php` | 2 | `togglePassword`, `emxHandleGoogleCredential` |
| `buscar_sugerencias.php` | 2 | `emxBuscarLike`, `emxBuscarImg` |
| `carrito.php` | 2 | `showConfirmModal`, `closeModal` |
| `checkout.php` | 20 | `emxCheckoutProductoId`, `emxCheckoutNormalizarCarritoSesion`, `emxCheckoutInsertFlexible`, `emxGuardarDireccionCheckoutSiAplica`, `calcularDistancia`, `calcularTiempoEntrega`, `asignarSucursalOptima`, `calcularFechaEstimada`, `validarLuhn`, `updateStepUI`, `nextStep`, `prevStep` ... |
| `components/footer.php` | 2 | `emxFooterTableExists`, `emxFooterColExists` |
| `components/navbar.php` | 2 | `emxNavTableExists`, `emxNavColExists` |
| `config_google.php` | 2 | `emxGoogleClientId`, `emxGoogleActivo` |
| `correos_empresa.php` | 2 | `h`, `tablaExiste` |
| `ficha_tecnica_pdf.php` | 14 | `pdf_clean_text`, `pdf_escape`, `pdf_text`, `pdf_rect`, `wrap_pdf`, `ficha_label`, `ficha_unit`, `ficha_group`, `ficha_order`, `ficha_value`, `build_header`, `build_footer` ... |
| `flujo_admin.php` | 36 | `emxTextoEstado`, `emxTextoMotivoDevolucion`, `emxCategoriaMotivoDevolucion`, `emxFlujoCasoDevolucion`, `emxSolucionesPermitidasDevolucion`, `emxTextoSolucionPropuesta`, `emxEstadoDevolucionNormalizado`, `emxMotivoEsCourier`, `emxMotivoRequiereRetorno`, `emxAccionesDevolucion`, `emxAccionesPedido`, `emxAgregarHistorial` ... |
| `funciones_automatizacion.php` | 4 | `registrarCambioEstado`, `obtenerIconoEstado`, `procesarAutomatizacionPedido`, `iniciarSeguimientoPedido` |
| `funciones_auxiliares.php` | 3 | `generarSKUProfesional`, `generarSerieUnica`, `validarSerieDevolucion` |
| `funciones_backorder.php` | 21 | `emxStockSucursalMasCercana`, `emxObtenerCostoReferenciaProducto`, `emxObtenerCapacidadProveedores`, `emxCalcularDescuentoProveedor`, `emxNombreProveedor`, `emxFechaDesdeDias`, `emxFechaLegible`, `emxBackorderColumnExists`, `emxObtenerPoliticaInventarioProducto`, `emxCantidadReposicionMinima`, `emxCalcularCantidadSolicitudInterna`, `emxActualizarStockProductoDisponible` ... |
| `funciones_descuentos_volumen.php` | 4 | `emxNormalizarPct`, `emxDescuentoProductoActivoPct`, `emxDescuentoVolumenProducto`, `emxCalcularPrecioProductoCarrito` |
| `funciones_facturacion.php` | 22 | `emxFactColumnExists`, `emxFactTableExists`, `emxEmpresaConfig`, `emxFacturaNumero`, `emxNotaCreditoNumero`, `emxClaveAccesoSimulada`, `emxFacturaDatosClienteDesdePedido`, `emxPdfEscape`, `emxPdfText`, `emxPdfRect`, `emxPdfLine`, `emxPdfRoundedRect` ... |
| `funciones_ficha_tecnica.php` | 10 | `emxFichaLabel`, `emxFichaUnidad`, `emxFichaGrupo`, `emxFichaIconoGrupo`, `emxFichaOrden`, `emxFichaValorTexto`, `emxFichaNormalizarLista`, `emxRenderFichaValorPremium`, `emxPrepararGruposFicha`, `emxRenderFichaPremium` |
| `funciones_garantias.php` | 5 | `emxGarantiaColumnExists`, `emxObtenerGarantiasProducto`, `emxSnapshotGarantiaProducto`, `emxAplicarGarantiaADetalle`, `emxDetalleTieneGarantiaVigente` |
| `funciones_google_auth.php` | 17 | `emxGoogleColumnExists`, `emxGoogleTableExists`, `emxGoogleBase64UrlDecode`, `emxGoogleHttpJson`, `emxGoogleVerificarIdToken`, `emxGoogleAsegurarMigracion`, `emxGoogleRegistrarEventoAuth`, `emxGoogleBuscarUsuarioPorGoogleId`, `emxGoogleBuscarUsuarioPorEmail`, `emxGoogleRolIdCliente`, `emxGoogleActualizarUsuario`, `emxGoogleIniciarSesion` ... |
| `funciones_home.php` | 10 | `emxHtml`, `emxNormalizarTextoHome`, `emxObtenerSeccionesHome`, `emxBannersDeSeccion`, `emxRenderBannerCard`, `emxRenderBannerSection`, `emxRenderHomeSlot`, `emxProductoQueryBase`, `emxObtenerMasVendidos`, `emxObtenerRecomendadosProducto` |
| `funciones_logistica.php` | 4 | `calcularDistanciaReal`, `asignarSucursalInteligente`, `calcularTiempoEstimado`, `generarNumeroGuia` |
| `funciones_notificaciones.php` | 1 | `enviarNotificacionCliente` |
| `funciones_planes.php` | 4 | `obtenerPlanActivoUsuario`, `obtenerBeneficiosUsuario`, `aplicarDescuentoPlan`, `tieneBeneficio` |
| `funciones_soporte.php` | 12 | `emxSoporteTableExists`, `emxSoporteMotivos`, `emxSoporteEstados`, `emxSoportePrioridades`, `emxSoportePrioridadPorMotivo`, `emxSoporteLabel`, `emxSoporteEstadoClase`, `emxSoportePrioridadClase`, `emxSoporteCodigo`, `emxSoporteUpload`, `emxSoporteNotificarAdmins`, `emxSoporteNotificarCliente` |
| `funciones_stock.php` | 11 | `emxColumnExistsLocal`, `emxReabCerrarSolicitudesDuplicadasProducto`, `emxCerrarSolicitudesDuplicadasProducto`, `emxAplicarDescuentoVolumenStock`, `emxCalcularDiasProveedorStock`, `emxGenerarCotizacionesSimuladas`, `verificarYGenerarSolicitudes`, `obtenerSolicitudesActivas`, `emxScorePropuesta`, `obtenerCotizaciones`, `aprobarCotizacion` |
| `funciones_wishlist.php` | 20 | `agregarAWishlist`, `eliminarDeWishlist`, `obtenerWishlist`, `estaEnWishlist`, `emxWishlistTableExists`, `emxWishlistColExists`, `emxWishlistFechaCol`, `emxWishlistNotificacionDuplicadaReciente`, `crearNotificacion`, `obtenerNotificaciones`, `contarNotificacionesNoLeidas`, `marcarNotificacionLeida` ... |
| `index.php` | 21 | `getCategoryIcon`, `getCategoryAccent`, `moveCarousel`, `moveBest`, `goToBestPage`, `updateBestCarousel`, `toggleFiltros`, `cargarFiltrosDinamicos`, `renderizarFiltrosHTML`, `toggleFiltroGrupo`, `actualizarSliders`, `aplicarFiltros` ... |
| `mi_cuenta.php` | 14 | `getEstadoColor`, `abrirModalConfirmacion`, `cerrarModalConfirmacion`, `previewFotosConfirmacion`, `eliminarFotoConfirmacion`, `enviarConfirmacionMiCuenta`, `abrirModalDevolucion`, `cerrarModalDevolucion`, `manejarCambioMotivo`, `mostrarResumen`, `mostrarPreviewFotos`, `eliminarFoto` ... |
| `notificaciones.php` | 1 | `marcarLeida` |
| `planes.php` | 4 | `validarLuhn`, `abrirModalPago`, `cerrarModalPago`, `validarLuhnJS` |
| `procesar_devolucion.php` | 1 | `emxSubirFotosDevolucion` |
| `producto.php` | 11 | `setImage`, `changeImage`, `updateQty`, `showStockModal`, `closeStockModal`, `switchTab`, `compartirProducto`, `agregarAlCarrito`, `flyToCart`, `updateCartBadge`, `showToast` |
| `proveedor.php` | 14 | `emxProveedorCalcularDescuentoRango`, `abrirModalCapacidad`, `cerrarModalCapacidad`, `editarCapacidadDesdeBoton`, `editarCapacidad`, `agregarRangoDescuento`, `agregarRangoDescuentoConDatos`, `abrirModalPropuesta`, `cerrarModalPropuesta`, `toggleOfertaCompleta`, `toggleOfertaParcial`, `agregarLote` ... |
| `seguridad.php` | 31 | `emxUsuarioId`, `emxRolActual`, `emxEsAdmin`, `emxEsProveedor`, `emxRequireLogin`, `emxRequireRole`, `emxIsUuid`, `emxCsrfToken`, `emxDbColumnExists`, `emxCsrfCampo`, `emxVerificarCsrf`, `emxVerificarCsrfSiPOST` ... |
| `simulador_sucursales.php` | 3 | `calcularDistancia`, `encontrarSucursalOptima`, `calcularTiempoEntrega` |
| `tracking.php` | 4 | `getEstadoColorTracking`, `abrirModalConfirmacion`, `cerrarModalConfirmacion`, `enviarConfirmacion` |
| `verificar_phpmailer.php` | 1 | `emxOut` |

## Dependencias críticas detectadas

- `db.php` aparece como dependencia directa en la mayoría de páginas y procesos. Debe permanecer accesible desde la raíz durante las primeras fases.
- `seguridad.php` también es transversal: controla sesión, roles, CSRF, redirecciones seguras y utilidades de uploads.
- `flujo_admin.php` concentra flujo de pedidos, devoluciones, reembolsos, reemplazos, fraude y notificaciones. Es una pieza crítica de negocio.
- `funciones_facturacion.php` concentra factura, nota de crédito, PDF y correo. Debe separarse con pruebas manuales posteriores.
- `funciones_backorder.php`, `funciones_stock.php` y `proveedor.php` están conectados con reabastecimiento, cotizaciones, descuentos por volumen de proveedor y planificación de entrega.