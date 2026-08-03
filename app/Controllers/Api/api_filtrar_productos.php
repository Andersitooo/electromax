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
    $especificaciones_where = [];
    $especificaciones_params = [];
    
    // Construir condiciones de especificaciones de forma segura
    if (!empty($filtros)) {
        foreach ($filtros as $key =>$valores) {
            if (!empty($valores)) {
                $or_conditions = [];
                foreach ($valores as $valor) {
                    // 1. Coincidencia exacta de texto (funciona perfecto para strings y números)
                    $or_conditions[] = "p.especificaciones_tecnicas->>? = ?";
                    $especificaciones_params[] = $key;
                    $especificaciones_params[] = $valor;
                    
                    // 2. Contención en array JSON (funciona si el valor está dentro de ["Negro", "Blanco"])
                    $or_conditions[] = "p.especificaciones_tecnicas->? @>?::jsonb";
                    $especificaciones_params[] = $key;
                    $especificaciones_params[] = json_encode($valor);
                }
                // Unimos con OR para que coincida con cualquiera de los valores seleccionados de esa clave
                $especificaciones_where[] = "(" . implode(' OR ', $or_conditions) . ")";
            }
        }
    }
    
    // Consulta principal optimizada
    $query = "
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
            p.especificaciones_tecnicas,
            c.nombre as categoria,
            m.nombre as marca,
            pm.url as imagen_principal,
            COALESCE(avg_stats.promedio_calificacion, 0) as promedio_calificacion,
            COALESCE(avg_stats.total_reseñas, 0) as total_reseñas
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN marcas m ON p.marca_id = m.id
        LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.orden = 1
        LEFT JOIN (
            SELECT 
                producto_id,
                AVG(calificacion) as promedio_calificacion,
                COUNT(*) as total_reseñas
            FROM reseñas_productos
            WHERE aprobado = TRUE
            GROUP BY producto_id
        ) avg_stats ON p.id = avg_stats.producto_id
        WHERE p.categoria_id = ?
        AND p.deleted_at IS NULL
        AND p.is_active = TRUE
        AND (p.precio_base * (1 + COALESCE(p.iva_porcentaje, 15)/100)) BETWEEN ? AND ?
    ";
    
    $params = [$categoria_id, $precio_min, $precio_max];
    
    // Agregar filtros de especificaciones (solo si existen)
    if (!empty($especificaciones_where)) {
        $query .= " AND (" . implode(' AND ', $especificaciones_where) . ")";
        $params = array_merge($params, $especificaciones_params);
    }
    
    // Agregar filtro de calificación exacta por bloque de estrellas.
    // Ejemplo:
    // 1 = promedio desde 1.0 hasta menor que 2.0
    // 4 = promedio desde 4.0 hasta menor que 5.0
    // 5 = promedio 5.0
    if ($calificacion_exacta > 0) {
        if ($calificacion_exacta >= 5) {
            $query .= " AND COALESCE(avg_stats.promedio_calificacion, 0) >= 5";
        } else {
            $query .= " AND COALESCE(avg_stats.promedio_calificacion, 0) >= ? AND COALESCE(avg_stats.promedio_calificacion, 0) < ?";
            $params[] = $calificacion_exacta;
            $params[] = $calificacion_exacta + 1;
        }
    }
    
    $query .= " ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolvemos también el 'debug' para que puedas ver en la consola del navegador exactamente qué se está ejecutando
    echo json_encode([
        'success' =>true,
        'productos' =>$productos,
        'total' =>count($productos),
        'debug' =>[
            'query_ejecutada' =>$query,
            'parametros' =>$params,
            'calificacion_exacta_aplicada' =>$calificacion_exacta,
            'filtros_recibidos' =>$filtros
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' =>false,
        'message' =>'Error de Base de Datos: ' . $e->getMessage(),
        'trace' =>$e->getTraceAsString()
    ]);
}
?>