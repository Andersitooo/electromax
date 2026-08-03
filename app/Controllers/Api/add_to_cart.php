<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_HELPERS_PATH . '/funciones_planes.php';
require_once EMX_HELPERS_PATH . '/funciones_backorder.php';
require_once EMX_HELPERS_PATH . '/funciones_descuentos_volumen.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    $redirect = $_SERVER['HTTP_REFERER'] ?? 'carrito.php';
    $redirect = str_replace(["\r", "\n"], '', (string)$redirect);
    if ($redirect === '' || preg_match('/^https?:\/\//i', $redirect) && stripos($redirect, $_SERVER['HTTP_HOST'] ?? '') === false) {
        $redirect = 'carrito.php';
    }

    $_SESSION['redirect_after_login'] = $redirect;

    http_response_code(401);
    echo json_encode([
        'success' =>false,
        'requires_login' =>true,
        'message' =>'Debes iniciar sesión para agregar productos al carrito.',
        'login_url' =>'auth.php?action=login&msg=debes_iniciar_sesion'
    ]);
    exit;
}

if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

// ID del producto (UUID en texto, no forzar a entero)
$producto_id = isset($_GET['id']) ? trim($_GET['id']) : '';
$cantidad = max(1, (int)($_GET['cantidad'] ?? 1));

if ($producto_id === '') {
    echo json_encode(['success' =>false, 'message' =>'Producto no especificado']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT p.*, pm.url as imagen FROM productos p LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.orden = 1 WHERE p.id = ? AND p.deleted_at IS NULL AND p.is_active = TRUE");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo json_encode(['success' =>false, 'message' =>'Producto no disponible']);
        exit;
    }

    // ===== Precios inteligentes: oferta + volumen + membresía =====
    $cantidad_final = isset($_SESSION['carrito'][$producto_id])
        ? ((int)$_SESSION['carrito'][$producto_id]['cantidad'] + $cantidad)
        : $cantidad;

    $precioCalc = emxCalcularPrecioProductoCarrito($pdo, $producto, $cantidad_final, $_SESSION['usuario_id']);

    // ===== Guardar en sesión con la MISMA estructura que usa carrito.php =====
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

    $plan_backorder = emxGenerarCalendarioBackorder($pdo, $producto_id, $_SESSION['carrito'][$producto_id]['cantidad'], $precioCalc['precio_base']);
    if ($plan_backorder['requiere_backorder']) {
        unset($_SESSION['backorder_planes'][$producto_id]);
    }

    $total_items = array_sum(array_column($_SESSION['carrito'], 'cantidad'));

    echo json_encode([
        'success' =>true,
        'message' =>'¡Producto agregado al carrito!',
        'total_items' =>$total_items,
        'nombre' =>$producto['nombre'],
        'imagen' =>$producto['imagen'] ?? null,
        'requiere_backorder' =>$plan_backorder['requiere_backorder'] ?? false,
        'backorder_resumen' =>$plan_backorder['resumen'] ?? null
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' =>false, 'message' =>'Error al procesar la solicitud']);
}