# Panel de empresa para facturación - ElectroMax

Este ajuste agrega en `admin.php` el módulo **Empresa**, donde se configuran los datos que salen en las facturas y notas de crédito simuladas.

## Archivos modificados

- `admin.php`
- `funciones_facturacion.php`
- `migracion_facturacion_garantias_checkout.sql`

## Archivo nuevo

- `migracion_empresa_config_admin.sql`

## Cómo instalar

1. Haz backup de tu base:

```bash
pg_dump -Fc electro2 > backup_antes_empresa_config.dump
```

2. Ejecuta la migración:

```bash
psql -d electro2 -f migracion_empresa_config_admin.sql
```

Si ya vas a ejecutar la migración grande, también quedó actualizado:

```bash
psql -d electro2 -f migracion_facturacion_garantias_checkout.sql
```

3. Entra al panel admin:

```text
http://localhost/electro2/admin.php?module=empresa
```

## Qué se puede configurar

- Razón social
- Nombre comercial
- RUC / identificación de empresa
- Dirección matriz
- Teléfono
- Correo de facturación
- Sitio web
- Establecimiento
- Punto de emisión
- Ambiente de simulación
- Moneda
- Régimen / nota legal simulada
- Obligado a llevar contabilidad
- Logo principal
- Logo PDF en JPG opcional

## Numeración de facturas

La factura se genera con el formato:

```text
establecimiento-punto_emision-secuencial
```

Ejemplo:

```text
001-001-000000001
```

Las notas de crédito usan:

```text
NC-001-001-000000001
```

## Logo en PDF

El generador PDF interno funciona mejor con JPG para el logo. Por eso el panel permite subir un **Logo PDF JPG opcional**. Si no subes nada, se usa:

```text
assets/electromax_logo_pdf.jpg
```

## Correo

Este panel configura los datos de empresa que se imprimen en la factura. Para enviar correos reales, además debes tener configurado:

```text
config_correo.php
vendor/autoload.php
```

Si SMTP no está configurado, el sistema guarda el intento en `email_outbox` y no rompe el checkout.
