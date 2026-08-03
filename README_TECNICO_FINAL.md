# ElectroMax - README técnico final

Fecha de generación: 2026-08-02 23:26:00

## 1. Descripción general del proyecto

ElectroMax es un sistema web de comercio electrónico para venta de electrodomésticos.

El proyecto está desarrollado en PHP con PostgreSQL y está preparado para funcionar en XAMPP usando rutas tradicionales como:

```text
http://localhost/electro2/index.php
http://localhost/electro2/admin.php
http://localhost/electro2/proveedor.php
```

El sistema no solo vende productos. También incluye administración, proveedores, stock, reabastecimiento, carrito, checkout, facturación, garantías, devoluciones, wishlist, notificaciones, soporte y seguimiento de pedidos.

## 2. Objetivo del sistema

El objetivo es permitir que una tienda de electrodomésticos gestione el proceso completo de venta:

```text
cliente navega productos
cliente agrega al carrito
cliente compra
admin aprueba o gestiona pedido
sistema factura
cliente puede hacer seguimiento
cliente puede solicitar devolución o garantía
proveedor puede registrar capacidad de producción
admin puede gestionar reabastecimiento
```

## 3. Roles principales

```text
Cliente:
Compra productos, administra su cuenta, usa wishlist, consulta pedidos, solicita devoluciones o garantías.

Administrador:
Gestiona productos, banners, pedidos, devoluciones, garantías, facturación, correos, analítica y soporte.

Proveedor:
Registra capacidad de producción, descuentos por volumen, propuestas y tiempos de entrega.

Invitado:
Puede navegar catálogo y ver productos, pero para comprar debe iniciar sesión.
```

## 4. Arquitectura actual

El proyecto fue reorganizado por fases para no romper rutas antiguas.

La estructura principal quedó así:

```text
app/
  Config/
  Middleware/
  Helpers/
  Services/
views/
  frontend/
  admin/
  proveedor/
  auth/
  components/
database/
  schema/
  migrations/
  hotfixes/
  functions/
  triggers/
routes/
public/
docs/
storage/
```

## 5. Separación por capas

### app/Config

Contiene configuración del sistema.

Ejemplos:

```text
app/Config/database.php
app/Config/google.php
app/Config/mail.php
app/Config/company.php
```

### app/Middleware

Contiene seguridad transversal.

Ejemplo:

```text
app/Middleware/security.php
```

Aquí se manejan sesión, roles, CSRF y validaciones de acceso.

### app/Helpers

Contiene funciones compartidas que siguen siendo usadas por rutas antiguas.

Ejemplos:

```text
app/Helpers/funciones_backorder.php
app/Helpers/funciones_facturacion.php
app/Helpers/funciones_wishlist.php
app/Helpers/funciones_stock.php
```

### app/Services

Contiene lógica de negocio separada.

Ejemplos:

```text
app/Services/Catalogo/PricingService.php
app/Services/Inventario/SerialNumberService.php
app/Services/Proveedor/SupplierCapacityService.php
app/Services/ReglasNegocio/BusinessRulesReference.php
```

### views

Contiene vistas separadas por tipo de usuario.

```text
views/frontend
views/admin
views/proveedor
views/auth
views/components
```

### database

Contiene los SQL organizados.

```text
database/schema
database/migrations
database/hotfixes
database/functions
database/triggers
database/queries
database/seeds
database/scripts
```

## 6. Compatibilidad con rutas antiguas

Aunque el proyecto ya está organizado, las rutas antiguas siguen funcionando.

Ejemplo:

```php
require_once 'db.php';
```

sigue funcionando, pero internamente carga:

```text
app/Config/database.php
```

Esto se hizo para evitar romper el sistema durante la reorganización.

## 7. Módulos del sistema

### Catálogo y productos

Archivos principales:

- `index.php`
- `producto.php`
- `api_producto.php`
- `api_filtros.php`
- `api_filtrar_productos.php`
- `buscar_sugerencias.php`

### Carrito y checkout

Archivos principales:

- `carrito.php`
- `add_to_cart.php`
- `checkout.php`

### Usuarios y autenticación

Archivos principales:

- `auth.php`
- `google_auth.php`
- `logout.php`
- `config_google.php`

### Panel administrativo

Archivos principales:

- `admin.php`
- `analitica.php`
- `correos_empresa.php`
- `crear_admin.php`
- `crear_usuario_empresa.php`

### Panel proveedor

Archivos principales:

- `proveedor.php`

### Pedidos y tracking

Archivos principales:

- `tracking.php`
- `flujo_admin.php`

### Devoluciones y garantías

Archivos principales:

- `mi_cuenta.php`
- `garantia.php`
- `procesar_devolucion.php`
- `recibir_devolucion.php`
- `responder_devolucion.php`
- `funciones_garantias.php`

### Facturación y correo

Archivos principales:

- `factura_pdf.php`
- `funciones_facturacion.php`
- `config_correo.php`
- `verificar_phpmailer.php`
- `probar_correo_facturacion.php`

### Wishlist y notificaciones

Archivos principales:

- `wishlist.php`
- `api_wishlist.php`
- `notificaciones.php`
- `funciones_wishlist.php`
- `funciones_notificaciones.php`

### Soporte

Archivos principales:

- `soporte.php`
- `soporte_admin.php`
- `funciones_soporte.php`

### Logística, stock y reabastecimiento

Archivos principales:

- `funciones_stock.php`
- `funciones_backorder.php`
- `funciones_logistica.php`
- `simulador_sucursales.php`

## 8. Estado técnico detectado

```text
Archivos PHP revisados: 179
Archivos SQL detectados: 67
Vistas separadas detectadas: 21
Servicios PHP detectados: 4
Helpers detectados: 16
Documentos técnicos detectados: 134
Errores de sintaxis PHP: 0
```

## 9. Cómo ejecutar el proyecto en XAMPP

1. Copiar la carpeta del proyecto dentro de:

```text
C:\xampp\htdocs\electro2
```

2. Iniciar Apache y PostgreSQL.

3. Configurar la base de datos en:

```text
app/Config/database.php
```

o mediante variables de entorno.

4. Ejecutar la estructura SQL base si es una instalación nueva:

```bash
psql -d electro2 -f database/schema/bd.sql
```

5. Ejecutar migraciones necesarias según el estado de la base:

```bash
psql -d electro2 -f database/migrations/nombre_migracion.sql
```

6. Abrir en navegador:

```text
http://localhost/electro2/index.php
```

## 10. Validación técnica

Se validó sintaxis PHP con:

```bash
php -l archivo.php
```

Resultado de esta fase:

```text
Archivos PHP validados: 179
Errores PHP: 0
```

## 11. Resumen de fases realizadas

```text
Fase 1:
Mapa de archivos y dependencias.

Fase 2:
Creación de estructura nueva sin romper rutas.

Fase 3:
Separación de configuración, seguridad y helpers.

Fase 4:
Separación de lógica de negocio comentada.

Fase 5:
Separación de vistas frontend, admin y proveedor.

Fase 6:
Organización de SQL, migraciones, funciones y triggers.

Fase 7:
Adaptadores para conservar rutas antiguas.

Fase 8:
Limpieza segura de documentación y detección de duplicados.

Fase 9:
README técnico final para defensa.
```

## 12. Conclusión

ElectroMax quedó organizado progresivamente por capas sin eliminar rutas antiguas. Esto permite explicar el proyecto como una aplicación que evolucionó desde una estructura PHP plana hacia una arquitectura más ordenada, con separación entre configuración, seguridad, helpers, servicios, vistas, SQL y documentación técnica.
