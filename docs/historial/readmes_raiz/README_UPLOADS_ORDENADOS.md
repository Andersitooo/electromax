# ElectroMax - estructura ordenada de uploads

Este paquete mantiene los cambios anteriores del proyecto y agrega una regla clara para guardar archivos nuevos de forma ordenada. No mueve automáticamente archivos antiguos para no romper URLs ya guardadas en la base de datos.

## Estructura nueva

```text
uploads/
├── productos/
│   └── 01 - televisores/
│       └── tv-sam-001-smart-tv-43-4k-uhd/
│           └── imagenes/
├── marcas/
│   └── samsung/
├── banners/
│   └── carousel-hero-principal/
│       └── 2026/
├── perfiles/
│   ├── clientes/
│   │   └── user-xxxxxxxx/
│   └── proveedores/
│       └── user-xxxxxxxx/
├── devoluciones/
│   └── pedido-xxxxxxxx/
│       └── 2026-08/
└── confirmaciones/
    └── pedido-xxxxxxxx/
```

Los documentos PDF generados por facturación quedan en:

```text
documentos/
└── facturacion/
    ├── facturas/
    │   └── 2026/08/
    └── notas_credito/
        └── 2026/08/
```

## Reglas aplicadas

- Los productos se guardan por categoría y producto.
- Las categorías se numeran automáticamente en orden alfabético: `01 - aires-acondicionados`, `02 - lavadoras`, etc.
- El nombre de carpeta del producto usa SKU + nombre cuando existe.
- Las marcas se guardan en su carpeta propia.
- Los banners se guardan por sección de página y año.
- Las evidencias de devoluciones y confirmaciones se guardan por pedido.
- Las fotos de perfil se separan entre clientes y proveedores.
- Se crea `uploads/.htaccess` para evitar ejecución de PHP dentro de uploads.

## Importante

Los archivos antiguos siguen funcionando en las rutas antiguas porque la base de datos ya guarda esas URLs. Los nuevos archivos ya se guardan en la estructura ordenada.

Si más adelante quieres mover archivos antiguos, debe hacerse con una migración controlada que actualice también las rutas en la base de datos.
