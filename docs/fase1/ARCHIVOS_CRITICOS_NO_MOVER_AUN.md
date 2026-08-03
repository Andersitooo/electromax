# Fase 1 - Archivos críticos que no deben moverse todavía

Estos archivos tienen alta dependencia o impacto directo en rutas existentes. En las siguientes fases se deben rodear con adaptadores antes de moverlos.

| Archivo | Por qué es crítico | Acción segura |
| --- | --- | --- |
| `db.php` | Conexión a PostgreSQL. Lo usan muchas páginas. | No mover todavía; primero crear `app/Config/database.php` y dejar puente en raíz. |
| `seguridad.php` | Sesiones, roles, CSRF, sanitización, uploads y redirecciones. | No mover todavía; primero crear `app/Core/Security.php` o `app/Helpers/security.php` y puente. |
| `admin.php` | Panel principal de administrador con módulos, acciones POST, SQL, vistas y JavaScript. | No mover completo en una sola fase. Primero extraer servicios y vistas por módulo. |
| `proveedor.php` | Panel de proveedor, capacidades, propuestas y solicitudes. | Separar después de crear estructura de proveedor y servicios de reabastecimiento. |
| `mi_cuenta.php` | Panel del cliente, pedidos, devoluciones, seguridad, membresía y Google. | No insertar navbar global ni mover sin pruebas; ya tuvo riesgo de rotura visual. |
| `checkout.php` | Cálculo de totales, dirección, pago simulado, stock, backorder, garantías y factura. | Archivo altamente crítico; dividir por servicios pero mantener ruta. |
| `carrito.php` | Cantidades, descuentos, backorder y resumen de compra. | Mover lógica a servicios después de tener adaptador de ruta. |
| `producto.php` | Ficha de producto, wishlist, reseñas, ficha técnica, relacionados y carrito. | Separar vista y lógica con cuidado por dependencias JS. |
| `flujo_admin.php` | Máquina de estados para pedidos, devoluciones, reemplazos, reembolsos e incidencias. | Convertir después en servicios de dominio; no tocar antes de mapear estados. |
| `funciones_facturacion.php` | Facturas, notas de crédito, PDF, email y liberación de inventario cancelado. | Separar en servicios con pruebas manuales de factura y nota crédito. |
| `funciones_backorder.php` | Backorder, calendario, reposición y evaluación de proveedores. | Mantener intacto hasta separar módulo de stock/proveedores. |
| `funciones_stock.php` | Reabastecimiento, cotizaciones y aprobación de proveedor. | No mover antes de confirmar dependencias con admin y checkout. |
| `config_correo.php` | Configuración SMTP local. `.gitignore` ya lo excluye. | Mover a entorno/config segura luego de dejar archivo puente. |
| `config_google.php` y `google_auth.php` | Login/vinculación Google. | Mantener rutas antiguas porque Google depende de URL autorizada. |
| `components/navbar.php` y `components/footer.php` | Componentes visuales compartidos por páginas cliente. | Mover a vistas en Fase 5, manteniendo includes puente. |

## Riesgos de mover sin adaptadores

- Error de rutas por `require_once` relativo.
- Formularios POST apuntando a archivos antiguos.
- Redirecciones `header(Location: ...)` rotas.
- Google Login fallando por redirect URI diferente.
- Archivos PDF, guías, facturas o etiquetas buscando rutas antiguas.
- Pérdida de estilos por mover componentes o assets sin actualizar enlaces.

## Reglas para las fases siguientes

- Crear estructura nueva primero, sin mover archivos críticos.
- Crear archivos puente en la raíz cuando se mueva una clase o helper.
- Separar una responsabilidad por vez: configuración, seguridad, helpers, servicios, repositorios, vistas.
- No eliminar archivos hasta Fase 8.