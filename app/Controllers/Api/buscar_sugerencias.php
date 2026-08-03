<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';

header('Content-Type: application/json; charset=UTF-8');

function emxBuscarEscapeLike($value) {
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], (string)$value);
}

function emxBuscarLike($q) {
    $q = trim((string)$q);
    $q = preg_replace('/\s+/', ' ', $q);
    return '%' . emxBuscarEscapeLike($q) . '%';
}

function emxBuscarImg($url) {
    $url = trim((string)$url);
    return $url !== '' ? $url : 'assets/placeholder-product.png';
}

function emxBuscarTokens($q) {
    $tokens = [];
    foreach (preg_split('/\s+/u', mb_strtolower((string)$q)) as $token) {
        $token = trim($token);
        if (mb_strlen($token) >= 2 && !in_array($token, $tokens, true)) {
            $tokens[] = $token;
        }
    }
    return array_slice($tokens, 0, 5);
}

$q = trim((string)($_GET['q'] ?? ''));
$q = preg_replace('/\s+/', ' ', $q);

if ($q === '' || mb_strlen($q) < 2) {
    echo json_encode([
        'ok' => true,
        'query' => $q,
        'productos' => [],
        'categorias' => [],
        'marcas' => [],
        'sugerencias' => [],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$qLimit = mb_substr($q, 0, 80);
$like = emxBuscarLike($qLimit);
$prefix = emxBuscarEscapeLike($qLimit) . '%';
$tokens = emxBuscarTokens($qLimit);

try {
    $productos = [];
    $categorias = [];
    $marcas = [];

    $productoParams = [
        ':score_exact_nombre' => $qLimit,
        ':score_prefix_nombre' => $prefix,
        ':score_exact_sku' => $qLimit,
        ':score_marca' => $like,
        ':score_categoria' => $like,
        ':score_descripcion' => $like,
        ':match_nombre' => $like,
        ':match_descripcion' => $like,
        ':match_sku' => $like,
        ':match_marca' => $like,
        ':match_categoria' => $like,
    ];

    $productoMatchParts = ["(
        p.nombre ILIKE :match_nombre ESCAPE '\\'
        OR COALESCE(p.descripcion_corta,'') ILIKE :match_descripcion ESCAPE '\\'
        OR COALESCE(p.sku,'') ILIKE :match_sku ESCAPE '\\'
        OR COALESCE(m.nombre,'') ILIKE :match_marca ESCAPE '\\'
        OR COALESCE(c.nombre,'') ILIKE :match_categoria ESCAPE '\\'
    )"];

    $productoScoreParts = [
        "CASE WHEN LOWER(p.nombre) = LOWER(:score_exact_nombre) THEN 120 ELSE 0 END",
        "CASE WHEN LOWER(p.nombre) LIKE LOWER(:score_prefix_nombre) ESCAPE '\\' THEN 90 ELSE 0 END",
        "CASE WHEN LOWER(COALESCE(p.sku,'')) = LOWER(:score_exact_sku) THEN 85 ELSE 0 END",
        "CASE WHEN LOWER(COALESCE(m.nombre,'')) LIKE LOWER(:score_marca) ESCAPE '\\' THEN 45 ELSE 0 END",
        "CASE WHEN LOWER(COALESCE(c.nombre,'')) LIKE LOWER(:score_categoria) ESCAPE '\\' THEN 35 ELSE 0 END",
        "CASE WHEN LOWER(COALESCE(p.descripcion_corta,'')) LIKE LOWER(:score_descripcion) ESCAPE '\\' THEN 15 ELSE 0 END",
    ];

    foreach ($tokens as $i => $token) {
        $tokenLike = '%' . emxBuscarEscapeLike($token) . '%';
        $productoMatchParts[] = "(
            p.nombre ILIKE :tok_{$i}_nombre ESCAPE '\\'
            OR COALESCE(p.descripcion_corta,'') ILIKE :tok_{$i}_descripcion ESCAPE '\\'
            OR COALESCE(p.sku,'') ILIKE :tok_{$i}_sku ESCAPE '\\'
            OR COALESCE(m.nombre,'') ILIKE :tok_{$i}_marca ESCAPE '\\'
            OR COALESCE(c.nombre,'') ILIKE :tok_{$i}_categoria ESCAPE '\\'
        )";
        $productoParams[":tok_{$i}_nombre"] = $tokenLike;
        $productoParams[":tok_{$i}_descripcion"] = $tokenLike;
        $productoParams[":tok_{$i}_sku"] = $tokenLike;
        $productoParams[":tok_{$i}_marca"] = $tokenLike;
        $productoParams[":tok_{$i}_categoria"] = $tokenLike;

        $productoScoreParts[] = "CASE WHEN p.nombre ILIKE :score_tok_{$i}_nombre ESCAPE '\\' THEN 18 ELSE 0 END";
        $productoScoreParts[] = "CASE WHEN COALESCE(p.descripcion_corta,'') ILIKE :score_tok_{$i}_descripcion ESCAPE '\\' THEN 8 ELSE 0 END";
        $productoScoreParts[] = "CASE WHEN COALESCE(m.nombre,'') ILIKE :score_tok_{$i}_marca ESCAPE '\\' THEN 12 ELSE 0 END";
        $productoScoreParts[] = "CASE WHEN COALESCE(c.nombre,'') ILIKE :score_tok_{$i}_categoria ESCAPE '\\' THEN 10 ELSE 0 END";
        $productoParams[":score_tok_{$i}_nombre"] = $tokenLike;
        $productoParams[":score_tok_{$i}_descripcion"] = $tokenLike;
        $productoParams[":score_tok_{$i}_marca"] = $tokenLike;
        $productoParams[":score_tok_{$i}_categoria"] = $tokenLike;
    }

    $productoScoreParts[] = "CASE WHEN COALESCE(p.stock_actual_global,0) > 0 THEN 8 ELSE 0 END";
    $productoScoreParts[] = "CASE WHEN COALESCE(p.descuento_porcentaje,0) > 0 THEN 5 ELSE 0 END";

    $sqlProductos = "
        SELECT
            p.id,
            p.nombre,
            COALESCE(p.slug,'') AS slug,
            COALESCE(p.sku,'') AS sku,
            COALESCE(c.nombre,'') AS categoria,
            COALESCE(m.nombre,'') AS marca,
            COALESCE(pm.url,'') AS imagen,
            COALESCE(p.precio_base,0) AS precio_base,
            COALESCE(p.descuento_porcentaje,0) AS descuento_porcentaje,
            (" . implode(" +\n                ", $productoScoreParts) . ") AS score
        FROM productos p
        LEFT JOIN categorias c ON c.id = p.categoria_id
        LEFT JOIN marcas m ON m.id = p.marca_id
        LEFT JOIN producto_multimedia pm ON pm.producto_id = p.id AND pm.tipo='FOTO' AND pm.orden = 1
        WHERE p.deleted_at IS NULL
          AND COALESCE(p.is_active,true)=true
          AND (" . implode(" OR\n               ", $productoMatchParts) . ")
        ORDER BY score DESC, p.created_at DESC
        LIMIT 8
    ";
    $st = $pdo->prepare($sqlProductos);
    $st->execute($productoParams);
    $productos = array_map(function($r) {
        return [
            'id' => $r['id'],
            'nombre' => $r['nombre'],
            'sku' => $r['sku'],
            'categoria' => $r['categoria'],
            'marca' => $r['marca'],
            'imagen' => emxBuscarImg($r['imagen']),
            'precio_base' => (float)$r['precio_base'],
            'descuento_porcentaje' => (float)$r['descuento_porcentaje'],
            'url' => 'producto.php?id=' . urlencode((string)$r['id']),
        ];
    }, $st->fetchAll(PDO::FETCH_ASSOC));

    $catParams = [
        ':cat_prefix' => $prefix,
        ':cat_match' => $like,
    ];
    $catMatchParts = ["nombre ILIKE :cat_match ESCAPE '\\'"];
    $catScoreParts = ["CASE WHEN LOWER(nombre) LIKE LOWER(:cat_prefix) ESCAPE '\\' THEN 0 ELSE 1 END"];
    foreach ($tokens as $i => $token) {
        $tokenLike = '%' . emxBuscarEscapeLike($token) . '%';
        $catMatchParts[] = "nombre ILIKE :cat_tok_{$i} ESCAPE '\\'";
        $catParams[":cat_tok_{$i}"] = $tokenLike;
    }
    $stCat = $pdo->prepare("
        SELECT nombre, slug, (" . implode(' + ', $catScoreParts) . ") AS score
        FROM categorias
        WHERE COALESCE(is_active,true)=true
          AND (" . implode(' OR ', $catMatchParts) . ")
        ORDER BY score ASC, nombre ASC
        LIMIT 5
    ");
    $stCat->execute($catParams);
    $categorias = array_map(function($r) {
        return [
            'nombre' => $r['nombre'],
            'slug' => $r['slug'],
            'url' => 'index.php?categoria=' . urlencode((string)$r['slug'])
        ];
    }, $stCat->fetchAll(PDO::FETCH_ASSOC));

    $marcaParams = [
        ':marca_prefix' => $prefix,
        ':marca_match' => $like,
    ];
    $marcaMatchParts = ["nombre ILIKE :marca_match ESCAPE '\\'"];
    $marcaScoreParts = ["CASE WHEN LOWER(nombre) LIKE LOWER(:marca_prefix) ESCAPE '\\' THEN 0 ELSE 1 END"];
    foreach ($tokens as $i => $token) {
        $tokenLike = '%' . emxBuscarEscapeLike($token) . '%';
        $marcaMatchParts[] = "nombre ILIKE :marca_tok_{$i} ESCAPE '\\'";
        $marcaParams[":marca_tok_{$i}"] = $tokenLike;
    }
    $stMarca = $pdo->prepare("
        SELECT nombre, (" . implode(' + ', $marcaScoreParts) . ") AS score
        FROM marcas
        WHERE " . implode(' OR ', $marcaMatchParts) . "
        ORDER BY score ASC, nombre ASC
        LIMIT 5
    ");
    $stMarca->execute($marcaParams);
    $marcas = array_map(function($r) {
        return [
            'nombre' => $r['nombre'],
            'url' => 'index.php?q=' . urlencode((string)$r['nombre'])
        ];
    }, $stMarca->fetchAll(PDO::FETCH_ASSOC));

    $sugerencias = [];
    foreach ($productos as $p) $sugerencias[] = $p['nombre'];
    foreach ($categorias as $c) $sugerencias[] = $c['nombre'];
    foreach ($marcas as $m) $sugerencias[] = $m['nombre'];
    $sugerencias = array_values(array_unique(array_slice($sugerencias, 0, 10)));

    echo json_encode([
        'ok' => true,
        'query' => $qLimit,
        'productos' => $productos,
        'categorias' => $categorias,
        'marcas' => $marcas,
        'sugerencias' => $sugerencias,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('[buscar_sugerencias] ' . $e->getMessage());
    echo json_encode([
        'ok' => false,
        'query' => $qLimit,
        'error' => 'No se pudieron cargar sugerencias.',
        'productos' => [],
        'categorias' => [],
        'marcas' => [],
        'sugerencias' => [],
    ], JSON_UNESCAPED_UNICODE);
}
