# Fase 1 - Rutas actuales del proyecto

Este documento registra las rutas PHP actuales y los módulos internos encontrados. En esta fase no se cambian URL ni formularios.

## Rutas principales por tipo

| Grupo | Rutas |
| --- | --- |
| Cliente | `index.php`, `producto.php`, `carrito.php`, `checkout.php`, `auth.php`, `google_auth.php`, `logout.php`, `mi_cuenta.php`, `wishlist.php`, `notificaciones.php`, `planes.php`, `tracking.php`, `soporte.php`, `garantia.php` |
| Administrador | `admin.php`, `analitica.php`, `soporte_admin.php`, `correos_empresa.php`, `simulador_sucursales.php` |
| Proveedor | `proveedor.php` |
| APIs/procesos | `add_to_cart.php`, `api_producto.php`, `api_filtros.php`, `api_filtrar_productos.php`, `api_guardar_producto.php`, `api_wishlist.php`, `buscar_sugerencias.php`, `banner_redirect.php`, `cancelar_membresia.php`, `procesar_devolucion.php`, `responder_devolucion.php`, `recibir_devolucion.php`, `factura_pdf.php`, `ficha_tecnica.php`, `ficha_tecnica_pdf.php`, `generar_etiqueta.php`, `imprimir_guia.php`, `probar_correo_facturacion.php`, `verificar_phpmailer.php`, `crear_admin.php`, `crear_usuario_empresa.php` |

## Módulos internos de admin.php

- `admin.php?module=banners`
- `admin.php?module=categorias`
- `admin.php?module=clientes`
- `admin.php?module=dashboard`
- `admin.php?module=devoluciones`
- `admin.php?module=empresa`
- `admin.php?module=garantias`
- `admin.php?module=marcas`
- `admin.php?module=pedidos`
- `admin.php?module=planes`
- `admin.php?module=producto_proveedores`
- `admin.php?module=productos`
- `admin.php?module=sucursales`
- `admin.php?module=usuarios`

## Secciones internas de proveedor.php

- `proveedor.php?seccion=capacidad`
- `proveedor.php?seccion=dashboard`
- `proveedor.php?seccion=perfil`
- `proveedor.php?seccion=propuestas`
- `proveedor.php?seccion=solicitudes`

## Secciones internas de mi_cuenta.php

- `mi_cuenta.php?seccion=devoluciones`
- `mi_cuenta.php?seccion=direcciones`
- `mi_cuenta.php?seccion=historial`
- `mi_cuenta.php?seccion=membresia`
- `mi_cuenta.php?seccion=pedidos`
- `mi_cuenta.php?seccion=perfil`
- `mi_cuenta.php?seccion=seguridad`

## Referencias de rutas detectadas

Se listan las referencias encontradas en formularios, enlaces, `fetch` y redirecciones. Algunas son dinámicas porque usan PHP dentro de la URL.

| Ruta referenciada | Veces | Primeras referencias |
| --- | --- | --- |
| `?action=login` | 1 | formulario en `auth.php` línea 306 |
| `?action=registro` | 1 | formulario en `auth.php` línea 344 |
| `?module=` | 3 | redireccion en `admin.php` línea 932; redireccion en `admin.php` línea 964; redireccion en `admin.php` línea 967 |
| `?module=banners&action=<?= $edit_banner ?` | 1 | formulario en `admin.php` línea 1354 |
| `?module=banners&action=<?= $edit_section ?` | 1 | formulario en `admin.php` línea 1346 |
| `?module=banners&msg=` | 2 | redireccion en `admin.php` línea 903; redireccion en `admin.php` línea 907 |
| `?module=banners&msg=Banner+eliminado&msg_type=success` | 1 | redireccion en `admin.php` línea 927 |
| `?module=banners&msg=Sección+actualizada&msg_type=success` | 1 | redireccion en `admin.php` línea 822 |
| `?module=banners&msg=Sección+creada&msg_type=success` | 1 | redireccion en `admin.php` línea 812 |
| `?module=banners&msg=Sección+eliminada&msg_type=success` | 1 | redireccion en `admin.php` línea 918 |
| `?module=categorias&action=<?= $action ===` | 1 | formulario en `admin.php` línea 1340 |
| `?module=categorias&msg=` | 1 | redireccion en `admin.php` línea 655 |
| `?module=devoluciones&action=actualizar_estado` | 1 | formulario en `admin.php` línea 1263 |
| `?module=devoluciones&msg=` | 1 | redireccion en `admin.php` línea 334 |
| `?module=empresa&action=guardar` | 1 | formulario en `admin.php` línea 1212 |
| `?module=empresa&msg=` | 1 | redireccion en `admin.php` línea 321 |
| `?module=garantias&action=actualizar_garantia` | 1 | formulario en `admin.php` línea 1268 |
| `?module=garantias&msg=` | 1 | redireccion en `admin.php` línea 369 |
| `?module=marcas&action=<?= $action ===` | 1 | formulario en `admin.php` línea 1340 |
| `?module=marcas&msg=` | 1 | redireccion en `admin.php` línea 638 |
| `?module=pedidos&action=accion_masiva` | 1 | formulario en `admin.php` línea 1274 |
| `?module=pedidos&action=update_status` | 1 | formulario en `admin.php` línea 1290 |
| `?module=pedidos&msg=` | 2 | redireccion en `admin.php` línea 395; redireccion en `admin.php` línea 406 |
| `?module=planes&action=<?= $action ===` | 1 | formulario en `admin.php` línea 1340 |
| `?module=planes&msg=` | 1 | redireccion en `admin.php` línea 724 |
| `?module=planes&msg=Plan+desactivado&msg_type=success` | 1 | redireccion en `admin.php` línea 913 |
| `?module=producto_proveedores&action=aprobar_cotizacion` | 1 | formulario en `admin.php` línea 1321 |
| `?module=producto_proveedores&action=asignar` | 1 | formulario en `admin.php` línea 1321 |
| `?module=producto_proveedores&action=solicitar_reabastecimiento` | 1 | formulario en `admin.php` línea 1332 |
| `?module=producto_proveedores&msg=Cotización+aprobada&msg_type=success` | 1 | redireccion en `admin.php` línea 739 |
| `?module=producto_proveedores&msg=Proveedor+asignado&msg_type=success` | 1 | redireccion en `admin.php` línea 732 |
| `?module=producto_proveedores&msg=Solicitud+creada+y+solicitudes+anteriores+cerradas.+Proveedores+notificados:+` | 1 | redireccion en `admin.php` línea 801 |
| `?module=productos&action=<?= $action ===` | 1 | formulario en `admin.php` línea 1332 |
| `?module=productos&msg=` | 1 | redireccion en `admin.php` línea 608 |
| `?module=sucursales&action=<?= $action ===` | 1 | formulario en `admin.php` línea 1324 |
| `?module=sucursales&msg=No+se+puede+eliminar+la+Matriz&msg_type=error` | 1 | redireccion en `admin.php` línea 959 |
| `?module=sucursales&msg=Sucursal+guardada&msg_type=success` | 1 | redireccion en `admin.php` línea 433 |
| `?module=usuarios&action=<?= $action ===` | 1 | formulario en `admin.php` línea 1340 |
| `?module=usuarios&msg=` | 1 | redireccion en `admin.php` línea 689 |
| `?seccion=capacidad` | 1 | formulario en `proveedor.php` línea 410 |
| `?seccion=solicitudes` | 1 | formulario en `proveedor.php` línea 412 |
| `add_to_cart.php?id=` | 1 | fetch en `index.php` línea 655 |
| `admin.php` | 8 | enlace en `admin.php` línea 1208; enlace en `analitica.php` línea 483; redireccion en `auth.php` línea 9 ... |
| `admin.php?module=dashboard` | 3 | enlace en `correos_empresa.php` línea 57; enlace en `simulador_sucursales.php` línea 161; enlace en `soporte_admin.php` línea 191 |
| `admin.php?module=devoluciones` | 2 | enlace en `recibir_devolucion.php` línea 55; enlace en `soporte_admin.php` línea 193 |
| `admin.php?module=devoluciones&msg=` | 4 | redireccion en `recibir_devolucion.php` línea 31; redireccion en `recibir_devolucion.php` línea 35; redireccion en `recibir_devolucion.php` línea 42 ... |
| `admin.php?module=empresa` | 1 | enlace en `correos_empresa.php` línea 57 |
| `admin.php?module=garantias` | 1 | enlace en `soporte_admin.php` línea 194 |
| `admin.php?module=pedidos` | 1 | enlace en `soporte_admin.php` línea 192 |
| `admin.php?module=producto_proveedores` | 1 | enlace en `soporte_admin.php` línea 197 |
| `admin.php?module=productos` | 2 | enlace en `simulador_sucursales.php` línea 161; enlace en `soporte_admin.php` línea 196 |
| `admin.php?module=sucursales` | 1 | enlace en `simulador_sucursales.php` línea 161 |
| `analitica.php` | 2 | enlace en `admin.php` línea 1208; enlace en `soporte_admin.php` línea 198 |
| `api_filtrar_productos.php?` | 1 | fetch en `index.php` línea 478 |
| `api_filtros.php?categoria_id=` | 1 | fetch en `index.php` línea 376 |
| `api_wishlist.php` | 1 | fetch en `index.php` línea 714 |
| `auth.php?action=login` | 10 | redireccion en `cancelar_membresia.php` línea 8; enlace en `components/navbar.php` línea 118; redireccion en `garantia.php` línea 9 ... |
| `auth.php?action=login&msg=` | 1 | redireccion en `google_auth.php` línea 46 |
| `auth.php?action=login&msg=debes_iniciar_sesion` | 4 | redireccion en `carrito.php` línea 13; redireccion en `checkout.php` línea 19; redireccion en `planes.php` línea 51 ... |
| `auth.php?action=login&redirect=<?= urlencode($_SERVER[` | 2 | enlace en `producto.php` línea 255; enlace en `producto.php` línea 343 |
| `banner_redirect.php?id=<?= $id ?>` | 1 | enlace en `funciones_home.php` línea 94 |
| `banner_redirect.php?id=<?= emxHtml($banner[` | 1 | enlace en `funciones_home.php` línea 102 |
| `carrito.php` | 2 | formulario en `carrito.php` línea 298; enlace en `components/navbar.php` línea 123 |
| `carrito.php?msg=` | 6 | redireccion en `carrito.php` línea 71; redireccion en `carrito.php` línea 86; redireccion en `carrito.php` línea 103 ... |
| `checkout.php` | 2 | enlace en `carrito.php` línea 330; formulario en `checkout.php` línea 638 |
| `correos_empresa.php` | 3 | enlace en `admin.php` línea 1209; enlace en `correos_empresa.php` línea 57; enlace en `correos_empresa.php` línea 57 |
| `correos_empresa.php?ver=<?= h($c[` | 1 | enlace en `correos_empresa.php` línea 57 |
| `factura_pdf.php?id=<?= urlencode($pedido[` | 1 | enlace en `mi_cuenta.php` línea 444 |
| `ficha_tecnica.php?id=<?= urlencode($p[` | 1 | enlace en `admin.php` línea 1300 |
| `ficha_tecnica.php?id=<?= urlencode($producto[` | 1 | enlace en `producto.php` línea 257 |
| `ficha_tecnica_pdf.php?id=<?= urlencode($id) ?>` | 1 | enlace en `ficha_tecnica.php` línea 41 |
| `ficha_tecnica_pdf.php?id=<?= urlencode($producto[` | 1 | enlace en `producto.php` línea 258 |
| `garantia.php` | 1 | enlace en `components/footer.php` línea 92 |
| `google_auth.php?action=link` | 1 | formulario en `mi_cuenta.php` línea 512 |
| `google_auth.php?action=login` | 1 | formulario en `auth.php` línea 296 |
| `google_auth.php?action=registro` | 1 | formulario en `auth.php` línea 334 |
| `google_auth.php?action=unlink` | 1 | formulario en `mi_cuenta.php` línea 512 |
| `imprimir_guia.php?codigo=<?= urlencode($dev[` | 1 | enlace en `mi_cuenta.php` línea 485 |
| `index.php` | 25 | enlace en `admin.php` línea 1209; enlace en `auth.php` línea 249; enlace en `auth.php` línea 265 ... |
| `index.php?categoria=<?= $cat[` | 1 | enlace en `index.php` línea 244 |
| `index.php?categoria=<?= htmlspecialchars($producto[` | 1 | enlace en `producto.php` línea 246 |
| `index.php?categoria=<?= urlencode($cat[` | 2 | enlace en `components/footer.php` línea 80; enlace en `components/navbar.php` línea 139 |
| `index.php?descuento_min=10` | 2 | enlace en `components/footer.php` línea 82; enlace en `index.php` línea 244 |
| `index.php?msg=bienvenido` | 2 | redireccion en `auth.php` línea 79; redireccion en `auth.php` línea 134 |
| `index.php?msg=sesion_cerrada` | 1 | redireccion en `logout.php` línea 9 |
| `logout.php` | 5 | enlace en `admin.php` línea 1209; enlace en `mi_cuenta.php` línea 424; enlace en `proveedor.php` línea 387 ... |
| `mi_cuenta.php` | 3 | redireccion en `cancelar_membresia.php` línea 42; enlace en `components/navbar.php` línea 105; enlace en `soporte.php` línea 215 |
| `mi_cuenta.php?msg=error_cancelacion&msg_type=error` | 1 | redireccion en `cancelar_membresia.php` línea 36 |
| `mi_cuenta.php?msg=plan_cancelado&msg_type=success` | 1 | redireccion en `cancelar_membresia.php` línea 32 |
| `mi_cuenta.php?seccion=devoluciones` | 3 | enlace en `components/footer.php` línea 91; enlace en `imprimir_guia.php` línea 51; redireccion en `responder_devolucion.php` línea 9 |
| `mi_cuenta.php?seccion=devoluciones&msg=` | 7 | redireccion en `procesar_devolucion.php` línea 21; redireccion en `procesar_devolucion.php` línea 180; redireccion en `procesar_devolucion.php` línea 185 ... |
| `mi_cuenta.php?seccion=direcciones&msg=Dirección+eliminada` | 1 | redireccion en `mi_cuenta.php` línea 355 |
| `mi_cuenta.php?seccion=direcciones&msg=Dirección+principal+actualizada` | 1 | redireccion en `mi_cuenta.php` línea 349 |
| `mi_cuenta.php?seccion=pedidos` | 4 | enlace en `checkout.php` línea 637; redireccion en `procesar_devolucion.php` línea 11; redireccion en `tracking.php` línea 15 ... |
| `mi_cuenta.php?seccion=pedidos&msg=No+tienes+acceso+a+este+pedido&msg_type=error` | 1 | redireccion en `tracking.php` línea 39 |
| `mi_cuenta.php?seccion=perfil&msg=Foto+eliminada` | 1 | redireccion en `mi_cuenta.php` línea 287 |
| `mi_cuenta.php?seccion=seguridad&msg=` | 1 | redireccion en `google_auth.php` línea 44 |
| `mi_cuenta.php?seccion=seguridad&msg=Cuenta+de+Google+desvinculada&msg_type=success` | 1 | redireccion en `google_auth.php` línea 35 |
| `mi_cuenta.php?seccion=seguridad&msg=Cuenta+de+Google+vinculada&msg_type=success` | 1 | redireccion en `google_auth.php` línea 26 |
| `notificaciones.php` | 2 | enlace en `components/navbar.php` línea 98; redireccion en `notificaciones.php` línea 23 |
| `notificaciones.php?marcar_leida=` | 1 | fetch en `notificaciones.php` línea 39 |
| `planes.php` | 5 | enlace en `components/footer.php` línea 93; enlace en `components/navbar.php` línea 144; enlace en `mi_cuenta.php` línea 499 ... |
| `planes.php?msg=` | 1 | redireccion en `planes.php` línea 109 |
| `procesar_devolucion.php` | 1 | formulario en `mi_cuenta.php` línea 516 |
| `producto.php?id=` | 3 | redireccion en `producto.php` línea 33; redireccion en `producto.php` línea 36; redireccion en `producto.php` línea 58 |
| `producto.php?id=<?= $item[` | 1 | enlace en `wishlist.php` línea 48 |
| `producto.php?id=<?= $notif[` | 1 | enlace en `notificaciones.php` línea 35 |
| `producto.php?id=<?= $producto_id ?>#tab-reviews` | 1 | formulario en `producto.php` línea 342 |
| `producto.php?id=<?= $rel[` | 1 | enlace en `producto.php` línea 354 |
| `producto.php?id=<?= $visto[` | 1 | enlace en `mi_cuenta.php` línea 498 |
| `producto.php?id=<?= urlencode($producto_id) ?>` | 1 | formulario en `producto.php` línea 255 |
| `proveedor.php` | 3 | redireccion en `auth.php` línea 11; redireccion en `auth.php` línea 132; redireccion en `mi_cuenta.php` línea 17 |
| `responder_devolucion.php` | 1 | formulario en `mi_cuenta.php` línea 486 |
| `simulador_sucursales.php` | 2 | formulario en `simulador_sucursales.php` línea 162; enlace en `simulador_sucursales.php` línea 161 |
| `soporte.php` | 2 | enlace en `components/footer.php` línea 89; enlace en `components/navbar.php` línea 148 |
| `soporte.php?msg=` | 1 | redireccion en `soporte.php` línea 135 |
| `soporte.php?msg=Ticket+cerrado&msg_type=success` | 1 | redireccion en `soporte.php` línea 130 |
| `soporte.php?ticket=` | 2 | redireccion en `soporte.php` línea 70; redireccion en `soporte.php` línea 111 |
| `soporte.php?ticket=<?= urlencode($t[` | 1 | enlace en `soporte.php` línea 291 |
| `soporte_admin.php` | 2 | enlace en `admin.php` línea 1208; enlace en `soporte_admin.php` línea 195 |
| `soporte_admin.php?msg=` | 1 | redireccion en `soporte_admin.php` línea 90 |
| `soporte_admin.php?ticket=` | 2 | redireccion en `soporte_admin.php` línea 60; redireccion en `soporte_admin.php` línea 85 |
| `soporte_admin.php?ticket=<?= urlencode($t[` | 1 | enlace en `soporte_admin.php` línea 257 |
| `tracking.php` | 1 | enlace en `components/footer.php` línea 90 |
| `tracking.php?id=` | 1 | redireccion en `tracking.php` línea 51 |
| `tracking.php?id=<?= $ped[` | 1 | enlace en `admin.php` línea 1290 |
| `tracking.php?id=<?= $pedido[` | 1 | enlace en `mi_cuenta.php` línea 443 |
| `tracking.php?id=<?= $pedido_exitoso[` | 1 | enlace en `checkout.php` línea 636 |
| `wishlist.php` | 2 | enlace en `components/navbar.php` línea 94; formulario en `wishlist.php` línea 49 |
| `wishlist.php?msg=producto_agregado` | 1 | redireccion en `wishlist.php` línea 21 |
| `wishlist.php?msg=producto_eliminado` | 1 | redireccion en `wishlist.php` línea 25 |

## Conclusión para reorganización de rutas

- Las rutas antiguas deben mantenerse al menos hasta Fase 7 mediante adaptadores. Por ejemplo, `admin.php` puede quedarse como entrada y delegar a controladores nuevos.
- No conviene mover de inmediato `auth.php`, `admin.php`, `proveedor.php`, `mi_cuenta.php`, `checkout.php`, `carrito.php`, `producto.php` ni `index.php`, porque muchas URLs y redirecciones dependen de esos nombres.