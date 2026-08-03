# ElectroMax - Proyecto reformulado seguro

Este paquete mantiene el diseño visual original y cambia principalmente validaciones, seguridad por rutas y flujos guiados.

## Instalación recomendada

1. Haz respaldo de tu base:

```bash
pg_dump -Fc electro2 > backup_antes_reformulacion.dump
```

2. Ejecuta migraciones:

```bash
psql -d electro2 -f migracion_reformulacion_segura.sql
```

3. Copia todos los archivos del proyecto reformulado sobre tu proyecto actual.

4. Configura credenciales por entorno, por ejemplo:

```bash
export DB_HOST=localhost
export DB_PORT=5432
export DB_NAME=electro2
export DB_USER=postgres
export DB_PASSWORD=tu_password
```

Si no defines variables, `db.php` usa los valores locales anteriores para que puedas probar sin cambiar tu entorno.

## Seguridad aplicada

- `seguridad.php` centraliza sesión segura, CSRF, validación UUID, roles y subidas seguras.
- `crear_admin.php` ya no se ejecuta desde navegador; solo por consola.
- Panel admin, proveedor, analítica y simulador requieren rol correcto.
- Etiquetas y guías de devolución solo las ve el dueño del caso o admin.
- Subidas de imágenes validan MIME real y bloquean ejecución PHP en `uploads/.htaccess`.
- Se eliminó debug visible de `auth.php` y se regenera sesión en login/registro.
- Redirecciones de banners ya no permiten URLs externas peligrosas.

## Flujo de pedidos

El pedido avanza secuencialmente. Si hay problema, no se retrocede libremente: se abre incidencia/devolución.

Ruta normal:

```text
Pendiente -> Pago confirmado -> En Preparación -> Despachado -> En Tránsito -> En Reparto -> Entregado -> Cerrado
```

Excepciones:

```text
En tránsito -> incidencia courier
Entregado -> incidencia no recibido / daño
En revisión -> reembolso / reemplazo / cierre
```

## Flujo de devoluciones/incidencias

El admin ya no debe escoger estados sueltos. En `admin.php` aparecen acciones válidas según estado.

Puntos importantes:

- La solución final no se muestra al inicio.
- Reembolso/cambio aparece solo cuando ya corresponde resolver.
- La inspección pide serie, diagnóstico y comentario técnico.
- `defecto_fabrica` queda como diagnóstico posterior, no como suposición inicial.
- `dano_transporte` y `reclamo_courier` tienen ruta propia.

Ruta principal:

```text
pendiente_revision -> autorizada_retorno -> en_camino_retorno -> recibido_almacen -> en_inspeccion -> aprobado_reembolso/aprobado_cambio/reclamo_courier/garantia_proveedor/rechazada
```

Ruta sin producto físico:

```text
pendiente_revision -> investigacion_courier -> aprobado_reembolso/aprobado_cambio/rechazada
```

## Fichas técnicas y PDF

Se agregaron:

- `ficha_tecnica.php`: vista elegante imprimible.
- `ficha_tecnica_pdf.php`: descarga PDF real generado desde PHP sin librerías externas.
- Logo empresarial en `assets/electromax_logo.svg`.

La ficha usa:

- nombre del producto,
- SKU,
- modelo,
- marca,
- categoría,
- descripción,
- `productos.especificaciones_tecnicas`.

## Proveedores y reabastecimiento

- Cuando el stock baja del punto de reorden, el admin ve alerta.
- El admin solicita reabastecimiento.
- La solicitud llega a todos los proveedores asociados al producto.
- Los proveedores envían propuestas con cantidad, días, precio y calendario.
- El admin ve score y proveedor recomendado, pero sigue eligiendo manualmente.
- Al aprobar una propuesta, se suma stock global y stock de matriz como simulación académica.

## Carrito con sobrestock / backorder simulado

- El cliente puede tipear cantidades mayores al stock inmediato.
- Si la cantidad supera stock, el carrito muestra calendario total o parcial según proveedores.
- El cliente debe aceptar el calendario o ajustar al stock antes de checkout.
- En checkout solo se descuenta stock físico disponible.
- El faltante crea `pedidos_backorder` y `cronogramas_reabastecimiento`.

## Archivos nuevos principales

- `seguridad.php`
- `empresa_config.php`
- `funciones_backorder.php`
- `ficha_tecnica.php`
- `ficha_tecnica_pdf.php`
- `migracion_reformulacion_segura.sql`
- `assets/electromax_logo.svg`
- `uploads/.htaccess`

## Prueba rápida

1. Entra como admin.
2. Revisa productos con stock bajo.
3. Solicita reabastecimiento.
4. Entra como proveedor y envía propuesta.
5. Vuelve a admin y aprueba la mejor propuesta.
6. Entra como cliente y pide más cantidad que el stock.
7. Acepta calendario en carrito.
8. Procesa checkout.
9. Crea una devolución y revisa que las acciones sean secuenciales.
10. Genera ficha técnica PDF desde la página del producto.
