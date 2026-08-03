# Cómo explicar la Fase 5 en una defensa

Puedes explicarlo así:

En esta fase separé la presentación visual del procesamiento.

Antes una misma página hacía consultas, validaciones y además imprimía todo el HTML.

Ahora las rutas principales funcionan como controladores. Preparan datos y luego cargan una vista ubicada en la carpeta `views`.

Por ejemplo:

```text
proveedor.php
```

sigue siendo la ruta del panel del proveedor, pero su HTML principal está en:

```text
views/proveedor/proveedor_view.php
```

Esto ayuda a que el proyecto sea más ordenado porque separa:

```text
Controlador: procesa datos.
Servicio: calcula reglas.
Vista: muestra pantalla.
```

También mantuve las rutas antiguas para que XAMPP y los enlaces actuales no se rompan.
