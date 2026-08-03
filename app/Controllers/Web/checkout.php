<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

// 1. CONFIGURAR ZONA HORARIA DE ECUADOR (Babahoyo/Guayaquil)
date_default_timezone_set('America/Guayaquil');

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
emxVerificarCsrfSiPOST();
require_once EMX_HELPERS_PATH . '/funciones_logistica.php';
require_once EMX_HELPERS_PATH . '/funciones_auxiliares.php'; // ← Para generarSerieUnica()
require_once EMX_HELPERS_PATH . '/funciones_backorder.php';
require_once EMX_HELPERS_PATH . '/funciones_descuentos_volumen.php';
require_once EMX_HELPERS_PATH . '/funciones_stock.php';
require_once EMX_HELPERS_PATH . '/funciones_garantias.php';
require_once EMX_HELPERS_PATH . '/funciones_facturacion.php';

//  PROTECCIÓN
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: auth.php?action=login&msg=debes_iniciar_sesion');
    exit();
}

if (empty($_SESSION['carrito'])) {
    header('Location: index.php');
    exit();
}

$action = $_POST['action'] ?? null;
$pedido_exitoso = null;
$error_msg = null;


// Normaliza carritos antiguos que pudieron quedarse en sesión sin producto_id.
function emxCheckoutProductoId($item, $fallbackKey = null) {
    if (is_array($item)) {
        if (!empty($item['producto_id'])) return (string)$item['producto_id'];
        if (!empty($item['id'])) return (string)$item['id'];
    }
    if (is_string($fallbackKey) && preg_match('/^[a-f0-9-]{36}$/i', $fallbackKey)) return $fallbackKey;
    return null;
}

function emxCheckoutNormalizarCarritoSesion() {
    if (empty($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) return;
    foreach ($_SESSION['carrito'] as $key =>&$item) {
        if (!is_array($item)) { unset($_SESSION['carrito'][$key]); continue; }
        $pid = emxCheckoutProductoId($item, $key);
        if (!$pid) { unset($_SESSION['carrito'][$key]); continue; }
        $item['producto_id'] = $pid;
        $item['cantidad'] = max(1, (int)($item['cantidad'] ?? 1));
        $item['precio_base'] = (float)($item['precio_base'] ?? 0);
        $item['descuento_aplicado'] = (float)($item['descuento_aplicado'] ?? 0);
        $item['iva'] = (float)($item['iva'] ?? 15);
    }
    unset($item);
}

function emxCheckoutInsertFlexible($pdo, $tabla, array $datos) {
    $columnas = [];
    $valores = [];
    foreach ($datos as $col =>$val) {
        if (function_exists('emxFactColumnExists') && !emxFactColumnExists($pdo, $tabla, $col)) continue;

        // PostgreSQL no acepta cadena vacía ('') para columnas booleanas.
        // Cuando PDO recibe false en execute([...]), algunas configuraciones lo envían como '',
        // por eso normalizamos booleanos a texto PostgreSQL válido.
        if (is_bool($val)) {
            $val = $val ? 'true' : 'false';
        }

        $columnas[] = $col;
        $valores[] = $val;
    }
    if (empty($columnas)) return false;
    $sql = "INSERT INTO {$tabla} (" . implode(',', $columnas) . ") VALUES (" . implode(',', array_fill(0, count($columnas), '?')) . ")";
    $st = $pdo->prepare($sql);
    return $st->execute($valores);
}

function emxGuardarDireccionCheckoutSiAplica($pdo, $usuarioId, array $post) {
    $guardar = !empty($post['guardar_direccion']) || ($post['direccion_tipo'] ?? '') === 'ubicacion_actual';
    if (!$guardar) return;
    $direccion = trim($post['direccion'] ?? '');
    $ciudad = trim($post['ciudad'] ?? '');
    $lat = trim((string)($post['latitud'] ?? ''));
    $lon = trim((string)($post['longitud'] ?? ''));
    if ($direccion === '' || $ciudad === '' || $lat === '' || $lon === '') return;
    $alias = trim($post['direccion_alias'] ?? '');
    if ($alias === '') $alias = (($post['direccion_tipo'] ?? '') === 'ubicacion_actual') ? 'Ubicación actual' : 'Dirección checkout';
    try {
        $st = $pdo->prepare("SELECT id FROM direcciones_usuario WHERE usuario_id = ? AND alias = ? LIMIT 1");
        $st->execute([$usuarioId, $alias]);
        if ($st->fetchColumn()) $alias .= ' ' . date('d/m H:i');
    } catch (Throwable $e) {}
    $datos = [
        'usuario_id' =>$usuarioId,
        'alias' =>$alias,
        'direccion' =>$direccion,
        'ciudad' =>$ciudad,
        'codigo_postal' =>trim($post['codigo_postal'] ?? ''),
        'telefono' =>trim($post['telefono'] ?? ''),
        'latitud' =>(float)$lat,
        'longitud' =>(float)$lon,
        'es_principal' =>'false',
        'provincia_id' =>!empty($post['provincia_id']) ? (int)$post['provincia_id'] : null,
        'canton_id' =>!empty($post['canton_id']) ? (int)$post['canton_id'] : null,
        'direccion_detallada' =>$direccion,
    ];
    emxCheckoutInsertFlexible($pdo, 'direcciones_usuario', $datos);
}

emxCheckoutNormalizarCarritoSesion();

// ============================================
// FUNCIÓN: Calcular distancia (Haversine)
// ============================================
function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
    $radio_tierra = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $radio_tierra * $c;
}

// ============================================
// FUNCIÓN: Calcular tiempo de entrega
// ============================================
function calcularTiempoEntrega($distancia_km) {
    if ($distancia_km <= 10) return ['dias' =>0, 'mensaje' =>'Entrega hoy (2-4 horas)', 'estimado' =>'Hoy'];
    if ($distancia_km <= 50) return ['dias' =>1, 'mensaje' =>'Mañana (24 horas)', 'estimado' =>'Mañana'];
    if ($distancia_km <= 200) return ['dias' =>2, 'mensaje' =>'1-2 días hábiles', 'estimado' =>'En 1-2 días'];
    return ['dias' =>4, 'mensaje' =>'3-5 días hábiles', 'estimado' =>'En 3-5 días'];
}

// ============================================
// FUNCIÓN: Asignar sucursal óptima
// ============================================
function asignarSucursalOptima($pdo, $carrito, $lat_cliente, $lon_cliente, $user_id) {
    $sucursales = $pdo->query("SELECT * FROM sucursales WHERE is_active = TRUE ORDER BY es_matriz DESC")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($sucursales)) {
        throw new Exception('No hay sucursales activas disponibles.');
    }

    foreach ($sucursales as &$s) {
        $s['distancia'] = calcularDistancia($lat_cliente, $lon_cliente, $s['latitud'], $s['longitud']);
    }
    unset($s);
    usort($sucursales, fn($a, $b) =>$a['distancia'] <=>$b['distancia']);

    foreach ($sucursales as $sucursal) {
        $tiene_todo_el_stock = true;
        
        foreach ($carrito as $keyItem =>$item) {
            $pidItem = emxCheckoutProductoId($item, $keyItem);
            if (!$pidItem) continue;
            $stmt = $pdo->prepare("SELECT stock FROM inventario_sucursal WHERE sucursal_id = ? AND producto_id = ?");
            $stmt->execute([$sucursal['id'], $pidItem]);
            $stock = (int)($stmt->fetchColumn() ?: 0);
            
            if ($stock < $item['cantidad']) {
                $tiene_todo_el_stock = false;
                break;
            }
        }
        
        if ($tiene_todo_el_stock) {
            $tiempo = calcularTiempoEntrega($sucursal['distancia']);
            $fecha_estimada = calcularFechaEstimada($pdo, $tiempo['dias'], $sucursal['hora_corte'] ?? '14:00:00', $user_id, $sucursal['ciudad']);
            
            return [
                'sucursal_id' =>$sucursal['id'],
                'sucursal_nombre' =>$sucursal['nombre'],
                'distancia_km' =>round($sucursal['distancia'], 2),
                'tiempo_entrega' =>$tiempo['mensaje'],
                'caso' =>'Entrega desde sucursal más cercana',
                'detalle' =>"Todos los productos serán enviados desde {$sucursal['nombre']} (a " . round($sucursal['distancia'], 2) . " km de tu dirección).",
                'fecha_estimada' =>$fecha_estimada
            ];
        }
    }

    $matriz = $pdo->query("SELECT * FROM sucursales WHERE es_matriz = TRUE LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$matriz) {
        throw new Exception('No se encontró una sucursal matriz para procesar el pedido.');
    }
    
    $distancia_matriz = calcularDistancia($lat_cliente, $lon_cliente, $matriz['latitud'], $matriz['longitud']);
    $tiempo = calcularTiempoEntrega($distancia_matriz);
    $fecha_estimada = calcularFechaEstimada($pdo, $tiempo['dias'], $matriz['hora_corte'] ?? '14:00:00', $user_id, $matriz['ciudad']);
    
    return [
        'sucursal_id' =>$matriz['id'],
        'sucursal_nombre' =>$matriz['nombre'] . ' (Stock consolidado)',
        'distancia_km' =>round($distancia_matriz, 2),
        'tiempo_entrega' =>$tiempo['mensaje'],
        'caso' =>'Envío desde matriz',
        'detalle' =>"Tu pedido será procesado desde la {$matriz['nombre']}.",
        'fecha_estimada' =>$fecha_estimada
    ];
}

// ============================================
// FUNCIÓN: Calcular fecha estimada (Con lógica Prime y Hora de Corte)
// ============================================
function calcularFechaEstimada($pdo, $dias, $hora_corte, $user_id, $ciudad_sucursal) {
    $ahora = new DateTime();
    $hora_actual = $ahora->format('H:i');
    
    $stmt = $pdo->prepare("
        SELECT p.es_prime 
        FROM usuarios u 
        LEFT JOIN planes p ON u.plan_id = p.id 
        WHERE u.id = ? AND p.es_prime = TRUE AND (u.plan_expira_en IS NULL OR u.plan_expira_en >NOW())
    ");
    $stmt->execute([$user_id]);
    $es_prime = (bool)$stmt->fetchColumn();
    
    if ($es_prime && $dias <= 1) {
        if ($hora_actual < '18:00') {
            $ahora->setTime(20, 0, 0);
        } else {
            $ahora->modify('+1 day');
            $ahora->setTime(12, 0, 0);
        }
        return $ahora->format('Y-m-d H:i:s');
    }
    
    $hora_corte_str = substr($hora_corte, 0, 5);
    if ($hora_actual >$hora_corte_str) {
        $ahora->modify('+' . ($dias + 1) . ' days');
    } else {
        $ahora->modify('+' . $dias . ' days');
    }
    
    $ahora->setTime(12, 0, 0);
    return $ahora->format('Y-m-d H:i:s');
}

// ============================================
// FUNCIÓN: Validar Luhn (Tarjetas)
// ============================================
function validarLuhn($numero) {
    $suma = 0; $longitud = strlen($numero); $paridad = $longitud % 2;
    for ($i = 0; $i < $longitud; $i++) {
        $digito = (int)$numero[$i];
        if ($i % 2 == $paridad) { $digito *= 2; if ($digito >9) $digito -= 9; }
        $suma += $digito;
    }
    return ($suma % 10 == 0);
}

// ============================================
// PROCESAR EL PEDIDO
// ============================================
if ($action === 'procesar') {
    try {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $cp = trim($_POST['codigo_postal'] ?? '');
        $lat_cliente = (float)($_POST['latitud'] ?? 0);
        $lon_cliente = (float)($_POST['longitud'] ?? 0);
        $provincia_id = !empty($_POST['provincia_id']) ? (int)$_POST['provincia_id'] : null;
        $canton_id = !empty($_POST['canton_id']) ? (int)$_POST['canton_id'] : null;
        $provincia_nombre = trim($_POST['provincia_nombre'] ?? '');
        if ($canton_id) {
            $stGeo = $pdo->prepare("SELECT c.nombre AS canton, p.nombre AS provincia FROM cantones c LEFT JOIN provincias p ON p.id = c.provincia_id WHERE c.id = ?");
            $stGeo->execute([$canton_id]);
            if ($geo = $stGeo->fetch(PDO::FETCH_ASSOC)) {
                $ciudad = $geo['canton'] ?: $ciudad;
                $provincia_nombre = $geo['provincia'] ?: $provincia_nombre;
            }
        }

        $facturacion_datos = [
            'tipo_identificacion' =>trim($_POST['fact_tipo_identificacion'] ?? 'cedula'),
            'identificacion' =>preg_replace('/[^0-9A-Za-z]/', '', $_POST['fact_identificacion'] ?? ''),
            'razon_social' =>trim($_POST['fact_razon_social'] ?? $nombre),
            'email' =>trim($_POST['fact_email'] ?? $email),
            'telefono' =>trim($_POST['fact_telefono'] ?? $telefono),
            'direccion' =>trim($_POST['fact_direccion'] ?? $direccion),
            'provincia_id' =>$provincia_id,
            'canton_id' =>$canton_id,
            'provincia' =>$provincia_nombre,
            'canton' =>$ciudad
        ];
        if ($facturacion_datos['tipo_identificacion'] !== 'consumidor_final' && ($facturacion_datos['identificacion'] === '' || $facturacion_datos['razon_social'] === '')) {
            throw new Exception('Completa los datos de facturación.');
        }

        if (empty($nombre) || empty($email) || empty($telefono) || empty($direccion) || empty($ciudad)) {
            throw new Exception('Todos los campos de envío son obligatorios.');
        }
        if (abs($lat_cliente) < 0.000001 && abs($lon_cliente) < 0.000001) {
            throw new Exception('Selecciona una dirección guardada o usa tu ubicación actual para calcular la sucursal más cercana.');
        }

        $card_number = str_replace(' ', '', $_POST['card_number'] ?? '');
        $card_name = trim($_POST['card_name'] ?? '');
        $card_expiry = trim($_POST['card_expiry'] ?? '');
        $card_cvv = trim($_POST['card_cvv'] ?? '');

        if (empty($card_number) || empty($card_name) || empty($card_expiry) || empty($card_cvv)) {
            throw new Exception('Completa todos los datos de la tarjeta.');
        }

        if (!validarLuhn($card_number)) {
            throw new Exception('Número de tarjeta inválido.');
        }

        if (!preg_match('/^[0-9]{3,4}$/', $card_cvv)) {
            throw new Exception('CVV inválido.');
        }

        list($mes, $anio) = explode('/', $card_expiry);
        if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $card_expiry)) {
            throw new Exception('Fecha de expiración inválida.');
        }
        $expiraTs = strtotime('last day of 20' . $anio . '-' . $mes . ' 23:59:59');
        if ($expiraTs !== false && $expiraTs < time()) {
            throw new Exception('Tarjeta vencida.');
        }

        $marca = 'Desconocida';
        if (preg_match('/^4/', $card_number)) $marca = 'Visa';
        elseif (preg_match('/^5[1-5]/', $card_number)) $marca = 'Mastercard';
        elseif (preg_match('/^3[47]/', $card_number)) $marca = 'American Express';
        $ultimos_4 = substr($card_number, -4);

        $subtotal = 0; 
        $iva_total = 0;
        $total_general = 0;
        
        foreach ($_SESSION['carrito'] as $keyItem =>$item) {
            $item['producto_id'] = emxCheckoutProductoId($item, $keyItem);
            if (!$item['producto_id']) continue;
            $precio_base_desc = $item['precio_base'] * (1 - ($item['descuento_aplicado'] / 100));
            $item_subtotal = $precio_base_desc * $item['cantidad'];
            $item_iva = $item_subtotal * ($item['iva'] / 100);
            $item_total = $item_subtotal + $item_iva;
            
            $subtotal += $item_subtotal;
            $iva_total += $item_iva;
            $total_general += $item_total;
        }

        if (emxCarritoTieneBackorderPendiente()) {
            throw new Exception('Tienes productos con cantidad mayor al stock. Acepta el calendario de entrega en el carrito antes de pagar.');
        }

        $asignacion = asignarSucursalOptima($pdo, $_SESSION['carrito'], $lat_cliente, $lon_cliente, $_SESSION['usuario_id']);

        sleep(2);

        $pdo->beginTransaction();
        $usuario_id = $_SESSION['usuario_id'];
        emxGuardarDireccionCheckoutSiAplica($pdo, $usuario_id, $_POST);
        
        $stmt = $pdo->prepare("
            INSERT INTO pedidos (usuario_id, nombre_cliente, email, telefono, direccion, ciudad, codigo_postal, provincia, provincia_id, canton_id,
                subtotal, iva_total, total, metodo_pago, ultimos_4_digitos, marca_tarjeta,
                sucursal_asignada_id, distancia_km, estado, estado_pago, fecha_estimada_entrega, historial_estados, facturacion_datos, checkout_paso_info) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', 'pendiente_aprobacion', ?, ?, ?::jsonb, ?::jsonb) 
            RETURNING id
        ");
        $stmt->execute([
            $usuario_id, $nombre, $email, $telefono, $direccion, $ciudad, $cp, $provincia_nombre, $provincia_id, $canton_id,
            $subtotal, $iva_total, $total_general, $marca, $ultimos_4, $marca,
            $asignacion['sucursal_id'], $asignacion['distancia_km'],
            $asignacion['fecha_estimada'], 
            json_encode([[
                'estado' =>'Pendiente',
                'descripcion' =>'Pago simulado registrado. El pedido queda reservado y pendiente de aprobación administrativa. ' . $asignacion['caso'] . '. ' . $asignacion['detalle'],
                'fecha' =>date('Y-m-d H:i:s'),
                'icono' =>'fa-hourglass-half'
            ]], JSON_UNESCAPED_UNICODE),
            json_encode($facturacion_datos, JSON_UNESCAPED_UNICODE),
            json_encode(['direccion_tipo' =>$_POST['direccion_tipo'] ?? 'manual', 'facturacion' =>'capturada', 'pago_simulado' =>'pendiente_aprobacion'], JSON_UNESCAPED_UNICODE)
        ]);
        $pedido_id = $stmt->fetchColumn();

        // ============================================
        // ⭐ NUEVO: GENERAR NÚMEROS DE SERIE ÚNICOS POR CADA UNIDAD VENDIDA
        // ============================================
        $stmt_detalle = $pdo->prepare("
            INSERT INTO detalle_pedidos 
            (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario, iva_porcentaje, total, sucursal_origen_id, numero_serie_vendido) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id
        ");
        
        foreach ($_SESSION['carrito'] as $keyItem =>$item) {
            $item['producto_id'] = emxCheckoutProductoId($item, $keyItem);
            if (!$item['producto_id']) continue;
            $precio_base_desc = $item['precio_base'] * (1 - ($item['descuento_aplicado'] / 100));
            $item_subtotal = $precio_base_desc * $item['cantidad'];
            $item_iva = $item_subtotal * ($item['iva'] / 100);
            $item_total = $item_subtotal + $item_iva;
            
            $asig = $asignacion['sucursal_id'];
            
            // Obtener la marca del producto para generar series realistas
            $stmt_marca = $pdo->prepare("
                SELECT m.nombre 
                FROM productos p 
                LEFT JOIN marcas m ON p.marca_id = m.id 
                WHERE p.id = ?
            ");
            $stmt_marca->execute([$item['producto_id']]);
            $marca_producto = $stmt_marca->fetchColumn() ?: 'GEN';
            
            // Reservar/descontar únicamente stock inmediato después del pago.
            // Regla:
            // - Entrega parcial: las unidades disponibles salen ahora y se descuentan físicamente.
            // - Entrega total: las unidades disponibles se apartan/reservan para el pedido, pero no se despachan todavía.
            $stmtStock = $pdo->prepare("SELECT stock" . (emxBackorderColumnExists($pdo, 'inventario_sucursal', 'stock_reservado') ? ", COALESCE(stock_reservado,0) AS stock_reservado" : ", 0 AS stock_reservado") . " FROM inventario_sucursal WHERE sucursal_id = ? AND producto_id = ? FOR UPDATE");
            $stmtStock->execute([$asig, $item['producto_id']]);
            $rowStock = $stmtStock->fetch(PDO::FETCH_ASSOC) ?: ['stock' =>0, 'stock_reservado' =>0];
            $stockDisponibleSucursal = max(0, (int)($rowStock['stock'] ?? 0) - (int)($rowStock['stock_reservado'] ?? 0));
            $cantidadSolicitada = (int)$item['cantidad'];
            $cantidadInmediata = min($cantidadSolicitada, $stockDisponibleSucursal);
            $cantidadPendiente = max(0, $cantidadSolicitada - $cantidadInmediata);

            $planAceptado = $cantidadPendiente >0 ? ($_SESSION['backorder_planes'][$item['producto_id']]['plan'] ?? null) : null;
            $opcionAceptada = $cantidadPendiente >0 ? ($_SESSION['backorder_planes'][$item['producto_id']]['opcion'] ?? 'total') : 'normal';
            if ($cantidadPendiente >0 && !$planAceptado) {
                throw new Exception('Falta aceptar el calendario para ' . $item['nombre']);
            }

            // Generar series solo para unidades físicamente entregadas ahora.
            // Para entrega total se reserva el stock existente, pero la garantía/serie queda pendiente hasta el despacho real.
            $series_generadas = [];
            $unidadesFisicasAhora = ($opcionAceptada === 'total') ? 0 : $cantidadInmediata;
            for ($i = 0; $i < $unidadesFisicasAhora; $i++) {
                $series_generadas[] = generarSerieUnica($marca_producto);
            }
            if ($opcionAceptada === 'total' && $cantidadInmediata >0) {
                $series_generadas[] = 'RESERVADO_ENTREGA_TOTAL_' . $cantidadInmediata . '_UNIDADES';
            }
            if ($cantidadPendiente >0) {
                $series_generadas[] = 'PENDIENTE_BACKORDER_' . $cantidadPendiente . '_UNIDADES';
            }
            $series_json = json_encode($series_generadas);

            $stmt_detalle->execute([
                $pedido_id,
                $item['producto_id'],
                $item['nombre'],
                $cantidadSolicitada,
                $precio_base_desc,
                $item['iva'],
                $item_total,
                $asig,
                $series_json
            ]);
            $detalleIdCreado = $stmt_detalle->fetchColumn();
            if ($detalleIdCreado && function_exists('emxAplicarGarantiaADetalle')) {
                emxAplicarGarantiaADetalle($pdo, $detalleIdCreado, $item['producto_id'], date('Y-m-d'));
            }

            if ($cantidadInmediata >0) {
                $modoStock = ($cantidadPendiente >0 && $opcionAceptada === 'total') ? 'total' : 'parcial';
                emxAplicarStockInmediatoDespuesPago($pdo, $asig, $item['producto_id'], $cantidadInmediata, $modoStock);
            }

            if ($cantidadPendiente >0) {
                $stmtBo = $pdo->prepare("INSERT INTO pedidos_backorder (pedido_original_id, usuario_id, producto_id, cantidad_pendiente, estado, created_at, updated_at) VALUES (?, ?, ?, ?, 'cronograma_aceptado', NOW(), NOW()) RETURNING id");
                $stmtBo->execute([$pedido_id, $usuario_id, $item['producto_id'], $cantidadPendiente]);
                $backorderId = $stmtBo->fetchColumn();

                $lotes = [];
                if ($opcionAceptada === 'parcial' && !empty($planAceptado['opcion_parcial']['lotes'])) {
                    $lotes = $planAceptado['opcion_parcial']['lotes'];
                } elseif (!empty($planAceptado['opcion_total'])) {
                    $lotes = [$planAceptado['opcion_total']];
                }
                $stmtCrono = $pdo->prepare("INSERT INTO cronogramas_reabastecimiento (backorder_id, proveedor_id, fecha_llegada_tienda, cantidad, tipo_entrega, opcion_grupo, estado, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pendiente_llegada', NOW())");
                foreach ($lotes as $idx =>$lote) {
                    $stmtCrono->execute([
                        $backorderId,
                        $lote['proveedor_id'] ?? null,
                        $lote['fecha'] ?? date('Y-m-d', strtotime('+7 days')),
                        (int)($lote['cantidad'] ?? $cantidadPendiente),
                        $opcionAceptada === 'parcial' ? 'parcial' : 'total',
                        $opcionAceptada === 'parcial' ? 'A' : 'B'
                    ]);
                }

                $politicaPedido = emxCalcularCantidadSolicitudInterna($pdo, $item['producto_id'], $cantidadPendiente);
                $cantidadSolicitudProveedor = (int)($planAceptado['cantidad_solicitud_interna'] ?? ($politicaPedido['cantidad_solicitud_interna'] ?? $cantidadPendiente));
                $reposicionMinima = (int)($politicaPedido['reposicion_minima'] ?? 0);
                emxCrearSolicitudReabastecimientoBackorder(
                    $pdo,
                    $item['producto_id'],
                    max($cantidadPendiente, $cantidadSolicitudProveedor),
                    $backorderId,
                    'Pedido con sobrestock #' . substr($pedido_id, 0, 8) . '. Faltante cliente: ' . $cantidadPendiente . '. Reposición mínima por punto de reorden: ' . $reposicionMinima . '.'
                );
            }
        }

        $pdo->commit();
        $_SESSION['carrito'] = [];
        unset($_SESSION['backorder_planes']);
        $pedido_exitoso = [
            'id' =>$pedido_id,
            'total' =>$total_general,
            'asignacion' =>$asignacion
        ];

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        $error_msg = $e->getMessage();
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        $error_msg = 'Error de base de datos: ' . $e->getMessage();
    }
}

// ============================================
// CALCULAR TOTALES PARA LA VISTA
// ============================================
$productos_carrito = [];
$subtotal_original = 0;
$total_descuento = 0;
$total_iva = 0;
$total_general = 0;
$total_items = 0;

if (!$pedido_exitoso) {
    foreach ($_SESSION['carrito'] as $keyItem =>$item) {
        $productoIdVista = emxCheckoutProductoId($item, $keyItem);
        if (!$productoIdVista) continue;
        $cantidad = (int)($item['cantidad'] ?? 1);

        try {
            $stProdCheckout = $pdo->prepare("SELECT p.*, pm.url as imagen FROM productos p LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.orden = 1 WHERE p.id = ? AND p.deleted_at IS NULL");
            $stProdCheckout->execute([$productoIdVista]);
            $prodCheckout = $stProdCheckout->fetch(PDO::FETCH_ASSOC);
            if ($prodCheckout && function_exists('emxCalcularPrecioProductoCarrito')) {
                $calcCheckout = emxCalcularPrecioProductoCarrito($pdo, $prodCheckout, $cantidad, $_SESSION['usuario_id'] ?? null);
                $_SESSION['carrito'][$keyItem]['producto_id'] = $productoIdVista;
                $_SESSION['carrito'][$keyItem]['nombre'] = $prodCheckout['nombre'] ?? ($item['nombre'] ?? 'Producto');
                $_SESSION['carrito'][$keyItem]['precio_base'] = $calcCheckout['precio_base'];
                $_SESSION['carrito'][$keyItem]['precio_con_iva'] = $calcCheckout['precio_con_iva'];
                $_SESSION['carrito'][$keyItem]['precio_final'] = $calcCheckout['precio_final'];
                $_SESSION['carrito'][$keyItem]['descuento_aplicado'] = $calcCheckout['descuento_total_porcentaje'];
                $_SESSION['carrito'][$keyItem]['descuento_producto'] = $calcCheckout['descuento_producto'];
                $_SESSION['carrito'][$keyItem]['descuento_volumen'] = $calcCheckout['descuento_volumen'];
                $_SESSION['carrito'][$keyItem]['descuento_plan'] = $calcCheckout['descuento_plan'];
                $_SESSION['carrito'][$keyItem]['rango_volumen_label'] = $calcCheckout['rango_volumen_label'];
                $_SESSION['carrito'][$keyItem]['iva'] = $calcCheckout['iva'];
                $_SESSION['carrito'][$keyItem]['imagen'] = $prodCheckout['imagen'] ?? ($item['imagen'] ?? null);
                $item = $_SESSION['carrito'][$keyItem];
            }
        } catch (Throwable $e) {}

        $precio_base = $item['precio_base'];
        $iva_pct = $item['iva'] ?? 15;
        $descuento_pct = $item['descuento_aplicado'] ?? 0;

        $precio_con_iva_original = $precio_base * (1 + ($iva_pct / 100));
        $precio_base_desc = $precio_base * (1 - ($descuento_pct / 100));
        $iva_unitario = $precio_base_desc * ($iva_pct / 100);
        $precio_final_unitario = $precio_base_desc + $iva_unitario;

        $linea_subtotal_orig = $precio_con_iva_original * $cantidad;
        $linea_ahorro = ($precio_con_iva_original - $precio_final_unitario) * $cantidad;
        $linea_iva = $iva_unitario * $cantidad;
        $linea_total = $precio_final_unitario * $cantidad;

        $productos_carrito[] = [
            'producto_id' =>$productoIdVista,
            'nombre' =>$item['nombre'],
            'imagen' =>$item['imagen'],
            'cantidad' =>$cantidad,
            'precio_final' =>$precio_final_unitario,
            'precio_con_iva' =>$precio_con_iva_original,
            'descuento_aplicado' =>$descuento_pct,
            'total' =>$linea_total
        ];

        $subtotal_original += $linea_subtotal_orig;
        $total_descuento += $linea_ahorro;
        $total_iva += $linea_iva;
        $total_general += $linea_total;
        $total_items += $cantidad;
    }
}

// Datos geográficos para checkout con combos
$usuario_checkout = ['nombres'=>'','apellidos'=>'','email'=>'','telefono'=>'','cedula_ruc'=>''];
try {
    $stU = $pdo->prepare("SELECT nombres, apellidos, email, telefono, cedula_ruc FROM usuarios WHERE id = ?");
    $stU->execute([$_SESSION['usuario_id']]);
    $usuario_checkout = $stU->fetch(PDO::FETCH_ASSOC) ?: $usuario_checkout;
} catch (Throwable $e) {}
$nombre_checkout = trim(($usuario_checkout['nombres'] ?? '') . ' ' . ($usuario_checkout['apellidos'] ?? ''));
if ($nombre_checkout === '') $nombre_checkout = $_SESSION['usuario_nombre'] ?? '';
$provincias_checkout = [];
$cantones_checkout = [];
try {
    $provincias_checkout = $pdo->query("SELECT id, nombre FROM provincias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $cantones_checkout = $pdo->query("SELECT id, provincia_id, nombre FROM cantones ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $provincias_checkout = [];
    $cantones_checkout = [];
}

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/frontend/checkout_view.php
require EMX_VIEWS_PATH . '/frontend/checkout_view.php';
exit;
