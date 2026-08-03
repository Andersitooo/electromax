# ElectroMax - Panel Empresa simplificado

Este ajuste simplifica el panel **Admin → Empresa** para que solo edites lo necesario para facturación simulada.

## Cambios incluidos

- Nueva paleta del panel admin para mejorar contraste con el logo.
- Sidebar oscuro, fijo y con scroll interno para que el color no se corte.
- Se removió del formulario el campo **Obligado a llevar contabilidad**.
- El ambiente queda fijo como **Simulación**.
- La moneda queda fija como **USD**.
- El sitio web queda como valor interno opcional, sin estorbar en el formulario.
- La nota legal simulada queda prellenada con texto académico.
- Los logos siguen disponibles:
  - Logo principal: interfaz y documentos.
  - Logo PDF opcional: versión optimizada para facturas/PDF si quieres subir una distinta.

## SQL recomendado

Ejecuta solo si quieres asegurar los valores predeterminados:

```bash
psql -d electro2 -f migracion_empresa_simplificada_admin.sql
```

No borra datos. Solo fija `ambiente = SIMULACION`, `moneda = USD` y `obligado_contabilidad = false`.

## Campos que sí debes configurar

En `Admin → Empresa` llena o revisa:

- Razón social
- Nombre comercial
- RUC
- Correo de facturación
- Teléfono
- Dirección matriz
- Establecimiento
- Punto de emisión
- Logo principal
- Logo PDF opcional, solo si quieres una versión distinta
- Nota legal simulada

## Nota legal simulada recomendada

```text
Documento académico sin validez tributaria. Facturación simulada para proyecto.
```

Esa nota **no es una nota de crédito**. Solo aparece como leyenda en los documentos. Las notas de crédito son documentos separados que se generan cuando hay devolución/reembolso.
