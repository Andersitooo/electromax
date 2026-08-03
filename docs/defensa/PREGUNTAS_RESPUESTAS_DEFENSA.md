# Preguntas y respuestas para defensa

## Qué problema resuelve el sistema

Permite gestionar una tienda de electrodomésticos con compras, proveedores, stock, devoluciones, garantías y facturación.

## Por qué se separó por capas

Para que el código sea más fácil de mantener, explicar y ampliar.

## Por qué no se eliminaron las rutas antiguas

Porque el sistema ya funcionaba en XAMPP usando archivos como `index.php`, `admin.php` y `proveedor.php`. Eliminarlos podía romper enlaces y formularios.

## Qué es un servicio

Es una clase o archivo que contiene reglas de negocio o cálculos, sin mezclar HTML.

## Qué es un helper

Es un archivo con funciones compartidas. En este proyecto algunos helpers se conservaron por compatibilidad.

## Qué es un adaptador

Es un archivo que conserva una ruta antigua, pero carga una ubicación nueva.

## Cómo se calcula el precio final

Se toma el precio base, se suma IVA y luego se aplican descuentos secuenciales: descuento del producto, descuento por volumen y descuento por membresía.

## Qué diferencia hay entre SKU y serie

El SKU identifica el modelo del producto. La serie identifica una unidad física específica.

## Por qué se valida la serie en devoluciones

Para confirmar que el producto devuelto sea el mismo que se vendió en el pedido.

## Qué pasa en un reemplazo por el mismo producto

La factura original se conserva y se asigna una nueva serie al producto reemplazado.

## Qué pasa si hay reembolso

Se genera una nota de crédito y la factura original no se elimina.

## Qué hace el proveedor en el sistema

Registra capacidad de producción, tiempos de entrega, unidades disponibles y descuentos por volumen.

## Dónde está la base de datos

En la carpeta `database`, separada en schema, migrations, hotfixes, functions y triggers.

## Dónde está la seguridad

En `app/Middleware/security.php`.

## Cómo se ejecuta

Copiando el proyecto a `htdocs`, configurando PostgreSQL y entrando por `http://localhost/electro2/index.php`.
