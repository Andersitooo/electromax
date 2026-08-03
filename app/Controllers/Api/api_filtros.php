<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
header('Content-Type: application/json');

$categoria_id = $_GET['categoria_id'] ?? '';

if (empty($categoria_id)) {
    echo json_encode(['success' =>false, 'message' =>'Categoría no especificada']);
    exit();
}

try {
    // Obtener productos y sus especificaciones
    $stmt = $pdo->prepare("
        SELECT p.id, p.especificaciones_tecnicas, p.precio_base, p.iva_porcentaje
        FROM productos p
        WHERE p.categoria_id = ? 
        AND p.deleted_at IS NULL 
        AND p.is_active = TRUE
    ");
    $stmt->execute([$categoria_id]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular precio mínimo y máximo REAL
    $min_price = null;
    $max_price = null;

    foreach ($productos as $prod) {
        $iva = $prod['iva_porcentaje'] ?? 15;
        $precio_con_iva = $prod['precio_base'] * (1 + ($iva / 100));
        
        if ($min_price === null || $precio_con_iva < $min_price) $min_price = $precio_con_iva;
        if ($max_price === null || $precio_con_iva >$max_price) $max_price = $precio_con_iva;
    }

    // Extraer especificaciones únicas
    $filtros = [];
    foreach ($productos as $prod) {
        $specs = json_decode($prod['especificaciones_tecnicas'], true);
        
        if (is_array($specs)) {
            foreach ($specs as $key =>$value) {
                $label = ucwords(str_replace('_', ' ', $key));
                
                if (!isset($filtros[$key])) {
                    $filtros[$key] = ['label' =>$label, 'valores' =>[]];
                }
                
                if (is_array($value)) {
                    foreach ($value as $v) {
                        if (!in_array($v, $filtros[$key]['valores'])) {
                            $filtros[$key]['valores'][] = $v;
                        }
                    }
                } else {
                    if (!in_array($value, $filtros[$key]['valores'])) {
                        $filtros[$key]['valores'][] = $value;
                    }
                }
            }
        }
    }

    // Ordenar valores alfabéticamente
    foreach ($filtros as &$filtro) {
        sort($filtro['valores']);
    }

    echo json_encode([
        'success' =>true,
        'filtros' =>array_values($filtros),
        'min_price' =>$min_price !== null ? round($min_price, 2) : 0,
        'max_price' =>$max_price !== null ? round($max_price, 2) : 1000,
        'total_productos' =>count($productos)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' =>false,
        'message' =>'Error: ' . $e->getMessage()
    ]);
}
?>