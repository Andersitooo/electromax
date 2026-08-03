# Cómo explicar la Fase 10 en una defensa

Puede explicarse así:

Después de ordenar el proyecto por fases, preparé un modo de ejecución más profesional usando `public/` como entrada web.

En este modo, el navegador ya no depende directamente de los archivos antiguos de raíz. Entra por `public/` y desde ahí se cargan controladores ubicados en `app/Controllers`.

Esto separa mejor:

```text
public:
entrada web.

app/Controllers:
controladores.

app/Services:
lógica de negocio.

views:
interfaz.

database:
base de datos.
```

Los archivos antiguos siguen existiendo solo por compatibilidad, pero ya no son el camino principal del modo neto.
