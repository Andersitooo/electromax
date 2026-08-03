# Fase 1 - Mapa de archivos actuales

Este documento describe el estado actual del proyecto recibido en el archivo final indicado por el usuario. Esta fase no reorganiza código ni cambia rutas; solo documenta el mapa técnico para preparar una reorganización segura.

## Resumen general

| Elemento | Resultado |
| --- | --- |
| Total de archivos | 201 |
| Archivos PHP | 66 |
| Archivos SQL | 22 |
| Archivos README/Markdown | 59 |
| Directorios de primer nivel | assets, components, scripts, uploads |
| Resultado de revisión de sintaxis PHP | Sin errores detectados con php -l |

## Distribución por extensión

| Extensión | Cantidad |
| --- | --- |
| .bat | 2 |
| .example | 1 |
| .jpeg | 3 |
| .jpg | 7 |
| .js | 2 |
| .json | 1 |
| .md | 59 |
| .pdf | 2 |
| .php | 66 |
| .png | 25 |
| .sql | 22 |
| .svg | 1 |
| .txt | 1 |
| .webp | 6 |
| [sin extensión] | 3 |

## Directorios actuales

| Directorio | Archivos | Observación |
| --- | --- | --- |
| assets | 7 | Carpeta existente del proyecto |
| components | 2 | Carpeta existente del proyecto |
| scripts | 1 | Carpeta existente del proyecto |
| uploads | 41 | Carpeta existente del proyecto |

## Archivos PHP más grandes

Estos archivos son prioritarios porque mezclan varias responsabilidades y deben separarse con cuidado en fases posteriores.

| Archivo | Líneas | Tamaño |
| --- | --- | --- |
| flujo_admin.php | 1645 | 74.8 KB |
| admin.php | 1567 | 204.4 KB |
| checkout.php | 903 | 70.3 KB |
| index.php | 857 | 68.7 KB |
| mi_cuenta.php | 762 | 91.7 KB |
| funciones_facturacion.php | 758 | 40.8 KB |
| producto.php | 691 | 55.2 KB |
| analitica.php | 682 | 44.6 KB |
| proveedor.php | 617 | 65.1 KB |
| funciones_backorder.php | 531 | 25.1 KB |
| auth.php | 409 | 20.6 KB |
| seguridad.php | 407 | 15.1 KB |
| soporte.php | 384 | 20.3 KB |
| carrito.php | 378 | 38.6 KB |
| soporte_admin.php | 358 | 21.9 KB |
| funciones_stock.php | 355 | 22.1 KB |
| funciones_google_auth.php | 351 | 15.2 KB |
| funciones_wishlist.php | 331 | 12.9 KB |
| tracking.php | 303 | 34.5 KB |
| ficha_tecnica_pdf.php | 266 | 13.0 KB |

## Clasificación inicial de archivos

### Rutas públicas y de cliente

- `index.php`
- `producto.php`
- `carrito.php`
- `checkout.php`
- `auth.php`
- `google_auth.php`
- `logout.php`
- `mi_cuenta.php`
- `wishlist.php`
- `notificaciones.php`
- `planes.php`
- `tracking.php`
- `soporte.php`
- `garantia.php`

### Rutas administrativas

- `admin.php`
- `analitica.php`
- `soporte_admin.php`
- `correos_empresa.php`
- `simulador_sucursales.php`

### Ruta de proveedor

- `proveedor.php`

### APIs, controladores puntuales y procesos POST

- `add_to_cart.php`
- `api_producto.php`
- `api_filtros.php`
- `api_filtrar_productos.php`
- `api_guardar_producto.php`
- `api_wishlist.php`
- `buscar_sugerencias.php`
- `banner_redirect.php`
- `cancelar_membresia.php`
- `procesar_devolucion.php`
- `responder_devolucion.php`
- `recibir_devolucion.php`
- `factura_pdf.php`
- `ficha_tecnica.php`
- `ficha_tecnica_pdf.php`
- `generar_etiqueta.php`
- `imprimir_guia.php`
- `probar_correo_facturacion.php`
- `verificar_phpmailer.php`
- `crear_admin.php`
- `crear_usuario_empresa.php`

### Configuración, seguridad y helpers

- `config_correo.example.php`
- `config_correo.php`
- `config_google.php`
- `db.php`
- `empresa_config.php`
- `funciones_automatizacion.php`
- `funciones_auxiliares.php`
- `funciones_backorder.php`
- `funciones_descuentos_volumen.php`
- `funciones_facturacion.php`
- `funciones_ficha_tecnica.php`
- `funciones_garantias.php`
- `funciones_google_auth.php`
- `funciones_home.php`
- `funciones_logistica.php`
- `funciones_notificaciones.php`
- `funciones_planes.php`
- `funciones_soporte.php`
- `funciones_stock.php`
- `funciones_wishlist.php`
- `seguridad.php`

### Componentes visuales compartidos

- `components/navbar.php`
- `components/footer.php`

## Observaciones técnicas principales

- El proyecto funciona actualmente como una aplicación PHP plana en la raíz del servidor, pensada para ejecutarse directamente desde rutas como `index.php`, `admin.php`, `proveedor.php` y `mi_cuenta.php`.
- La mayor parte de los archivos grandes mezclan vista HTML, consultas SQL, validaciones, reglas de negocio y JavaScript. Esto no impide que funcione, pero dificulta explicar, mantener y probar el sistema.
- Los archivos `db.php` y `seguridad.php` son dependencias transversales. Deben moverse recién cuando existan adaptadores o rutas puente.
- Los archivos SQL están en la raíz junto con los PHP. En Fase 6 deben moverse a `database/schema`, `database/migrations`, `database/hotfixes` y `database/triggers`.
- Existen muchos README históricos en la raíz. No deben eliminarse todavía; en Fase 8 se decidirá cuáles quedan en `docs/historico` y cuáles se archivan.