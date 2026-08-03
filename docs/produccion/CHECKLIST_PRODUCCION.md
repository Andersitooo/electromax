# Checklist antes de subir a anderspace.online

## Antes de subir

```text
[ ] Respaldar public/uploads/
[ ] Respaldar storage/
[ ] Respaldar .env si ya existe
[ ] Respaldar base de datos
```

## En el VPS

```text
[ ] Instalar Apache/httpd
[ ] Instalar PHP y extensiones
[ ] Instalar PostgreSQL o configurar acceso a PostgreSQL
[ ] Instalar Composer
[ ] Descomprimir proyecto
[ ] Crear .env desde .env.production.example
[ ] Ejecutar composer install
[ ] Aplicar permisos
[ ] Configurar VirtualHost con DocumentRoot public/
[ ] Reiniciar Apache
[ ] Probar https://anderspace.online
```

## Pruebas funcionales

```text
[ ] Home
[ ] Login cliente
[ ] Login admin
[ ] Productos
[ ] Filtros
[ ] Carrito sin login redirige a login
[ ] Carrito con login agrega productos
[ ] Checkout
[ ] Factura PDF
[ ] Envío de correo
[ ] Subida de imagen desde admin
[ ] Imagen se conserva en public/uploads/
```
