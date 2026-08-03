<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_logistica.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

/**
 * ============================================================================
 * FUNCIONES DE LOGÍSTICA INTELIGENTE - ElectroMax
 * ============================================================================
 * Este archivo contiene las funciones para:
 * - Calcular distancias reales entre coordenadas (Fórmula Haversine)
 * - Asignar la sucursal más cercana con stock disponible
 * - Calcular tiempos estimados de entrega
 * ============================================================================
 */

/**
 * Calcula la distancia en KM entre dos coordenadas geográficas
 * Usa la fórmula Haversine para precisión en la esfera terrestre
 * 
 * @param float $lat1 Latitud del punto 1
 * @param float $lon1 Longitud del punto 1
 * @param float $lat2 Latitud del punto 2
 * @param float $lon2 Longitud del punto 2
 * @return float Distancia en kilómetros (redondeada a 2 decimales)
 */
function calcularDistanciaReal($lat1, $lon1, $lat2, $lon2) {
    $radio_tierra = 6371; // Radio de la tierra en KM
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return round($radio_tierra * $c, 2);
}

/**
 * ENRUTAMIENTO INTELIGENTE: Busca la sucursal más cercana que tenga TODO el stock
 * 
 * Lógica:
 * 1. Obtiene todas las sucursales activas
 * 2. Calcula la distancia de cada una al cliente
 * 3. Las ordena de la más cercana a la más lejana
 * 4. Verifica que la sucursal tenga stock de TODOS los productos del carrito
 * 5. Retorna la primera que cumpla (la más cercana con stock completo)
 * 6. Si ninguna tiene todo, usa la Matriz como fallback
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @param array $carrito Array de productos del carrito
 * @param float $lat_cliente Latitud del cliente
 * @param float $lon_cliente Longitud del cliente
 * @return array Información de la sucursal asignada
 */
function asignarSucursalInteligente($pdo, $carrito, $lat_cliente, $lon_cliente) {
    // 1. Obtener todas las sucursales activas
    $sucursales = $pdo->query("SELECT id, nombre, ciudad, latitud, longitud FROM sucursales WHERE is_active = TRUE")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($sucursales)) {
        throw new Exception('No hay sucursales disponibles para procesar el pedido.');
    }
    
    // 2. Calcular distancia de cada sucursal al cliente
    foreach ($sucursales as &$s) {
        $s['distancia'] = calcularDistanciaReal($lat_cliente, $lon_cliente, $s['latitud'], $s['longitud']);
    }
    unset($s);
    
    // 3. Ordenar de la más cercana a la más lejana
    usort($sucursales, fn($a, $b) =>$a['distancia'] <=>$b['distancia']);

    // 4. Buscar la primera sucursal que tenga stock de TODOS los productos
    foreach ($sucursales as $sucursal) {
        $tiene_todo_el_stock = true;
        $productos_sin_stock = [];
        
        foreach ($carrito as $item) {
            $stmt = $pdo->prepare("SELECT stock FROM inventario_sucursal WHERE sucursal_id = ? AND producto_id = ?");
            $stmt->execute([$sucursal['id'], $item['producto_id']]);
            $stock = (int)($stmt->fetchColumn() ?: 0);
            
            if ($stock < $item['cantidad']) {
                $tiene_todo_el_stock = false;
                $productos_sin_stock[] = $item['nombre'];
                break; // Esta sucursal no sirve, probar la siguiente
            }
        }
        
        // ¡ENCONTRADA! Es la más cercana que tiene todo el stock
        if ($tiene_todo_el_stock) {
            return [
                'sucursal_id' =>$sucursal['id'],
                'sucursal_nombre' =>$sucursal['nombre'],
                'sucursal_ciudad' =>$sucursal['ciudad'],
                'distancia_km' =>$sucursal['distancia'],
                'es_split' =>false,
                'productos_faltantes' =>[]
            ];
        }
    }

    // 5. FALLBACK: Si ninguna sucursal tiene todo, usar la Matriz
    $matriz = $pdo->query("SELECT id, nombre, ciudad, latitud, longitud FROM sucursales WHERE es_matriz = TRUE LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if (!$matriz) {
        throw new Exception('No se encontró una sucursal matriz para procesar el pedido.');
    }
    
    $distancia_matriz = calcularDistanciaReal($lat_cliente, $lon_cliente, $matriz['latitud'], $matriz['longitud']);
    
    return [
        'sucursal_id' =>$matriz['id'],
        'sucursal_nombre' =>$matriz['nombre'] . ' (Stock consolidado)',
        'sucursal_ciudad' =>$matriz['ciudad'],
        'distancia_km' =>$distancia_matriz,
        'es_split' =>true,
        'productos_faltantes' =>$productos_sin_stock ?? []
    ];
}

/**
 * Calcula el tiempo estimado de entrega basado en la distancia real
 * 
 * Reglas de negocio:
 * - Hasta 30 km: Entrega Express (24 horas)
 * - Hasta 150 km: Entrega Provincial (1-2 días hábiles)
 * - Hasta 400 km: Entrega Interprovincial (3-4 días hábiles)
 * - Más de 400 km: Entrega Nacional (5-6 días hábiles)
 * 
 * @param float $distancia_km Distancia en kilómetros
 * @return array Fecha estimada y mensaje logístico
 */
function calcularTiempoEstimado($distancia_km) {
    $ahora = new DateTime();
    
    if ($distancia_km <= 30) {
        $ahora->modify('+1 day');
        $mensaje = "Entrega Express (24 horas)";
        $dias_habiles = 1;
    } elseif ($distancia_km <= 150) {
        $ahora->modify('+2 days');
        $mensaje = "Entrega Provincial (1-2 días hábiles)";
        $dias_habiles = 2;
    } elseif ($distancia_km <= 400) {
        $ahora->modify('+4 days');
        $mensaje = "Entrega Interprovincial (3-4 días hábiles)";
        $dias_habiles = 4;
    } else {
        $ahora->modify('+6 days');
        $mensaje = "Entrega Nacional (5-6 días hábiles)";
        $dias_habiles = 6;
    }
    
    return [
        'fecha_estimada' =>$ahora->format('Y-m-d H:i:s'),
        'mensaje_logistico' =>$mensaje,
        'dias_habiles' =>$dias_habiles
    ];
}

/**
 * Genera un número de guía único para el pedido
 * Formato: EMX-XXXXXXXX (8 caracteres alfanuméricos)
 * 
 * @return string Número de guía generado
 */
function generarNumeroGuia() {
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $codigo = '';
    for ($i = 0; $i < 8; $i++) {
        $codigo .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }
    return 'EMX-' . $codigo;
}
?>