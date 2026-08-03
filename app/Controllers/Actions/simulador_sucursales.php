<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';

// Solo accesible para admins
emxRequireRole(['SUPERADMIN', 'ADMIN']);
emxVerificarCsrfSiPOST();

// ============================================
// FUNCIÓN: Calcular distancia entre dos puntos (Fórmula de Haversine)
// ============================================
function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
    $tierra_radio = 6371; // Radio de la Tierra en km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $tierra_radio * $c;
}

// ============================================
// FUNCIÓN: Encontrar sucursal óptima para un pedido
// ============================================
function encontrarSucursalOptima($pdo, $producto_id, $cantidad_requerida, $lat_cliente, $lon_cliente) {
    $resultado = [
        'sucursal_asignada' =>null,
        'distancia_km' =>0,
        'tiempo_estimado' =>'',
        'caso' =>'',
        'explicacion' =>'',
        'alternativas' =>[]
    ];

    // Obtener todas las sucursales activas
    $stmt = $pdo->query("SELECT * FROM sucursales WHERE is_active = TRUE");
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($sucursales)) {
        $resultado['caso'] = 'ERROR';
        $resultado['explicacion'] = 'No hay sucursales activas en el sistema.';
        return $resultado;
    }

    // Calcular distancia de cada sucursal al cliente
    $sucursales_con_distancia = [];
    foreach ($sucursales as $sucursal) {
        $distancia = calcularDistancia($lat_cliente, $lon_cliente, $sucursal['latitud'], $sucursal['longitud']);
        $sucursales_con_distancia[] = [
            ...$sucursal,
            'distancia_km' =>round($distancia, 2)
        ];
    }

    // Ordenar por distancia (más cercana primero)
    usort($sucursales_con_distancia, fn($a, $b) =>$a['distancia_km'] <=>$b['distancia_km']);

    // CASO 1: Sucursal más cercana tiene stock suficiente
    $sucursal_cercana = $sucursales_con_distancia[0];
    
    // Simular stock (en producción vendría de una tabla inventario_sucursal)
    // Para simulación, usaremos un valor aleatorio basado en el ID
    $stock_simulado = rand(0, 20);
    
    if ($stock_simulado >= $cantidad_requerida) {
        $resultado['sucursal_asignada'] = $sucursal_cercana;
        $resultado['distancia_km'] = $sucursal_cercana['distancia_km'];
        $resultado['tiempo_estimado'] = calcularTiempoEntrega($sucursal_cercana['distancia_km']);
        $resultado['caso'] = 'CASO 1: Sucursal más cercana con stock suficiente';
        $resultado['explicacion'] = "La sucursal '{$sucursal_cercana['nombre']}' está a {$sucursal_cercana['distancia_km']} km y tiene {$stock_simulado} unidades disponibles (necesitas {$cantidad_requerida}). Es la opción más rápida y económica.";
        return $resultado;
    }

    // CASO 2: Sucursal más cercana NO tiene stock suficiente, buscar la siguiente
    $resultado['alternativas'][] = [
        'sucursal' =>$sucursal_cercana,
        'stock' =>$stock_simulado,
        'razon' =>'Stock insuficiente'
    ];

    foreach ($sucursales_con_distancia as $index =>$sucursal) {
        if ($index === 0) continue; // Ya revisamos la primera
        
        $stock_simulado = rand(0, 20);
        
        if ($stock_simulado >= $cantidad_requerida) {
            $resultado['sucursal_asignada'] = $sucursal;
            $resultado['distancia_km'] = $sucursal['distancia_km'];
            $resultado['tiempo_estimado'] = calcularTiempoEntrega($sucursal['distancia_km']);
            $resultado['caso'] = 'CASO 2: Sucursal cercana sin stock, asignada la siguiente con disponibilidad';
            $resultado['explicacion'] = "La sucursal más cercana ('{$sucursal_cercana['nombre']}') solo tiene {$resultado['alternativas'][0]['stock']} unidades. Se asignó '{$sucursal['nombre']}' a {$sucursal['distancia_km']} km con {$stock_simulado} unidades disponibles.";
            return $resultado;
        }
        
        $resultado['alternativas'][] = [
            'sucursal' =>$sucursal,
            'stock' =>$stock_simulado,
            'razon' =>'Stock insuficiente'
        ];
    }

    // CASO 3: Ninguna sucursal tiene stock suficiente individualmente
    // Buscar combinación de sucursales
    $stock_total = array_sum(array_column($resultado['alternativas'], 'stock'));
    
    if ($stock_total >= $cantidad_requerida) {
        $resultado['caso'] = 'CASO 3: Stock dividido entre múltiples sucursales';
        $resultado['explicacion'] = "Ninguna sucursal individual tiene {$cantidad_requerida} unidades. Se requiere dividir el pedido entre {$sucursales_con_distancia[0]['nombre']} ({$resultado['alternativas'][0]['stock']} unid.) y {$sucursales_con_distancia[1]['nombre']} ({$resultado['alternativas'][1]['stock']} unid.). Esto incrementa el costo de envío.";
        $resultado['sucursal_asignada'] = $sucursales_con_distancia[0]; // Sucursal principal
        $resultado['distancia_km'] = $sucursales_con_distancia[0]['distancia_km'];
        $resultado['tiempo_estimado'] = '2-4 días (envío dividido)';
        return $resultado;
    }

    // CASO 4: Stock total insuficiente en todo el sistema
    $resultado['caso'] = 'CASO 4: Stock insuficiente en todo el sistema';
    $resultado['explicacion'] = "El stock total en todas las sucursales es de {$stock_total} unidades, pero necesitas {$cantidad_requerida}. El producto está agotado a nivel nacional.";
    return $resultado;
}

function calcularTiempoEntrega($distancia_km) {
    if ($distancia_km <= 10) return 'Mismo día (2-4 horas)';
    if ($distancia_km <= 50) return '24 horas';
    if ($distancia_km <= 200) return '1-2 días';
    return '3-5 días';
}

// ============================================
// PROCESAR SIMULACIÓN
// ============================================
$simulacion_resultado = null;
$caso_seleccionado = $_POST['caso'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simular'])) {
    $producto_id = $_POST['producto_id'] ?? null;
    $cantidad = (int)($_POST['cantidad'] ?? 1);
    $lat_cliente = (float)($_POST['latitud'] ?? -0.1807); // Quito por defecto
    $lon_cliente = (float)($_POST['longitud'] ?? -78.4678);
    
    if ($producto_id && $cantidad >0) {
        $simulacion_resultado = encontrarSucursalOptima($pdo, $producto_id, $cantidad, $lat_cliente, $lon_cliente);
    }
}

// Cargar productos para el selector
$productos = $pdo->query("SELECT id, nombre, precio_base FROM productos WHERE deleted_at IS NULL AND is_active = TRUE LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

// Cargar sucursales
$sucursales = $pdo->query("SELECT * FROM sucursales WHERE is_active = TRUE ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
?><!DOCTYPE html><html lang="es"><head>
<!-- Favicon ElectroMax global -->
<link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
<link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
<link rel="manifest" href="assets/favicon/site.webmanifest">
<meta name="theme-color" content="#0b4da2">
<meta charset="UTF-8"><title>Simulador de Sucursales - ElectroMax</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"><style>* { font-family: 'Inter', sans-serif; }
        body { background: #f1f5f9; }
        .sidebar { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
        .nav-item { transition: all 0.2s; }
        .nav-item:hover { transform: translateX(4px); }
        .nav-item.active { background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%); }
        .btn-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); transition: all 0.2s; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4); }
        .case-card { transition: all 0.2s; }
        .case-card:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    </style></head><body class="flex h-screen"><aside class="sidebar w-64 text-white flex flex-col shadow-2xl flex-shrink-0"><div class="p-6 border-b border-slate-800 flex items-center gap-3"><img src="assets/electromax_logo.png" alt="ElectroMax" class="h-14 w-auto max-w-[200px] object-contain drop-shadow-[0_14px_22px_rgba(56,189,248,.25)]"></div><nav class="flex-1 p-4 space-y-1"><a href="admin.php?module=dashboard" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300"><i class="fas fa-chart-line w-5"></i>Dashboard</a><a href="admin.php?module=productos" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300"><i class="fas fa-box w-5"></i>Productos</a><a href="admin.php?module=sucursales" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300"><i class="fas fa-store w-5"></i>Sucursales</a><a href="simulador_sucursales.php" class="nav-item active flex items-center gap-3 px-4 py-3 rounded-lg text-white"><i class="fas fa-flask w-5"></i>Simulador</a><a href="index.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 mt-8"><i class="fas fa-store w-5"></i>Ver Tienda</a><a href="logout.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-red-400 hover:bg-red-900/20"><i class="fas fa-sign-out-alt w-5"></i>Cerrar Sesión</a></nav></aside><main class="flex-1 overflow-y-auto"><header class="bg-white border-b border-slate-200 px-8 py-5"><div class="flex justify-between items-center"><div><h2 class="text-2xl font-bold text-slate-800">Simulador de Asignación de Sucursales</h2><p class="text-sm text-slate-500">Prueba los 10 escenarios de fulfillment en tiempo real</p></div></div></header><div class="p-8 space-y-8"><!-- FORMULARIO DE SIMULACIÓN --><div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"><h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-flask text-blue-600"></i>Simular Pedido
                </h3><form method="POST" action="simulador_sucursales.php"><?= emxCsrfCampo() ?> class="grid grid-cols-1 md:grid-cols-4 gap-4"><div><label class="block text-xs font-medium text-slate-700 mb-1">Producto</label><select name="producto_id" required class="w-full rounded-lg px-3 py-2.5 text-sm border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"><option value="">Seleccionar producto...</option><?php foreach ($productos as $p): ?><option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option><?php endforeach; ?></select></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Cantidad</label><input type="number" name="cantidad" min="1" value="1" required class="w-full rounded-lg px-3 py-2.5 text-sm border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Latitud Cliente</label><input type="number" step="0.000001" name="latitud" value="-0.1807" required class="w-full rounded-lg px-3 py-2.5 text-sm border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"><p class="text-[10px] text-slate-400 mt-1">Quito: -0.1807</p></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Longitud Cliente</label><input type="number" step="0.000001" name="longitud" value="-78.4678" required class="w-full rounded-lg px-3 py-2.5 text-sm border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"><p class="text-[10px] text-slate-400 mt-1">Quito: -78.4678</p></div><div class="md:col-span-4 flex justify-end"><button type="submit" name="simular" class="btn-primary text-white px-6 py-2.5 rounded-lg font-medium flex items-center gap-2"><i class="fas fa-play"></i>Ejecutar Simulación
                        </button></div></form></div><!-- RESULTADO DE SIMULACIÓN --><?php if ($simulacion_resultado): ?><div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"><h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-check-circle text-emerald-600"></i>Resultado de la Simulación
                    </h3><div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6"><p class="font-bold text-blue-900 mb-1"><?= htmlspecialchars($simulacion_resultado['caso']) ?></p><p class="text-sm text-blue-800"><?= htmlspecialchars($simulacion_resultado['explicacion']) ?></p></div><?php if ($simulacion_resultado['sucursal_asignada']): ?><div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6"><div class="bg-slate-50 rounded-lg p-4"><p class="text-xs text-slate-500 mb-1">Sucursal Asignada</p><p class="font-bold text-slate-900"><?= htmlspecialchars($simulacion_resultado['sucursal_asignada']['nombre']) ?></p><p class="text-sm text-slate-600"><?= htmlspecialchars($simulacion_resultado['sucursal_asignada']['ciudad']) ?></p></div><div class="bg-slate-50 rounded-lg p-4"><p class="text-xs text-slate-500 mb-1">Distancia</p><p class="font-bold text-slate-900"><?= $simulacion_resultado['distancia_km'] ?>km</p></div><div class="bg-slate-50 rounded-lg p-4"><p class="text-xs text-slate-500 mb-1">Tiempo Estimado</p><p class="font-bold text-slate-900"><?= htmlspecialchars($simulacion_resultado['tiempo_estimado']) ?></p></div></div><?php endif; ?><?php if (!empty($simulacion_resultado['alternativas'])): ?><div class="border-t border-slate-200 pt-4"><p class="text-sm font-semibold text-slate-700 mb-2">Sucursales evaluadas:</p><div class="space-y-2"><?php foreach ($simulacion_resultado['alternativas'] as $alt): ?><div class="flex justify-between items-center bg-amber-50 border border-amber-200 rounded-lg px-4 py-2"><div><span class="font-medium text-slate-800"><?= htmlspecialchars($alt['sucursal']['nombre']) ?></span><span class="text-xs text-slate-500 ml-2"><?= $alt['sucursal']['distancia_km'] ?>km</span></div><div class="flex items-center gap-3"><span class="text-sm font-semibold text-amber-700"><?= $alt['stock'] ?>unidades</span><span class="text-xs text-amber-600"><i class="fas fa-exclamation-triangle mr-1"></i><?= htmlspecialchars($alt['razon']) ?></span></div></div><?php endforeach; ?></div></div><?php endif; ?></div><?php endif; ?><!-- LOS 10 CASOS DE SIMULACIÓN --><div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"><h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2"><i class="fas fa-list-ol text-blue-600"></i>Los 10 Escenarios de Fulfillment
                </h3><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><!-- CASO 1 --><div class="case-card border border-slate-200 rounded-lg p-4 bg-emerald-50"><div class="flex items-start gap-3"><div class="w-10 h-10 bg-emerald-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">1</div><div><h4 class="font-bold text-slate-900 mb-1">Sucursal más cercana con stock suficiente</h4><p class="text-sm text-slate-600 mb-2">El cliente está a 5 km de la Sucursal Norte, que tiene 15 unidades. El pedido es de 3 unidades.</p><div class="bg-white rounded p-2 text-xs"><strong>Resultado:</strong>Asignación directa a Sucursal Norte. Entrega en 2-4 horas. Costo de envío mínimo.
                                </div></div></div></div><!-- CASO 2 --><div class="case-card border border-slate-200 rounded-lg p-4 bg-amber-50"><div class="flex items-start gap-3"><div class="w-10 h-10 bg-amber-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">2</div><div><h4 class="font-bold text-slate-900 mb-1">Sucursal cercana sin stock, asignar la siguiente</h4><p class="text-sm text-slate-600 mb-2">Sucursal Norte (5 km) solo tiene 1 unidad. Sucursal Sur (15 km) tiene 20 unidades. Pedido de 5 unidades.</p><div class="bg-white rounded p-2 text-xs"><strong>Resultado:</strong>Se salta la más cercana y asigna Sucursal Sur. Entrega en 24 horas.
                                </div></div></div></div><!-- CASO 3 --><div class="case-card border border-slate-200 rounded-lg p-4 bg-blue-50"><div class="flex items-start gap-3"><div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">3</div><div><h4 class="font-bold text-slate-900 mb-1">Stock dividido entre múltiples sucursales</h4><p class="text-sm text-slate-600 mb-2">Pedido de 10 unidades. Sucursal A tiene 6, Sucursal B tiene 8. Ninguna tiene 10 individualmente.</p><div class="bg-white rounded p-2 text-xs"><strong>Resultado:</strong>Se divide el pedido: 6 de A + 4 de B. Incrementa costo logístico. Entrega en 2-3 días.
                                </div></div></div></div><!-- CASO 4 --><div class="case-card border border-slate-200 rounded-lg p-4 bg-red-50"><div class="flex items-start gap-3"><div class="w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">4</div><div><h4 class="font-bold text-slate-900 mb-1">Stock insuficiente en todo el sistema</h4><p class="text-sm text-slate-600 mb-2">Pedido de 50 unidades. Stock total en todas las sucursales: 35 unidades.</p><div class="bg-white rounded p-2 text-xs"><strong>Resultado:</strong>Pedido rechazado o en espera de reabastecimiento. Notificación al cliente.
                                </div></div></div></div><!-- CASO 5 --><div class="case-card border border-slate-200 rounded-lg p-4 bg-purple-50"><div class="flex items-start gap-3"><div class="w-10 h-10 bg-purple-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">5</div><div><h4 class="font-bold text-slate-900 mb-1">Click & Collect (Recoger en tienda)</h4><p class="text-sm text-slate-600 mb-2">Cliente elige "Recoger en tienda" y selecciona Sucursal Centro. Stock disponible: 12 unidades.</p><div class="bg-white rounded p-2 text-xs"><strong>Resultado:</strong>Reserva stock en Sucursal Centro por 24 horas. Cliente recibe código de recogida.
                                </div></div></div></div><!-- CASO 6 --><div class="case-card border border-slate-200 rounded-lg p-4 bg-cyan-50"><div class="flex items-start gap-3"><div class="w-10 h-10 bg-cyan-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">6</div><div><h4 class="font-bold text-slate-900 mb-1">Cliente en zona remota sin sucursal cercana</h4><p class="text-sm text-slate-600 mb-2">Cliente en zona rural a 150 km de la sucursal más cercana. Pedido de 2 unidades.</p><div class="bg-white rounded p-2 text-xs"><strong>Resultado:</strong>Asignación a sucursal más cercana. Tiempo de entrega: 3-5 días. Costo de envío elevado.
                                </div></div></div></div><!-- CASO 7 --><div class="case-card border border-slate-200 rounded-lg p-4 bg-indigo-50"><div class="flex items-start gap-3"><div class="w-10 h-10 bg-indigo-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">7</div><div><h4 class="font-bold text-slate-900 mb-1">Sucursal en mantenimiento o cerrada</h4><p class="text-sm text-slate-600 mb-2">Sucursal Norte está a 3 km pero en mantenimiento. Sucursal Sur a 20 km operativa.</p><div class="bg-white rounded p-2 text-xs"><strong>Resultado:</strong>Sistema ignora sucursales inactivas. Asigna Sucursal Sur automáticamente.
                                </div></div></div></div><!-- CASO 8 --><div class="case-card border border-slate-200 rounded-lg p-4 bg-pink-50"><div class="flex items-start gap-3"><div class="w-10 h-10 bg-pink-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">8</div><div><h4 class="font-bold text-slate-900 mb-1">Múltiples productos en un pedido</h4><p class="text-sm text-slate-600 mb-2">Pedido con 3 productos diferentes. Producto A disponible en Norte, B en Sur, C en ambas.</p><div class="bg-white rounded p-2 text-xs"><strong>Resultado:</strong>Optimización: asignar sucursal que tenga más productos disponibles para minimizar envíos.
                                </div></div></div></div><!-- CASO 9 --><div class="case-card border border-slate-200 rounded-lg p-4 bg-orange-50"><div class="flex items-start gap-3"><div class="w-10 h-10 bg-orange-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">9</div><div><h4 class="font-bold text-slate-900 mb-1">Balanceo de carga entre sucursales</h4><p class="text-sm text-slate-600 mb-2">Sucursal Norte tiene 100 pedidos pendientes. Sucursal Sur solo 10. Ambas tienen stock.</p><div class="bg-white rounded p-2 text-xs"><strong>Resultado:</strong>Sistema asigna a Sucursal Sur para equilibrar carga operativa, aunque esté más lejos.
                                </div></div></div></div><!-- CASO 10 --><div class="case-card border border-slate-200 rounded-lg p-4 bg-teal-50"><div class="flex items-start gap-3"><div class="w-10 h-10 bg-teal-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">10</div><div><h4 class="font-bold text-slate-900 mb-1">Transferencia interna entre sucursales</h4><p class="text-sm text-slate-600 mb-2">Sucursal A sin stock, Sucursal B con exceso (50 unid.). Sistema genera solicitud de transferencia.</p><div class="bg-white rounded p-2 text-xs"><strong>Resultado:</strong>Transferencia automática en 24-48 horas. Stock actualizado en tiempo real.
                                </div></div></div></div></div></div><!-- MAPA VISUAL DE SUCURSALES --><?php if (!empty($sucursales)): ?><div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"><h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-map-marked-alt text-blue-600"></i>Sucursales Activas en el Sistema
                    </h3><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"><?php foreach ($sucursales as $s): ?><div class="border border-slate-200 rounded-lg p-4 hover:shadow-md transition"><div class="flex items-start justify-between mb-2"><h4 class="font-bold text-slate-900"><?= htmlspecialchars($s['nombre']) ?></h4><span class="bg-emerald-100 text-emerald-700 text-xs font-semibold px-2 py-1 rounded">ACTIVA</span></div><p class="text-sm text-slate-600 mb-1"><i class="fas fa-map-marker-alt mr-1 text-slate-400"></i><?= htmlspecialchars($s['direccion']) ?></p><p class="text-sm text-slate-600 mb-1"><i class="fas fa-city mr-1 text-slate-400"></i><?= htmlspecialchars($s['ciudad']) ?></p><?php if ($s['telefono']): ?><p class="text-sm text-slate-600 mb-1"><i class="fas fa-phone mr-1 text-slate-400"></i><?= htmlspecialchars($s['telefono']) ?></p><?php endif; ?><?php if ($s['horario_atencion']): ?><p class="text-xs text-slate-500 mt-2"><i class="fas fa-clock mr-1"></i><?= htmlspecialchars($s['horario_atencion']) ?></p><?php endif; ?><div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-400"><i class="fas fa-map-pin mr-1"></i><?= $s['latitud'] ?>, <?= $s['longitud'] ?></div></div><?php endforeach; ?></div></div><?php endif; ?></div></main><script src="assets/emx_modales.js"></script></body></html>