# Cómo testear la estructura final

## 1. Verificar estructura

Desde la carpeta del proyecto:

```bash
php scripts\verificar_estructura_final_neta.php
```

Debe salir:

```text
Resultado: estructura final neta correcta. No hay PHP/SQL legacy en raíz.
```

## 2. Abrir sistema

Sin cambiar Apache:

```text
http://localhost/electro2/public/index.php
```

## 3. Probar rutas principales

```text
http://localhost/electro2/public/auth.php
http://localhost/electro2/public/producto.php
http://localhost/electro2/public/carrito.php
http://localhost/electro2/public/admin.php
http://localhost/electro2/public/proveedor.php
```

## 4. Probar flujos

```text
cliente login
producto
carrito
checkout
admin productos
proveedor capacidad
wishlist
devoluciones
facturación
```

## Nota

Desde esta versión ya no debes usar:

```text
http://localhost/electro2/index.php
```

Debes usar:

```text
http://localhost/electro2/public/index.php
```
