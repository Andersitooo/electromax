# Controladores y vistas

## Controlador

Un controlador es el archivo que recibe la petición del navegador.

Ejemplo:

```text
carrito.php
```

Este archivo puede:

```text
1. Validar sesión.
2. Leer datos del POST o GET.
3. Consultar la base de datos.
4. Llamar servicios de negocio.
5. Preparar variables.
6. Cargar la vista.
```

## Vista

Una vista es el archivo que contiene el HTML.

Ejemplo:

```text
views/frontend/carrito_view.php
```

Este archivo debe encargarse principalmente de mostrar información.

## Por qué se hizo así

Porque permite separar responsabilidades.

```text
Controlador = procesa
Servicio = calcula reglas
Vista = muestra
```
