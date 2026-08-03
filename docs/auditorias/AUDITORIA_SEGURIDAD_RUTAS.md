# Auditoría rápida ElectroMax antes de organizar estructura

Fecha de auditoría: 2026-08-02
Base revisada: último paquete del proyecto, con hardening aplicado.

## Resultado general

- Sintaxis PHP: OK (65 archivos PHP revisados).
- Includes/requires internos: OK.
- Links/rutas PHP internas encontradas: OK.
- CSRF en POST principales: OK.
- Formularios POST sin token detectados por escaneo simple: 0.
- Secretos reales detectados por escaneo simple: 0.

## Correcciones aplicadas en este hotfix

1. `api_wishlist.php`
   - Ahora solo acepta POST.
   - Ahora valida CSRF.
   - Valida `producto_id` como UUID o numérico legacy.

2. `index.php`
   - El AJAX de wishlist ahora envía `csrf_token`.

3. `wishlist.php`
   - Ahora valida CSRF en POST.
   - Corrige `producto_id`: ya no lo castea a `int`, porque tus productos usan UUID.
   - Formularios de eliminar/agregar al carrito llevan token.

4. `tracking.php`
   - Acciones POST ahora validan CSRF.
   - Formularios admin de simulación llevan token.
   - La confirmación dinámica del cliente agrega token.
   - Se quitó el acceso al panel de simulación por `?demo`; queda solo para admin.

5. `simulador_sucursales.php`
   - Ahora valida CSRF en POST.
   - El formulario del simulador lleva token.

6. `verificar_phpmailer.php`
   - Si se abre desde navegador, ahora exige usuario ADMIN/SUPERADMIN.
   - Desde consola sigue funcionando.

7. `db.php`
   - Mantiene respaldo local para XAMPP.
   - En producción exige `DB_PASSWORD` por variable de entorno y no debe depender de clave escrita en código.

8. Archivos nuevos de protección:
   - `.htaccess`: bloquea descarga de `.sql`, `.md`, `.env`, logs, scripts auxiliares, composer, etc.
   - `.gitignore`: evita subir claves, logs, vendor y uploads.
   - `.env.example`: plantilla segura para producción.

## Detalles técnicos

### Sintaxis PHP

Sin errores de sintaxis.

### Rutas faltantes

No se encontraron rutas PHP internas faltantes.

### Includes faltantes

No se encontraron includes/requires faltantes.

### POST sin verificación CSRF por archivo

No se detectaron archivos POST principales sin verificación CSRF.

### Formularios POST sin token detectados por escaneo simple

No quedaron formularios POST sin token detectados por el escaneo simple.

### Secretos reales detectados

No se detectaron secretos reales. Hay placeholders seguros en `config_correo.php`, `.env.example` y READMEs.

## Estado de seguridad revisado

- Sesiones: usan `httponly`, `SameSite=Lax`, `strict_mode` y cookie segura cuando hay HTTPS.
- CSRF: queda centralizado con `emxCsrfCampo()`, `emxVerificarCsrf()` y `emxVerificarCsrfSiPOST()`.
- Roles: admin/proveedor usan `emxRequireRole()` en rutas principales.
- Google Login: usa ID token y validación de firma en servidor; el Client ID es público y no hay Client Secret guardado.
- Correo SMTP: no hay contraseña real en el paquete; solo placeholders.
- Archivos internos: `.htaccess` evita servir migraciones, documentación, logs, env y scripts auxiliares.

## Pendiente antes de producción real

- Probar flujo completo en tu XAMPP con base de datos real: login, carrito, checkout, factura, devolución, reemplazo, proveedor y notificaciones.
- Ejecutar migraciones acumuladas pendientes en tu PostgreSQL.
- Configurar variables de entorno reales en el servidor.
- No subir `config_correo.php` real ni `.env` real a GitHub.
- Instalar HTTPS en producción para que la cookie segura funcione correctamente.
