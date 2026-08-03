# Proveedor: editar capacidad de producción

Cambio aplicado en `proveedor.php`.

Ahora la sección `Mi Capacidad de Producción` deja más claro que el proveedor puede:

- registrar una nueva capacidad
- editar una capacidad ya registrada

Mejoras incluidas:

1. Botón principal visible: `Registrar Nueva Capacidad`.
2. Encabezado explicativo en la sección.
3. Botón visible `Editar` en cada fila de capacidad.
4. El mismo modal sirve para crear y editar.
5. Al editar cambia el título a `Editar Capacidad de Producción`.
6. Al crear cambia el título a `Registrar Nueva Capacidad de Producción`.
7. El backend valida que la capacidad a editar pertenezca al proveedor logueado.
8. Se conservan los rangos de descuento por volumen al editar.

No requiere SQL nuevo.
