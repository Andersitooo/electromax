# Cómo testear el modo neto

## Opción rápida sin cambiar Apache

Abre:

```text
http://localhost/electro2/public/index.php
```

Luego prueba:

```text
http://localhost/electro2/public/auth.php
http://localhost/electro2/public/producto.php
http://localhost/electro2/public/carrito.php
http://localhost/electro2/public/admin.php
http://localhost/electro2/public/proveedor.php
```

## Verificación por consola

Desde la carpeta del proyecto:

```bash
php scripts\verificar_modo_neto_fase10.php
php scripts\verificar_adaptadores_fase7.php
php scripts\verificar_limpieza_fase8.php
```

## Qué significa modo neto

Significa que `public/index.php` ya no llama a `../index.php`.

Ahora llama directamente a:

```text
app/Controllers/Web/index.php
```

El archivo de raíz `index.php` queda solo como compatibilidad antigua.
