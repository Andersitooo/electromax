# Plan de Fase 3

Fase 3: mover configuración, seguridad y helpers.

Objetivo:

Separar archivos pequeños y transversales antes de mover lógica grande.

Candidatos para Fase 3:

```text
db.php
seguridad.php
config_correo.php
config_google.php
funciones_auxiliares.php
funciones_uploads.php
funciones_notificaciones.php
funciones_descuentos_volumen.php
```

Estrategia:

1. Crear copias organizadas en `app/Config`, `app/Middleware` y `app/Helpers`.
2. Mantener archivos antiguos en la raíz como adaptadores temporales.
3. Los archivos antiguos harán `require_once` hacia la nueva ubicación.
4. Validar sintaxis PHP.
5. Revisar login, admin, proveedor, carrito y checkout.

Resultado esperado:

El proyecto seguirá usando las mismas URLs, pero configuración y helpers estarán centralizados.
