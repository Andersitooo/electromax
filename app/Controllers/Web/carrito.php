<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_HELPERS_PATH . '/funciones_planes.php';
require_once EMX_HELPERS_PATH . '/funciones_backorder.php';
require_once EMX_HELPERS_PATH . '/funciones_descuentos_volumen.php';

// ==========================================
//  PROTECCIÓN: REQUERIR INICIO DE SESIÓN
// ==========================================
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: auth.php?action=login&msg=debes_iniciar_sesion');
    exit();
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$action = $_GET['action'] ?? null;
$msg = $_GET['msg'] ?? null;
$msg_type = $_GET['msg_type'] ?? 'success';

// ============================================
// PROCESAR ACCIONES
// ============================================

if ($action === 'add' && isset($_GET['id'])) {
    $producto_id = $_GET['id'];
    $cantidad = max(1, (int)($_GET['cantidad'] ?? 1));
    
    try {
        $stmt = $pdo->prepare("SELECT p.*, pm.url as imagen FROM productos p LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.orden = 1 WHERE p.id = ? AND p.deleted_at IS NULL AND p.is_active = TRUE");
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($producto) {
            $cantidad_final = isset($_SESSION['carrito'][$producto_id])
                ? ((int)$_SESSION['carrito'][$producto_id]['cantidad'] + $cantidad)
                : $cantidad;

            $precioCalc = emxCalcularPrecioProductoCarrito($pdo, $producto, $cantidad_final, $_SESSION['usuario_id'] ?? null);

            $_SESSION['carrito'][$producto_id] = [
                'producto_id' =>$producto_id,
                'nombre' =>$producto['nombre'],
                'precio_base' =>$precioCalc['precio_base'],
                'precio_con_iva' =>$precioCalc['precio_con_iva'],
                'precio_final' =>$precioCalc['precio_final'],
                'descuento_aplicado' =>$precioCalc['descuento_total_porcentaje'],
                'descuento_producto' =>$precioCalc['descuento_producto'],
                'descuento_volumen' =>$precioCalc['descuento_volumen'],
                'descuento_plan' =>$precioCalc['descuento_plan'],
                'rango_volumen_label' =>$precioCalc['rango_volumen_label'],
                'iva' =>$precioCalc['iva'],
                'cantidad' =>$cantidad_final,
                'imagen' =>$producto['imagen'] ?? null,
                'stock' =>$producto['stock_actual_global']
            ];
            $msg = '¡Producto agregado al carrito exitosamente!';
        } else {
            $msg = 'Producto no disponible o sin stock';
            $msg_type = 'error';
        }
    } catch(PDOException $e) {
        $msg = 'Error al procesar: ' . $e->getMessage();
        $msg_type = 'error';
    }
    header("Location: carrito.php?msg=" . urlencode($msg) . "&msg_type=" . $msg_type);
    exit();
}

if ($action === 'update' && isset($_GET['id'])) {
    $producto_id = $_GET['id'];
    $cantidad = max(1, (int)($_GET['cantidad'] ?? 1));
    
    if (isset($_SESSION['carrito'][$producto_id])) {
        $_SESSION['carrito'][$producto_id]['cantidad'] = $cantidad;
        unset($_SESSION['backorder_planes'][$producto_id]);
        $msg = 'Cantidad actualizada correctamente';
        $msg_type = ($cantidad >(int)($_SESSION['carrito'][$producto_id]['stock'] ?? 0)) ? 'warning' : 'success';
        if ($msg_type === 'warning') $msg = 'Cantidad actualizada. Revisa y acepta el calendario de entrega por sobrestock.';
    }
    header("Location: carrito.php?msg=" . urlencode($msg) . "&msg_type=" . $msg_type);
    exit();
}

if ($action === 'aceptar_backorder' && isset($_GET['id'])) {
    $producto_id = $_GET['id'];
    $opcion = $_GET['opcion'] ?? 'total';
    if (isset($_SESSION['carrito'][$producto_id])) {
        $item = $_SESSION['carrito'][$producto_id];
        $stmt_bo = $pdo->prepare("SELECT precio_base FROM productos WHERE id = ? AND deleted_at IS NULL");
        $stmt_bo->execute([$producto_id]);
        $precio = (float)($stmt_bo->fetchColumn() ?: ($item['precio_base'] ?? 0));
        $plan = emxGenerarCalendarioBackorder($pdo, $producto_id, (int)$item['cantidad'], $precio);
        emxGuardarPlanBackorderEnSesion($producto_id, $plan, $opcion);
        $msg = 'Calendario de sobrestock aceptado. Puedes continuar al checkout.';
        $msg_type = 'success';
    }
    header("Location: carrito.php?msg=" . urlencode($msg ?? 'Acción aplicada') . "&msg_type=" . ($msg_type ?? 'success'));
    exit();
}

if ($action === 'rechazar_backorder' && isset($_GET['id'])) {
    $producto_id = $_GET['id'];
    if (isset($_SESSION['carrito'][$producto_id])) {
        // Si el cliente rechaza el calendario de sobrestock, no se conserva una cantidad parcial.
        // Conceptualmente rechazó esa compra en esas condiciones, por eso el ítem queda en 0
        // y se retira del carrito para evitar que pase al checkout por error.
        unset($_SESSION['backorder_planes'][$producto_id]);
        unset($_SESSION['carrito'][$producto_id]);
        $msg = 'Sobrestock rechazado. El producto se retiró del carrito y la cantidad quedó en 0.';
        $msg_type = 'warning';
    }
    header("Location: carrito.php?msg=" . urlencode($msg ?? 'Acción aplicada') . "&msg_type=" . ($msg_type ?? 'success'));
    exit();
}

if ($action === 'remove' && isset($_GET['id'])) {
    $producto_id = $_GET['id'];
    if (isset($_SESSION['carrito'][$producto_id])) {
        unset($_SESSION['carrito'][$producto_id]);
        $msg = 'Producto eliminado del carrito';
    }
    header("Location: carrito.php?msg=" . urlencode($msg) . "&msg_type=success");
    exit();
}

if ($action === 'clear') {
    $_SESSION['carrito'] = [];
    $msg = 'El carrito ha sido vaciado completamente';
    header("Location: carrito.php?msg=" . urlencode($msg) . "&msg_type=success");
    exit();
}

// ============================================
// CALCULAR TOTALES Y RECALCULAR PRECIOS
// ============================================

$productos_carrito = [];
$subtotal_sin_desc = 0;
$total_descuento = 0;
$total_iva = 0;
$total_general = 0;
$total_items = 0;

$beneficios_usuario = [];
if (isset($_SESSION['usuario_id'])) {
    $beneficios_usuario = obtenerBeneficiosUsuario($pdo, $_SESSION['usuario_id']);
}

foreach ($_SESSION['carrito'] as $key =>$item) {
    $stmt = $pdo->prepare("SELECT p.*, pm.url as imagen FROM productos p LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.orden = 1 WHERE p.id = ? AND p.deleted_at IS NULL");
    $stmt->execute([$item['producto_id']]);
    $producto_db = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto_db) {
        unset($_SESSION['carrito'][$key]);
        continue;
    }
    
    $item_cantidad = (int)($_SESSION['carrito'][$key]['cantidad'] ?? 1);
    $precioCalc = emxCalcularPrecioProductoCarrito($pdo, $producto_db, $item_cantidad, $_SESSION['usuario_id'] ?? null);

    $iva = $precioCalc['iva'];
    $precio_base = $precioCalc['precio_base'];
    $precio_con_iva = $precioCalc['precio_con_iva'];
    $precio_final_unitario = $precioCalc['precio_final'];
    $descuento_total_unitario = $precio_con_iva - $precio_final_unitario;
    $descuento_total_porcentaje = $precioCalc['descuento_total_porcentaje'];

    $_SESSION['carrito'][$key]['precio_base'] = $precio_base;
    $_SESSION['carrito'][$key]['precio_final'] = $precio_final_unitario;
    $_SESSION['carrito'][$key]['descuento_aplicado'] = $descuento_total_porcentaje;
    $_SESSION['carrito'][$key]['descuento_producto'] = $precioCalc['descuento_producto'];
    $_SESSION['carrito'][$key]['descuento_volumen'] = $precioCalc['descuento_volumen'];
    $_SESSION['carrito'][$key]['descuento_plan'] = $precioCalc['descuento_plan'];
    $_SESSION['carrito'][$key]['rango_volumen_label'] = $precioCalc['rango_volumen_label'];
    $_SESSION['carrito'][$key]['precio_con_iva'] = $precio_con_iva;
    $_SESSION['carrito'][$key]['iva'] = $iva;
    $_SESSION['carrito'][$key]['stock'] = $producto_db['stock_actual_global'];
    $_SESSION['carrito'][$key]['imagen'] = $producto_db['imagen'] ?? ($_SESSION['carrito'][$key]['imagen'] ?? null);
    $_SESSION['carrito'][$key]['nombre'] = $producto_db['nombre'] ?? ($_SESSION['carrito'][$key]['nombre'] ?? 'Producto');
    $plan_backorder = emxGenerarCalendarioBackorder($pdo, $item['producto_id'], $item_cantidad, $producto_db['precio_base']);
    $_SESSION['carrito'][$key]['stock'] = (int)($plan_backorder['stock_actual'] ?? $producto_db['stock_actual_global']);
    $_SESSION['carrito'][$key]['requiere_backorder'] = $plan_backorder['requiere_backorder'];
    $_SESSION['carrito'][$key]['plan_backorder_preview'] = $plan_backorder;

    $item_total = $precio_final_unitario * $item_cantidad;
    $item_ahorro = $descuento_total_unitario * $item_cantidad;
    $item_subtotal_sin_desc = $precio_con_iva * $item_cantidad;
    
    $precio_base_con_desc = $precio_base * (1 - ($descuento_total_porcentaje / 100));
    $item_iva_total = $precio_base_con_desc * ($iva / 100) * $item_cantidad;
    
    $productos_carrito[] = array_merge($_SESSION['carrito'][$key], [
        'total' =>$item_total,
        'ahorro' =>$item_ahorro,
        'subtotal_sin_desc' =>$item_subtotal_sin_desc,
        'iva_total' =>$item_iva_total
    ]);
    
    $subtotal_sin_desc += $item_subtotal_sin_desc;
    $total_descuento += $item_ahorro;
    $total_iva += $item_iva_total;
    $total_general += $item_total;
    $total_items += $item_cantidad;
}

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/frontend/carrito_view.php
require EMX_VIEWS_PATH . '/frontend/carrito_view.php';
exit;
