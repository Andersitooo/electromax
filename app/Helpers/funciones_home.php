<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_home.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

/**
 * Funciones para Home modular, banners dinámicos, más vendidos y recomendaciones.
 * No requiere columnas nuevas: usa page_sections.posicion/tipo para distribuir banners.
 */

if (!function_exists('emxHtml')) {
    function emxHtml($valor): string {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

function emxNormalizarTextoHome($texto): string {
    $texto = mb_strtolower((string)$texto, 'UTF-8');
    $map = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n'];
    return strtr($texto, $map);
}

function emxObtenerSeccionesHome(PDO $pdo): array {
    $resultado = [
        'hero_principal' =>[],
        'despues_categorias' =>[],
        'entre_productos' =>[],
        'despues_mas_vendidos' =>[],
        'antes_footer' =>[],
    ];

    try {
        $stmt = $pdo->query("SELECT * FROM page_sections WHERE is_active = TRUE ORDER BY posicion ASC, created_at ASC");
        $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return $resultado;
    }

    $slots = ['despues_categorias', 'entre_productos', 'despues_mas_vendidos', 'antes_footer'];
    $slotIndex = 0;
    $heroAsignado = false;

    foreach ($secciones as $section) {
        $nombre = emxNormalizarTextoHome($section['nombre'] ?? '');
        $tipo = emxNormalizarTextoHome($section['tipo'] ?? '');

        // Hero: se mantiene arriba. Solo el primer carrusel/principal se trata como hero.
        if (!$heroAsignado && (str_contains($nombre, 'hero') || str_contains($nombre, 'principal') || $tipo === 'carousel')) {
            $resultado['hero_principal'][] = $section;
            $heroAsignado = true;
            continue;
        }

        // Mapeo por nombre, para que desde Admin puedas mover secciones renombrándolas/ordenándolas.
        if (str_contains($nombre, 'categoria')) {
            $resultado['despues_categorias'][] = $section;
            continue;
        }
        if (str_contains($nombre, 'vendido') || str_contains($nombre, 'producto') || str_contains($nombre, 'destacad')) {
            $resultado['entre_productos'][] = $section;
            continue;
        }
        if (str_contains($nombre, 'oferta') || str_contains($nombre, 'campana') || str_contains($nombre, 'promocion')) {
            $resultado['despues_mas_vendidos'][] = $section;
            continue;
        }
        if (str_contains($nombre, 'footer') || str_contains($nombre, 'final') || str_contains($nombre, 'membres')) {
            $resultado['antes_footer'][] = $section;
            continue;
        }

        $slot = $slots[min($slotIndex, count($slots) - 1)];
        $resultado[$slot][] = $section;
        $slotIndex++;
    }

    return $resultado;
}

function emxBannersDeSeccion(PDO $pdo, string $sectionId): array {
    try {
        $hoy = date('Y-m-d');
        $stmt = $pdo->prepare("\n            SELECT *\n            FROM banners_promocionales\n            WHERE section_id = ?\n              AND is_active = TRUE\n              AND (fecha_inicio IS NULL OR fecha_inicio <= ?)\n              AND (fecha_fin IS NULL OR fecha_fin >= ?)\n            ORDER BY orden ASC, created_at ASC\n        ");
        $stmt->execute([$sectionId, $hoy, $hoy]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

function emxRenderBannerCard(array $banner, string $heightClass = 'h-48 md:h-64'): string {
    $titulo = emxHtml($banner['titulo'] ?? 'Promoción ElectroMax');
    $subtitulo = emxHtml($banner['subtitulo'] ?? '');
    $img = emxHtml($banner['imagen_url'] ?? '');
    $id = emxHtml($banner['id'] ?? '');
    if ($img === '' || $id === '') return '';

    ob_start(); ?><a href="banner_redirect.php?id=<?= $id ?>" class="group relative block overflow-hidden rounded-2xl shadow-sm border border-slate-200 bg-slate-900 card-hover <?= $heightClass ?>"><img src="<?= $img ?>" alt="<?= $titulo ?>" class="absolute inset-0 w-full h-full object-cover transition duration-500 group-hover:scale-105"><div class="absolute inset-0 bg-gradient-to-r from-slate-950/55 via-slate-900/15 to-transparent"></div><?php if ($titulo !== '' || $subtitulo !== ''): ?><div class="absolute left-5 bottom-5 right-5 text-white drop-shadow"><?php if ($titulo !== ''): ?><h3 class="text-lg md:text-xl font-extrabold leading-tight line-clamp-2"><?= $titulo ?></h3><?php endif; ?><?php if ($subtitulo !== ''): ?><p class="text-xs md:text-sm text-white/90 mt-1 line-clamp-2"><?= $subtitulo ?></p><?php endif; ?></div><?php endif; ?></a><?php return ob_get_clean();
}

function emxRenderBannerSection(array $section, array $banners): string {
    if (empty($banners)) return '';
    $tipo = $section['tipo'] ?? 'single';
    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($section['id'] ?? uniqid('sec_', true)));

    ob_start(); ?><section class="py-5 md:py-7 home-dynamic-section" data-section="<?= emxHtml($section['nombre'] ?? '') ?>"><?php if ($tipo === 'carousel'): ?><div class="relative overflow-hidden bg-slate-900 shadow-sm"><div class="flex transition-transform duration-700 ease-in-out" id="carousel-<?= $id ?>"><?php foreach ($banners as $banner): ?><div class="w-full flex-shrink-0"><a href="banner_redirect.php?id=<?= emxHtml($banner['id']) ?>" class="block relative h-[260px] md:h-[430px] lg:h-[500px] overflow-hidden"><img src="<?= emxHtml($banner['imagen_url']) ?>" alt="<?= emxHtml($banner['titulo'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover"><div class="absolute inset-0 bg-gradient-to-r from-slate-950/55 via-slate-900/15 to-transparent"></div><div class="absolute left-6 md:left-14 bottom-8 md:bottom-14 max-w-xl text-white drop-shadow"><?php if (!empty($banner['titulo'])): ?><h2 class="text-2xl md:text-5xl font-black leading-tight"><?= emxHtml($banner['titulo']) ?></h2><?php endif; ?><?php if (!empty($banner['subtitulo'])): ?><p class="mt-3 text-sm md:text-lg text-white/90"><?= emxHtml($banner['subtitulo']) ?></p><?php endif; ?></div></a></div><?php endforeach; ?></div><?php if (count($banners) >1): ?><button onclick="moveCarousel('<?= $id ?>',-1)" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition z-10"><i class="fas fa-chevron-left text-slate-700"></i></button><button onclick="moveCarousel('<?= $id ?>',1)" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition z-10"><i class="fas fa-chevron-right text-slate-700"></i></button><?php endif; ?></div><?php else: ?><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><?php if ($tipo === 'grid_2'): ?><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><?php foreach ($banners as $banner) echo emxRenderBannerCard($banner, 'h-52 md:h-72'); ?></div><?php elseif ($tipo === 'grid_3'): ?><div class="grid grid-cols-1 md:grid-cols-3 gap-4"><?php foreach ($banners as $banner) echo emxRenderBannerCard($banner, 'h-48 md:h-64'); ?></div><?php elseif ($tipo === 'grid_4'): ?><div class="grid grid-cols-2 md:grid-cols-4 gap-4"><?php foreach ($banners as $banner) echo emxRenderBannerCard($banner, 'h-40 md:h-52'); ?></div><?php else: ?><?php $banner = $banners[0]; echo emxRenderBannerCard($banner, 'h-52 md:h-80'); ?><?php endif; ?></div><?php endif; ?></section><?php return ob_get_clean();
}

function emxRenderHomeSlot(PDO $pdo, array $seccionesHome, string $slot): string {
    if (empty($seccionesHome[$slot])) return '';
    $html = '';
    foreach ($seccionesHome[$slot] as $section) {
        $banners = emxBannersDeSeccion($pdo, (string)$section['id']);
        if (!empty($banners)) {
            $html .= emxRenderBannerSection($section, $banners);
        }
    }
    return $html;
}

function emxProductoQueryBase(): string {
    return "\n        SELECT p.*, c.nombre as categoria, c.slug as categoria_slug, m.nombre as marca, pm.url as imagen_principal,\n               COALESCE(rv.promedio_calificacion, 0) as promedio_calificacion,\n               COALESCE(rv.total_resenas, 0) as total_reseñas\n        FROM productos p\n        LEFT JOIN categorias c ON p.categoria_id = c.id\n        LEFT JOIN marcas m ON p.marca_id = m.id\n        LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.tipo = 'FOTO' AND pm.orden = 1\n        LEFT JOIN (\n            SELECT producto_id, AVG(calificacion) as promedio_calificacion, COUNT(*) as total_resenas\n            FROM reseñas_productos\n            WHERE aprobado = TRUE\n            GROUP BY producto_id\n        ) rv ON rv.producto_id = p.id\n    ";
}

function emxObtenerMasVendidos(PDO $pdo, int $limit = 12): array {
    $estadosValidos = [
        'pago confirmado', 'preparando pedido', 'listo para despacho', 'en tránsito', 'en transito',
        'entregado', 'cerrado', 'completado', 'reemplazo generado', 'cambio despachado', 'cambio_despachado'
    ];
    $placeholders = implode(',', array_fill(0, count($estadosValidos), '?'));

    try {
        $sql = "\n            WITH ventas AS (\n                SELECT dp.producto_id, SUM(COALESCE(dp.cantidad, 0))::integer AS total_ventas\n                FROM detalle_pedidos dp\n                INNER JOIN pedidos ped ON ped.id = dp.pedido_id\n                WHERE LOWER(COALESCE(ped.estado, '')) IN ($placeholders)\n                GROUP BY dp.producto_id\n            )\n            SELECT base.*, COALESCE(v.total_ventas, 0) AS total_ventas\n            FROM (" . emxProductoQueryBase() . ") base\n            INNER JOIN ventas v ON v.producto_id = base.id\n            WHERE base.deleted_at IS NULL AND base.is_active = TRUE\n            ORDER BY v.total_ventas DESC, base.promedio_calificacion DESC, base.created_at DESC\n            LIMIT " . (int)$limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($estadosValidos);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

function emxObtenerRecomendadosProducto(PDO $pdo, array $producto, string $productoId, int $limit = 10): array {
    $limit = max(4, min(16, (int)$limit));
    $categoriaId = $producto['categoria_id'] ?? null;
    $marcaId = $producto['marca_id'] ?? null;
    $precio = max((float)($producto['precio_base'] ?? 0), 0);

    $resultados = [];
    $vistos = [(string)$productoId => true];

    $agregar = function(array $rows) use (&$resultados, &$vistos, $limit) {
        foreach ($rows as $row) {
            $id = (string)($row['id'] ?? '');
            if ($id === '' || isset($vistos[$id])) continue;
            $vistos[$id] = true;
            $resultados[] = $row;
            if (count($resultados) >= $limit) break;
        }
    };

    $query = function(string $where, array $params, string $order = '') use ($pdo, $limit) {
        $sql = emxProductoQueryBase() . "\n            WHERE p.deleted_at IS NULL\n              AND p.is_active = TRUE\n              " . $where . "\n            " . ($order ?: "ORDER BY p.stock_actual_global DESC, promedio_calificacion DESC, p.created_at DESC") . "\n            LIMIT " . (int)$limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    };

    try {
        // 1) Primero productos de la misma categoría. Es lo que el cliente espera en "recomendados".
        if ($categoriaId) {
            $agregar($query("AND p.id <> ? AND p.categoria_id = ?", [$productoId, $categoriaId], "ORDER BY promedio_calificacion DESC, p.stock_actual_global DESC, p.created_at DESC"));
        }

        // 2) Luego productos de la misma marca, si todavía faltan.
        if (count($resultados) < $limit && $marcaId) {
            $agregar($query("AND p.id <> ? AND p.marca_id = ?", [$productoId, $marcaId], "ORDER BY promedio_calificacion DESC, p.stock_actual_global DESC, p.created_at DESC"));
        }

        // 3) Luego productos con precio parecido.
        if (count($resultados) < $limit && $precio > 0) {
            $min = $precio * 0.70;
            $max = $precio * 1.35;
            $agregar($query("AND p.id <> ? AND p.precio_base BETWEEN ? AND ?", [$productoId, $min, $max], "ORDER BY ABS(p.precio_base - " . (float)$precio . ") ASC, promedio_calificacion DESC, p.created_at DESC"));
        }

        // 4) Fallback final: cualquier producto activo para que la sección nunca quede vacía si existe catálogo.
        if (count($resultados) < $limit) {
            $agregar($query("AND p.id <> ?", [$productoId], "ORDER BY p.stock_actual_global DESC, promedio_calificacion DESC, p.created_at DESC"));
        }
    } catch (Throwable $e) {
        try {
            $stmt = $pdo->prepare("\n                SELECT p.*, c.nombre as categoria, c.slug as categoria_slug, m.nombre as marca, pm.url as imagen_principal,\n                       COALESCE(p.calificacion_promedio, 0) as promedio_calificacion,\n                       0 as total_reseñas\n                FROM productos p\n                LEFT JOIN categorias c ON p.categoria_id = c.id\n                LEFT JOIN marcas m ON p.marca_id = m.id\n                LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.tipo = 'FOTO' AND pm.orden = 1\n                WHERE p.id <> ? AND p.deleted_at IS NULL AND p.is_active = TRUE\n                ORDER BY p.created_at DESC\n                LIMIT " . (int)$limit);
            $stmt->execute([$productoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e2) {
            return [];
        }
    }

    return $resultados;
}
?>