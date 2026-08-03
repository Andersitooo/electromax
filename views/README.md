# views

Carpeta de vistas separadas en Fase 5.

## Objetivo

Separar el HTML de las rutas principales.

## Organización

```text
views/auth       Login y registro.
views/frontend   Pantallas del cliente.
views/admin      Panel administrativo.
views/proveedor  Panel de proveedor.
views/components Componentes compartidos.
views/layouts    Diseños base preparados para fases posteriores.
```

## Regla de compatibilidad

Las rutas antiguas siguen existiendo en la raíz.

Ejemplo:

```text
index.php
```

sigue siendo la URL pública, pero ahora carga:

```text
views/frontend/index_view.php
```
