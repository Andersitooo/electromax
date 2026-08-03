# Explicación frontend y backend en el proyecto

## Antes

Antes los archivos tenían todo mezclado:

```text
Consulta SQL
Validación
Regla de negocio
HTML
JavaScript
```

Eso funcionaba, pero era difícil explicar y mantener.

## Ahora en Fase 5

La ruta antigua queda como controlador.

Ejemplo:

```text
producto.php
```

Responsabilidad del controlador:

```text
1. Cargar seguridad.
2. Cargar base de datos.
3. Procesar formularios.
4. Consultar datos.
5. Preparar variables.
6. Cargar la vista.
```

La vista queda separada.

Ejemplo:

```text
views/frontend/producto_view.php
```

Responsabilidad de la vista:

```text
1. Mostrar HTML.
2. Mostrar datos ya preparados.
3. Renderizar formularios.
4. Mostrar componentes visuales.
```

## Explicación simple para defensa

El backend prepara la información.

El frontend muestra la información.

Las rutas antiguas se conservaron para no romper enlaces.
