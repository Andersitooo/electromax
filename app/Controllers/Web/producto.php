<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
emxVerificarCsrfSiPOST();
require_once EMX_HELPERS_PATH . '/funciones_planes.php';
require_once EMX_HELPERS_PATH . '/funciones_wishlist.php';
require_once EMX_HELPERS_PATH . '/funciones_backorder.php';
require_once EMX_HELPERS_PATH . '/funciones_ficha_tecnica.php';
require_once EMX_HELPERS_PATH . '/funciones_home.php';
require_once EMX_HELPERS_PATH . '/funciones_descuentos_volumen.php';

// ==========================================
// CONTADOR DE NOTIFICACIONES NO LEÍDAS
// ==========================================
$notificaciones_no_leidas = 0;
if (isset($_SESSION['usuario_id'])) {
    $notificaciones_no_leidas = contarNotificacionesNoLeidas($pdo, $_SESSION['usuario_id']);
}

// ==========================================
// PROCESAR WISHLIST (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_action'])) {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: auth.php?action=login');
        exit();
    }
    $w_action = $_POST['wishlist_action'];
    $w_prod_id = $_POST['producto_id'];
    
    if ($w_action === 'add') {
        agregarAWishlist($pdo, $_SESSION['usuario_id'], $w_prod_id);
        header('Location: producto.php?id=' . $w_prod_id . '&wishlist_msg=added');
    } else {
        eliminarDeWishlist($pdo, $_SESSION['usuario_id'], $w_prod_id);
        header('Location: producto.php?id=' . $w_prod_id . '&wishlist_msg=removed');
    }
    exit();
}

// ==========================================
// PROCESAR ENVÍO DE RESEÑA (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (!isset($_SESSION['usuario_id']) || !isset($_POST['producto_id'])) {
        header('Location: auth.php?action=login');
        exit();
    }
    
    $prod_id = $_POST['producto_id'];
    $calificacion = (int)($_POST['calificacion'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $comentario = trim($_POST['comentario'] ?? '');
    
    if ($calificacion >= 1 && $calificacion <= 5 && !empty($comentario)) {
        $stmt = $pdo->prepare("INSERT INTO reseñas_productos (producto_id, usuario_id, calificacion, titulo, comentario, aprobado) VALUES (?, ?, ?, ?, ?, TRUE)");
        $stmt->execute([$prod_id, $_SESSION['usuario_id'], $calificacion, $titulo, $comentario]);
        header('Location: producto.php?id=' . $prod_id . '#tab-reviews');
        exit();
    }
}

if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
$total_items_carrito = array_sum(array_column($_SESSION['carrito'], 'cantidad'));

$producto_id = $_GET['id'] ?? null;
$producto = null;
$imagenes = [];
$productos_relacionados = [];
$tiene_descuento = false;
$porcentaje_descuento = 0;
$precio_final = 0;
$precio_con_iva = 0;
$stock_bajo = false;
$agotado = false;

$reviews = [];
$avg_rating = 0;
$total_reviews = 0;
$rangos_volumen_producto = [];

if ($producto_id && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT p.*, c.nombre as categoria, c.slug as categoria_slug, m.nombre as marca FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id LEFT JOIN marcas m ON p.marca_id = m.id WHERE p.id = ? AND p.deleted_at IS NULL AND p.is_active = TRUE");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        $stmt_img = $pdo->prepare("SELECT url FROM producto_multimedia WHERE producto_id = ? AND tipo = 'FOTO' ORDER BY orden ASC");
        $stmt_img->execute([$producto_id]);
        $imagenes = $stmt_img->fetchAll(PDO::FETCH_COLUMN);

        $iva = $producto['iva_porcentaje'] ?? 15;
        $precio_base = $producto['precio_base'];
        $precio_con_iva = $precio_base * (1 + ($iva / 100));
        
        $raw_discount = $producto['descuento_porcentaje'] ?? 0;
        $discount_val = $raw_discount < 1 && $raw_discount >0 ? $raw_discount * 100 : $raw_discount;

        $precio_final = $precio_con_iva;
        if ($discount_val >0) {
            $hoy_check = date('Y-m-d');
            $desde = $producto['descuento_desde'] ?? null;
            $hasta = $producto['descuento_hasta'] ?? null;
            
            if ((!$desde || $hoy_check >= $desde) && (!$hasta || $hoy_check <= $hasta)) {
                $tiene_descuento = true;
                $porcentaje_descuento = round($discount_val);
                $precio_final = $precio_con_iva * (1 - ($porcentaje_descuento / 100));
            }
        }

        $precio_miembro = $precio_final;
        $rangos_volumen_producto = json_decode($producto['descuentos_volumen_rangos'] ?? '[]', true) ?: [];
        $ahorro_miembro = 0;
        $tiene_descuento_plan = false;

        if (isset($_SESSION['usuario_id'])) {
            $beneficios = obtenerBeneficiosUsuario($pdo, $_SESSION['usuario_id']);
            $precio_calculado = aplicarDescuentoPlan($precio_final, $beneficios);
            
            if ($precio_calculado < $precio_final) {
                $precio_miembro = $precio_calculado;
                $ahorro_miembro = $precio_final - $precio_miembro;
                $tiene_descuento_plan = true;
            }
        }

        $stock_bajo = $producto['stock_actual_global'] <= 5 && $producto['stock_actual_global'] >0;
        $agotado = $producto['stock_actual_global'] == 0;

        if (isset($_SESSION['usuario_id'])) {
            $stmt_vista = $pdo->prepare("INSERT INTO productos_vistos (usuario_id, producto_id) VALUES (?, ?) ON CONFLICT (usuario_id, producto_id) DO UPDATE SET visto_en = NOW()");
            $stmt_vista->execute([$_SESSION['usuario_id'], $producto_id]);
        }

        $productos_relacionados = emxObtenerRecomendadosProducto($pdo, $producto, $producto_id, 10);

        $stmt_reviews = $pdo->prepare("SELECT r.*, u.nombres, u.apellidos, u.foto_perfil_url FROM reseñas_productos r JOIN usuarios u ON r.usuario_id = u.id WHERE r.producto_id = ? AND r.aprobado = TRUE ORDER BY r.created_at DESC LIMIT 10");
        $stmt_reviews->execute([$producto_id]);
        $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

        $stmt_stats = $pdo->prepare("SELECT AVG(calificacion) as avg_rating, COUNT(*) as total_reviews FROM reseñas_productos WHERE producto_id = ? AND aprobado = TRUE");
        $stmt_stats->execute([$producto_id]);
        $review_stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
        $avg_rating = round($review_stats['avg_rating'], 1) ?: 0;
        $total_reviews = $review_stats['total_reviews'] ?: 0;
    }
}

// Verificar si el producto está en la wishlist del usuario
$en_wishlist = false;
if (isset($_SESSION['usuario_id']) && $producto_id) {
    $en_wishlist = estaEnWishlist($pdo, $_SESSION['usuario_id'], $producto_id);
}

$wishlist_msg = $_GET['wishlist_msg'] ?? '';
$imagenes_json = json_encode($imagenes);

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/frontend/producto_view.php
require EMX_VIEWS_PATH . '/frontend/producto_view.php';
exit;
