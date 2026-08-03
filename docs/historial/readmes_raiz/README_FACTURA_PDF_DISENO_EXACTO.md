# Diseño PDF de factura replicado

Se implementó el diseño aprobado directamente en el PDF de facturas del sistema:

- PDF en formato horizontal para respetar el diseño aprobado.
- Header azul tecnológico como el modelo elegido.
- Logo ElectroMax grande y sin recuadro blanco.
- Caja superior derecha: "Factura No. 001-001-000000001".
- Bloque de código / clave de acceso.
- Tarjetas Emisor y Cliente / Datos de facturación.
- Tabla de productos con encabezado azul oscuro.
- Totales en caja inferior derecha con barra azul.
- Corrección de caracteres especiales con WinAnsiEncoding y conversión Windows-1252.
- Se eliminaron caracteres raros que salían como símbolos dañados.
- Correo HTML actualizado con un estilo corporativo.

No requiere SQL para el diseño visual.
