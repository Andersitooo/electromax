<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
header('Content-Type: application/json');

$categoria_id = $_GET['categoria_id'] ?? '';
$filtros = json_decode($_GET['filtros'] ?? '{}', true);
$calificacion_exacta = (int)($_GET['calificacion_exacta'] ?? ($_GET['calificacion_min'] ?? 0));
if ($calificacion_exacta < 0 || $calificacion_exacta > 5) {
    $calificacion_exacta = 0;
}
$precio_min = (float)($_GET['precio_min'] ?? 0);
$precio_max = (float)($_GET['precio_max'] ?? 999999);

if (empty($categoria_id)) {
    echo json_encode(['success' =>false, 'message' =>'Sin categoría']);
    exit();
}

try {
    // Query base - usando subconsulta para calcular calificación primero
    $query = "
        WITH producto_stats AS (
            SELECT 
                p.id,
                p.nombre,
                p.precio_base,
                p.iva_porcentaje,
                p.descuento_porcentaje,
                p.descuento_desde,
                p.descuento_hasta,
                p.stock_actual_global,
                p.marca_id,
                p.categoria_id,
                COALESCE(AVG(r.calificacion), 0) as calificacion_promedio,
                COUNT(r.id) as total_reseñas
            FROM productos p
            LEFT JOIN reseñas_productos r ON p.id = r.producto_id AND r.aprobado = TRUE
            WHERE p.categoria_id = ?
            AND p.deleted_at IS NULL
            AND p.is_active = TRUE
            GROUP BY p.id
        )
        SELECT 
            ps.*,
            c.nombre as categoria,
            m.nombre as marca,
            pm.url as imagen_principal
        FROM producto_stats ps
        LEFT JOIN categorias c ON ps.categoria_id = c.id
        LEFT JOIN marcas m ON ps.marca_id = m.id
        LEFT JOIN producto_multimedia pm ON ps.id = pm.producto_id AND pm.orden = 1
        WHERE 1=1
    ";

    $params = [$categoria_id];

    // Filtro por Precio (con IVA)
    $query .= " AND (ps.precio_base * (1 + COALESCE(ps.iva_porcentaje, 15)/100)) BETWEEN ? AND ?";
    $params[] = $precio_min;
    $params[] = $precio_max;

    // Filtro por calificación exacta por bloque de estrellas.
    // 4 estrellas = promedio >= 4 y < 5. Ya no significa "4 o más".
    if ($calificacion_exacta > 0) {
        if ($calificacion_exacta >= 5) {
            $query .= " AND ps.calificacion_promedio >= 5";
        } else {
            $query .= " AND ps.calificacion_promedio >= ? AND ps.calificacion_promedio < ?";
            $params[] = $calificacion_exacta;
            $params[] = $calificacion_exacta + 1;
        }
    }

    // Filtros por Especificaciones (JSONB)
    if (!empty($filtros)) {
        $where_clauses = [];
        foreach ($filtros as $key =>$valores) {
            if (!empty($valores)) {
                $or_conditions = [];
                foreach ($valores as $valor) {
                    $or_conditions[] = "(ps.especificaciones_tecnicas->>? = ? OR ps.especificaciones_tecnicas->? ? ?)";
                    $params[] = $key;
                    $params[] = $valor;
                    $params[] = $key;
                    $params[] = $valor;
                }
                $where_clauses[] = "(" . implode(' OR ', $or_conditions) . ")";
            }
        }
        
        if (!empty($where_clauses)) {
            $query .= " AND (" . implode(' AND ', $where_clauses) . ")";
        }
    }

    $query .= " ORDER BY ps.calificacion_promedio DESC, ps.id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' =>true,
        'productos' =>$productos,
        'total' =>count($productos),
        'debug' =>[
            'calificacion_exacta' =>$calificacion_exacta,
            'precio_min' =>$precio_min,
            'precio_max' =>$precio_max,
            'filtros' =>$filtros
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' =>false,
        'message' =>'Error: ' . $e->getMessage()
    ]);
}
?>