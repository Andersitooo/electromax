# Parche ElectroMax: errores encontrados + mejora de estimaciones

## Archivos incluidos

Reemplaza estos archivos en tu proyecto actual:

- `producto.php`
- `seguridad.php`
- `funciones_stock.php`
- `admin.php`
- `funciones_backorder.php`
- `carrito.php`

## Qué corrige

### 1. Error `s.notas_admin`

El proyecto consultaba siempre `solicitudes_reabastecimiento.notas_admin`, pero tu base actual no tiene esa columna.

Ahora el código revisa si la columna existe:

- Si existe, la usa.
- Si no existe, usa texto vacío y continúa funcionando.

No necesitas modificar la BD para corregir este error.

### 2. Error `emxVerificarCsrfSiPOST()` indefinida

`producto.php` llamaba esa función antes de cargar `seguridad.php`.

Ahora el orden correcto es:

```php
require_once 'seguridad.php';
require_once 'db.php';
emxVerificarCsrfSiPOST();
```

### 3. Carrito con cantidad mayor al stock

Ahora el carrito muestra dos opciones claras:

- Opción A: entrega parcial.
- Opción B: entrega total.

El cliente puede aceptar cualquiera de las dos o ajustar la cantidad al stock disponible.

### 4. Proveedor ganador en estimación

Para entrega total, el sistema evalúa hasta 5 proveedores asociados al producto y marca el ganador usando un puntaje simulado.

Puntaje menor = mejor.

La fórmula usada es:

- Costo unitario estimado: 50%.
- Tiempo de entrega: 35%.
- Riesgo por defectos: 10%.
- Disponibilidad inmediata: 5% como bonificación.

Como tu base todavía no tiene precio unitario por proveedor en `producto_proveedor`, la simulación usa:

- `productos.costo_unitario`, si existe y tiene valor.
- Si no existe o está en cero, usa aproximadamente el 70% de `precio_base`.
- Luego aplica `capacidad_proveedor.descuentos_volumen`, si existen.

## Importante

Este parche evita modificar tu base de datos. Si quieres guardar notas administrativas en solicitudes de reabastecimiento, después sí conviene agregar:

```sql
ALTER TABLE solicitudes_reabastecimiento ADD COLUMN IF NOT EXISTS notas_admin TEXT;
```

Pero no es obligatorio para que el proyecto funcione.
