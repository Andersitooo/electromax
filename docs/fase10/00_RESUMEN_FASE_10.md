# Fase 10: modo neto public/app

Fecha de generación: 2026-08-02 23:50:08

## Objetivo

Preparar el proyecto para funcionar sin depender de los archivos heredados de la raíz como flujo principal.

## Qué se hizo

1. Se crearon controladores netos en `app/Controllers`.
2. Las entradas de `public/` ahora cargan `app/Controllers`, no la raíz heredada.
3. Se movió `flujo_admin.php` a `app/Helpers/flujo_admin.php`.
4. Se copiaron `assets` y `uploads` dentro de `public/` para que public pueda ser raíz web.
5. Se creó `public/router.php`.
6. Se creó `app/Support/net_routes.php`.
7. Se creó un verificador específico de modo neto.

## Resultado

```text
Controladores netos creados: 41
Vistas parcheadas para componentes directos: 10
Carpetas copiadas a public: public/assets, public/uploads
```

## Importante

Los archivos antiguos de la raíz siguen existiendo solo como compatibilidad.

La prueba neta debe hacerse entrando por:

```text
http://localhost/electro2/public/index.php
```

o configurando Apache para que `public/` sea la raíz web.
