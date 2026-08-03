# Plan de testeo funcional en modo neto

## Orden recomendado

```text
1. Abrir public/index.php.
2. Abrir public/auth.php.
3. Iniciar sesión como cliente.
4. Abrir producto.
5. Agregar al carrito.
6. Abrir carrito.
7. Pasar a checkout.
8. Iniciar sesión como admin.
9. Abrir public/admin.php.
10. Editar producto.
11. Iniciar sesión como proveedor.
12. Abrir public/proveedor.php.
13. Editar capacidad de producción.
14. Probar wishlist.
15. Probar devoluciones.
16. Probar facturación.
```

## Qué reportar si falla

```text
URL exacta
captura del error
archivo y línea
acción que hiciste antes del error
mensaje del log de Apache
```
