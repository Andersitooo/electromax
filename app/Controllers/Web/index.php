<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_HELPERS_PATH . '/funciones_wishlist.php'; // <-- NUEVO: Funciones de wishlist
require_once EMX_HELPERS_PATH . '/funciones_home.php';

if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
$total_items_carrito = array_sum(array_column($_SESSION['carrito'], 'cantidad'));

$foto_perfil_usuario = null;
$notificaciones_no_leidas = 0;
$wishlist_ids = [];

if (isset($_SESSION['usuario_id'])) {
    try {
        $stmt_foto = $pdo->prepare("SELECT foto_perfil_url FROM usuarios WHERE id = ?");
        $stmt_foto->execute([$_SESSION['usuario_id']]);
        $foto_perfil_usuario = $stmt_foto->fetchColumn();
        
        // Obtener notificaciones no leídas
        $notificaciones_no_leidas = contarNotificacionesNoLeidas($pdo, $_SESSION['usuario_id']);
        
        // Obtener IDs de productos en wishlist para renderizar el estado inicial
        $stmt_w = $pdo->prepare("SELECT producto_id FROM wishlist WHERE usuario_id = ?");
        $stmt_w->execute([$_SESSION['usuario_id']]);
        $wishlist_ids = $stmt_w->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $foto_perfil_usuario = null;
    }
}

$productos = [];
$categorias_nav = [];
$categorias_display = [];
$secciones_home = [];
$productos_best = [];

$filtro_activo = false;
$titulo_filtro = "";
$subtitulo_filtro = "";
$where_clauses = ["p.deleted_at IS NULL", "p.is_active = TRUE"];
$params = [];

if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $filtro_activo = true;
    $where_clauses[] = "c.slug = :categoria_slug";
    $params[':categoria_slug'] = $_GET['categoria'];
    $stmt_cat = $pdo->prepare("SELECT nombre FROM categorias WHERE slug = ?");
    $stmt_cat->execute([$_GET['categoria']]);
    $cat_nombre = $stmt_cat->fetchColumn();
    $titulo_filtro = ($cat_nombre ?: $_GET['categoria']);
    $subtitulo_filtro = "Explora todos los productos de " . ($cat_nombre ?: $_GET['categoria']);
}

if (isset($_GET['descuento_min']) && is_numeric($_GET['descuento_min'])) {
    $filtro_activo = true;
    $min_desc = (float)$_GET['descuento_min'];

    // El descuento puede estar guardado como 10 o como 0.10.
    // Esta expresión lo normaliza a porcentaje real y acepta exactamente 10%.
    $where_clauses[] = "(CASE WHEN p.descuento_porcentaje > 0 AND p.descuento_porcentaje <= 1 THEN p.descuento_porcentaje * 100 ELSE p.descuento_porcentaje END) >= :descuento_min";
    $where_clauses[] = "(p.descuento_porcentaje IS NOT NULL AND p.descuento_porcentaje > 0)";
    $where_clauses[] = "(p.descuento_desde IS NULL OR p.descuento_desde <= CURRENT_DATE)";
    $where_clauses[] = "(p.descuento_hasta IS NULL OR p.descuento_hasta >= CURRENT_DATE)";
    $params[':descuento_min'] = $min_desc;

    $titulo_filtro = "Ofertas desde " . round($min_desc) . "% de descuento";
    $subtitulo_filtro = "Productos con " . round($min_desc) . "% de descuento o más";
}

if (isset($_GET['prime_only']) && $_GET['prime_only'] === '1') {
    $filtro_activo = true;
    if (isset($_SESSION['usuario_id'])) {
        $stmt_prime = $pdo->prepare("SELECT p.es_prime FROM usuarios u LEFT JOIN planes p ON u.plan_id = p.id WHERE u.id = ? AND p.es_prime = TRUE");
        $stmt_prime->execute([$_SESSION['usuario_id']]);
        if (!$stmt_prime->fetchColumn()) { $where_clauses[] = "1 = 0"; } else { $where_clauses[] = "p.is_prime_exclusive = TRUE"; }
    } else { $where_clauses[] = "1 = 0"; }
    $titulo_filtro = "Exclusivo para Miembros Prime";
    $subtitulo_filtro = "Beneficios y precios especiales solo para miembros VIP";
}

if (isset($_GET['tag']) && !empty($_GET['tag'])) {
    $filtro_activo = true;
    $where_clauses[] = "p.id IN (SELECT producto_id FROM producto_tags_rel WHERE tag_id IN (SELECT id FROM product_tags WHERE nombre = :tag_nombre))";
    $params[':tag_nombre'] = $_GET['tag'];
    $titulo_filtro = "Campaña: " . htmlspecialchars($_GET['tag']);
    $subtitulo_filtro = "Productos destacados de esta campaña especial";
}

$busqueda_query = '';
$busqueda_activa = false;
$busqueda_like = '';
$busqueda_prefix = '';
$busqueda_tokens = [];
$busqueda_usar_trgm = false;

$emx_escape_like = static function ($value) {
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], (string)$value);
};

$emx_pg_trgm_disponible = static function ($pdo) {
    try {
        $stmt = $pdo->query("SELECT to_regprocedure('similarity(text,text)') IS NOT NULL");
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
};

if (isset($_GET['q']) && trim((string)$_GET['q']) !== '') {
    $filtro_activo = true;
    $busqueda_activa = true;
    $busqueda_query = trim(preg_replace('/\s+/u', ' ', (string)$_GET['q']));
    $busqueda_query = mb_substr($busqueda_query, 0, 80);
    $busqueda_like = '%' . $emx_escape_like($busqueda_query) . '%';
    $busqueda_prefix = $emx_escape_like($busqueda_query) . '%';
    $busqueda_usar_trgm = (mb_strlen($busqueda_query) >= 3 && isset($pdo) && $emx_pg_trgm_disponible($pdo));

    $raw_tokens = preg_split('/\s+/u', mb_strtolower($busqueda_query));
    $busqueda_tokens = [];
    foreach ($raw_tokens as $token) {
        $token = trim($token);
        // Permitimos 1 letra para que "L" ya encuentre Lavadora, Laptop, etc.
        if (mb_strlen($token) >= 1 && !in_array($token, $busqueda_tokens, true)) {
            $busqueda_tokens[] = $token;
        }
    }
    $busqueda_tokens = array_slice($busqueda_tokens, 0, 6);

    $search_clauses = ["(
        p.nombre ILIKE :busq_nombre ESCAPE '\\'
        OR p.nombre ILIKE :busq_nombre_prefix ESCAPE '\\'
        OR COALESCE(p.descripcion_corta,'') ILIKE :busq_descripcion ESCAPE '\\'
        OR COALESCE(p.sku,'') ILIKE :busq_sku ESCAPE '\\'
        OR COALESCE(m.nombre,'') ILIKE :busq_marca ESCAPE '\\'
        OR COALESCE(c.nombre,'') ILIKE :busq_categoria ESCAPE '\\'
    )"];
    $params[':busq_nombre'] = $busqueda_like;
    $params[':busq_nombre_prefix'] = $busqueda_prefix;
    $params[':busq_descripcion'] = $busqueda_like;
    $params[':busq_sku'] = $busqueda_like;
    $params[':busq_marca'] = $busqueda_like;
    $params[':busq_categoria'] = $busqueda_like;

    foreach ($busqueda_tokens as $i => $token) {
        $token_like = '%' . $emx_escape_like($token) . '%';
        $token_prefix = $emx_escape_like($token) . '%';
        $search_clauses[] = "(
            p.nombre ILIKE :busq_tok_{$i}_nombre ESCAPE '\\'
            OR p.nombre ILIKE :busq_tok_{$i}_nombre_prefix ESCAPE '\\'
            OR COALESCE(p.descripcion_corta,'') ILIKE :busq_tok_{$i}_descripcion ESCAPE '\\'
            OR COALESCE(p.sku,'') ILIKE :busq_tok_{$i}_sku ESCAPE '\\'
            OR COALESCE(m.nombre,'') ILIKE :busq_tok_{$i}_marca ESCAPE '\\'
            OR COALESCE(c.nombre,'') ILIKE :busq_tok_{$i}_categoria ESCAPE '\\'
        )";
        $params[":busq_tok_{$i}_nombre"] = $token_like;
        $params[":busq_tok_{$i}_nombre_prefix"] = $token_prefix;
        $params[":busq_tok_{$i}_descripcion"] = $token_like;
        $params[":busq_tok_{$i}_sku"] = $token_like;
        $params[":busq_tok_{$i}_marca"] = $token_like;
        $params[":busq_tok_{$i}_categoria"] = $token_like;
    }

    if ($busqueda_usar_trgm) {
        $search_clauses[] = "(
            similarity(LOWER(p.nombre), LOWER(:busq_fuzzy_nombre)) >= 0.18
            OR similarity(LOWER(COALESCE(m.nombre,'')), LOWER(:busq_fuzzy_marca)) >= 0.22
            OR similarity(LOWER(COALESCE(c.nombre,'')), LOWER(:busq_fuzzy_categoria)) >= 0.22
        )";
        $params[':busq_fuzzy_nombre'] = $busqueda_query;
        $params[':busq_fuzzy_marca'] = $busqueda_query;
        $params[':busq_fuzzy_categoria'] = $busqueda_query;
    }

    $where_clauses[] = '(' . implode(' OR ', $search_clauses) . ')';

    $titulo_filtro = "Resultados de búsqueda";
    $subtitulo_filtro = "Resultados para '" . htmlspecialchars($busqueda_query) . "'";
}

$categoria_actual_id = null;
if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    try {
        $stmt_cat_id = $pdo->prepare("SELECT id FROM categorias WHERE slug = ?");
        $stmt_cat_id->execute([$_GET['categoria']]);
        $categoria_actual_id = $stmt_cat_id->fetchColumn();
    } catch (Exception $e) { $categoria_actual_id = null; }
}

if (isset($pdo)) {
    $stmt_cat = $pdo->query("SELECT id, nombre, slug FROM categorias WHERE is_active = TRUE ORDER BY nombre");
    $categorias_nav = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
    try {
        $stmt_cat_full = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM productos WHERE categoria_id = c.id AND deleted_at IS NULL AND is_active = TRUE) as total_productos FROM categorias c WHERE c.is_active = TRUE ORDER BY c.nombre");
        $categorias_display = $stmt_cat_full->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { $categorias_display = $categorias_nav; }

    if (!$filtro_activo) {
        $secciones_home = emxObtenerSeccionesHome($pdo);
        $productos_best = emxObtenerMasVendidos($pdo, 12);
    }

    $where_sql = implode(' AND ', $where_clauses);
    $limit = $filtro_activo ? "" : "LIMIT 8";

    $search_score_sql = "0 AS search_score";
    $order_sql = "p.created_at DESC";
    $execute_params = $params;

    if (!empty($busqueda_activa)) {
        $score_parts = [
            "CASE WHEN LOWER(p.nombre) = LOWER(:score_exact_nombre) THEN 140 ELSE 0 END",
            "CASE WHEN LOWER(p.nombre) LIKE LOWER(:score_prefix_nombre) ESCAPE '\\' THEN 110 ELSE 0 END",
            "CASE WHEN LOWER(COALESCE(p.sku,'')) = LOWER(:score_exact_sku) THEN 90 ELSE 0 END",
            "CASE WHEN LOWER(COALESCE(m.nombre,'')) LIKE LOWER(:score_marca) ESCAPE '\\' THEN 50 ELSE 0 END",
            "CASE WHEN LOWER(COALESCE(c.nombre,'')) LIKE LOWER(:score_categoria) ESCAPE '\\' THEN 42 ELSE 0 END",
            "CASE WHEN LOWER(COALESCE(p.descripcion_corta,'')) LIKE LOWER(:score_descripcion) ESCAPE '\\' THEN 18 ELSE 0 END",
        ];
        $score_params = [
            ':score_exact_nombre' => $busqueda_query,
            ':score_prefix_nombre' => $busqueda_prefix,
            ':score_exact_sku' => $busqueda_query,
            ':score_marca' => $busqueda_like,
            ':score_categoria' => $busqueda_like,
            ':score_descripcion' => $busqueda_like,
        ];

        foreach ($busqueda_tokens as $i => $token) {
            $token_like = '%' . $emx_escape_like($token) . '%';
            $token_prefix = $emx_escape_like($token) . '%';
            $score_parts[] = "CASE WHEN p.nombre ILIKE :score_tok_{$i}_nombre_prefix ESCAPE '\\' THEN 35 ELSE 0 END";
            $score_parts[] = "CASE WHEN p.nombre ILIKE :score_tok_{$i}_nombre ESCAPE '\\' THEN 22 ELSE 0 END";
            $score_parts[] = "CASE WHEN COALESCE(p.descripcion_corta,'') ILIKE :score_tok_{$i}_descripcion ESCAPE '\\' THEN 9 ELSE 0 END";
            $score_parts[] = "CASE WHEN COALESCE(m.nombre,'') ILIKE :score_tok_{$i}_marca ESCAPE '\\' THEN 14 ELSE 0 END";
            $score_parts[] = "CASE WHEN COALESCE(c.nombre,'') ILIKE :score_tok_{$i}_categoria ESCAPE '\\' THEN 12 ELSE 0 END";
            $score_params[":score_tok_{$i}_nombre_prefix"] = $token_prefix;
            $score_params[":score_tok_{$i}_nombre"] = $token_like;
            $score_params[":score_tok_{$i}_descripcion"] = $token_like;
            $score_params[":score_tok_{$i}_marca"] = $token_like;
            $score_params[":score_tok_{$i}_categoria"] = $token_like;
        }

        if ($busqueda_usar_trgm) {
            $score_parts[] = "CASE WHEN similarity(LOWER(p.nombre), LOWER(:score_fuzzy_nombre_a)) >= 0.18 THEN similarity(LOWER(p.nombre), LOWER(:score_fuzzy_nombre_b)) * 55 ELSE 0 END";
            $score_parts[] = "CASE WHEN similarity(LOWER(COALESCE(m.nombre,'')), LOWER(:score_fuzzy_marca_a)) >= 0.22 THEN similarity(LOWER(COALESCE(m.nombre,'')), LOWER(:score_fuzzy_marca_b)) * 35 ELSE 0 END";
            $score_parts[] = "CASE WHEN similarity(LOWER(COALESCE(c.nombre,'')), LOWER(:score_fuzzy_categoria_a)) >= 0.22 THEN similarity(LOWER(COALESCE(c.nombre,'')), LOWER(:score_fuzzy_categoria_b)) * 30 ELSE 0 END";
            $score_params[':score_fuzzy_nombre_a'] = $busqueda_query;
            $score_params[':score_fuzzy_nombre_b'] = $busqueda_query;
            $score_params[':score_fuzzy_marca_a'] = $busqueda_query;
            $score_params[':score_fuzzy_marca_b'] = $busqueda_query;
            $score_params[':score_fuzzy_categoria_a'] = $busqueda_query;
            $score_params[':score_fuzzy_categoria_b'] = $busqueda_query;
        }

        $score_parts[] = "CASE WHEN COALESCE(p.stock_actual_global,0) > 0 THEN 8 ELSE 0 END";
        $score_parts[] = "CASE WHEN COALESCE(p.descuento_porcentaje,0) > 0 THEN 5 ELSE 0 END";

        $search_score_sql = "(\n            " . implode(" +\n            ", $score_parts) . "\n        ) AS search_score";
        $order_sql = "search_score DESC, p.created_at DESC";
        $execute_params = array_merge($score_params, $params);
    }

    $sql = "SELECT p.*, c.nombre as categoria, c.slug as categoria_slug, m.nombre as marca, pm.url as imagen_principal,
        {$search_score_sql},
        (SELECT COALESCE(AVG(calificacion),0) FROM reseñas_productos WHERE producto_id = p.id AND aprobado = TRUE) as promedio_calificacion,
        (SELECT COUNT(*) FROM reseñas_productos WHERE producto_id = p.id AND aprobado = TRUE) as total_reseñas
        FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id LEFT JOIN marcas m ON p.marca_id = m.id
        LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.tipo = 'FOTO' AND pm.orden = 1
        WHERE $where_sql ORDER BY {$order_sql} $limit";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($execute_params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        /*
         * Fallback estable para producción:
         * Si la búsqueda avanzada falla por diferencias de driver PDO/PostgreSQL
         * (parámetros nombrados, pg_trgm o funciones de similitud), no rompemos la web.
         * Se ejecuta una búsqueda simple, insensible a mayúsculas/minúsculas,
         * usando parámetros posicionales.
         */
        error_log('ElectroMax búsqueda avanzada falló: ' . $e->getMessage());

        if (empty($busqueda_activa)) {
            throw $e;
        }

        $fallback_terms = [];
        $fallback_terms[] = $busqueda_query;
        foreach ($busqueda_tokens as $token) {
            if ($token !== '' && !in_array($token, $fallback_terms, true)) {
                $fallback_terms[] = $token;
            }
        }
        $fallback_terms = array_slice($fallback_terms, 0, 8);

        $fallback_where = ["p.deleted_at IS NULL", "p.is_active = TRUE"];
        $fallback_params = [];
        $fallback_search_parts = [];

        foreach ($fallback_terms as $term) {
            $like = '%' . $term . '%';
            $prefix = $term . '%';
            $fallback_search_parts[] = "(
                p.nombre ILIKE ?
                OR p.nombre ILIKE ?
                OR COALESCE(p.descripcion_corta,'') ILIKE ?
                OR COALESCE(p.sku,'') ILIKE ?
                OR COALESCE(m.nombre,'') ILIKE ?
                OR COALESCE(c.nombre,'') ILIKE ?
            )";
            array_push($fallback_params, $like, $prefix, $like, $like, $like, $like);
        }

        if (!empty($fallback_search_parts)) {
            $fallback_where[] = '(' . implode(' OR ', $fallback_search_parts) . ')';
        }

        $rank_prefix = $busqueda_query . '%';
        $rank_like = '%' . $busqueda_query . '%';

        $fallback_sql = "SELECT p.*, c.nombre as categoria, c.slug as categoria_slug, m.nombre as marca, pm.url as imagen_principal,
            0 AS search_score,
            (SELECT COALESCE(AVG(calificacion),0) FROM \"reseñas_productos\" WHERE producto_id = p.id AND aprobado = TRUE) as promedio_calificacion,
            (SELECT COUNT(*) FROM \"reseñas_productos\" WHERE producto_id = p.id AND aprobado = TRUE) as total_reseñas
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN marcas m ON p.marca_id = m.id
            LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.tipo = 'FOTO' AND pm.orden = 1
            WHERE " . implode(' AND ', $fallback_where) . "
            ORDER BY
                CASE WHEN p.nombre ILIKE ? THEN 0 ELSE 1 END,
                CASE WHEN p.nombre ILIKE ? THEN 0 ELSE 1 END,
                p.nombre ASC
            LIMIT 48";

        $fallback_params[] = $rank_prefix;
        $fallback_params[] = $rank_like;

        $stmt = $pdo->prepare($fallback_sql);
        $stmt->execute($fallback_params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function getCategoryIcon($n) {
    $n = strtolower($n);
    $m = ['refrigerador'=>'fa-snowflake','heladera'=>'fa-snowflake','nevera'=>'fa-snowflake','televisor'=>'fa-tv','tv'=>'fa-tv','lavadora'=>'fa-water','lavarropas'=>'fa-water','microondas'=>'fa-bolt','horno'=>'fa-fire','cocina'=>'fa-utensils','computadora'=>'fa-laptop','laptop'=>'fa-laptop','monitor'=>'fa-desktop','audio'=>'fa-headphones','sonido'=>'fa-volume-up','celular'=>'fa-mobile-screen','smartphone'=>'fa-mobile-screen','cámara'=>'fa-camera','consola'=>'fa-gamepad','gaming'=>'fa-gamepad','aire'=>'fa-temperature-low','ac'=>'fa-temperature-low','ventilador'=>'fa-fan','cafetera'=>'fa-mug-hot','impresora'=>'fa-print','tablet'=>'fa-tablet-screen-button','accesorio'=>'fa-puzzle-piece'];
    foreach ($m as $k=>$i) { if (strpos($n,$k)!==false) return $i; } return 'fa-microchip';
}
function getCategoryAccent($n) {
    $n = strtolower($n);
    $m = ['refrigerador'=>'blue','heladera'=>'blue','televisor'=>'indigo','tv'=>'indigo','lavadora'=>'cyan','microondas'=>'orange','horno'=>'red','cocina'=>'red','computadora'=>'purple','laptop'=>'purple','monitor'=>'slate','audio'=>'pink','celular'=>'emerald','smartphone'=>'emerald','cámara'=>'amber','consola'=>'violet','aire'=>'sky','ac'=>'sky'];
    foreach ($m as $k=>$c) { if (strpos($n,$k)!==false) return $c; } return 'slate';
}

$productos_json = json_encode($productos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
$productos_best_json = json_encode($productos_best, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
$categoria_id_json = json_encode($categoria_actual_id);
$wishlist_ids_json = json_encode($wishlist_ids);
$best_chunks_count = !empty($productos_best) ? count(array_chunk($productos_best, 4)) : 1;

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/frontend/index_view.php
require EMX_VIEWS_PATH . '/frontend/index_view.php';
exit;
