<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_planes.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

/**
 * Funciones para manejar beneficios de planes de membresía
 */

// 1. Obtener el plan activo del usuario
function obtenerPlanActivoUsuario($pdo, $usuario_id) {
    $stmt = $pdo->prepare("
        SELECT p.*, u.plan_expira_en 
        FROM planes p 
        JOIN usuarios u ON p.id = u.plan_id 
        WHERE u.id = ? AND u.plan_id IS NOT NULL 
        AND (u.plan_expira_en IS NULL OR u.plan_expira_en >NOW())
        AND p.is_active = TRUE
    ");
    $stmt->execute([$usuario_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 2. Obtener los beneficios estructurados del usuario
function obtenerBeneficiosUsuario($pdo, $usuario_id) {
    $plan = obtenerPlanActivoUsuario($pdo, $usuario_id);
    if (!$plan || empty($plan['beneficios'])) {
        return [];
    }
    
    $beneficios = json_decode($plan['beneficios'], true);
    if (!is_array($beneficios)) {
        return [];
    }
    
    // Filtrar solo beneficios estructurados válidos
    $beneficios_validos = [];
    foreach ($beneficios as $b) {
        if (isset($b['tipo']) && !empty($b['tipo'])) {
            $beneficios_validos[] = $b;
        }
    }
    
    return $beneficios_validos;
}

// 3. Aplicar descuento de plan a un precio
function aplicarDescuentoPlan($precio_original, $beneficios) {
    $precio_final = $precio_original;
    foreach ($beneficios as $b) {
        if ($b['tipo'] === 'descuento_producto') {
            if ($b['unidad'] === 'porcentaje') {
                $descuento = floatval($b['valor']);
                $precio_final = $precio_original * (1 - ($descuento / 100));
            } elseif ($b['unidad'] === 'monto') {
                $descuento = floatval($b['valor']);
                $precio_final = max(0, $precio_original - $descuento);
            }
            break; // Asumimos un solo descuento de precio por plan
        }
    }
    return $precio_final;
}

// 4. Verificar si el usuario tiene un beneficio específico (ej: acceso_anticipado)
function tieneBeneficio($beneficios, $tipo_beneficio) {
    foreach ($beneficios as $b) {
        if ($b['tipo'] === $tipo_beneficio) {
            return true;
        }
    }
    return false;
}
?>