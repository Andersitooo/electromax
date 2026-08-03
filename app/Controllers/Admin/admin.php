<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
emxVerificarCsrfSiPOST();
require_once EMX_HELPERS_PATH . '/funciones_stock.php';
require_once EMX_HELPERS_PATH . '/funciones_wishlist.php';
require_once EMX_HELPERS_PATH . '/funciones_facturacion.php';
require_once EMX_HELPERS_PATH . '/flujo_admin.php';
// ============================================
// FUNCIÓN: ENVIAR NOTIFICACIÓN AL CLIENTE
// ============================================
if (!function_exists('enviarNotificacionCliente')) {
function enviarNotificacionCliente($pdo, $usuario_id, $tipo, $titulo, $mensaje, $enlace = '#', $tipo_enlace = 'ninguno') {
try {
$stmt = $pdo->prepare("
INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace_accion, tipo_enlace, leida, creado_en)
VALUES (?, ?, ?, ?, ?, ?, FALSE, NOW())
");
return $stmt->execute([$usuario_id, $tipo, $titulo, $mensaje, $enlace, $tipo_enlace]);
} catch (Exception $e) {
error_log("Error notificación: " . $e->getMessage());
return false;
}
}
}
// ============================================
// FUNCIÓN: VALIDAR SERIE DE DEVOLUCIÓN
// ============================================
if (!function_exists('validarSerieDevolucion')) {
function validarSerieDevolucion($pdo, $pedido_id, $producto_id, $serie_devuelta) {
return emxValidarSerieVendida($pdo, $pedido_id, $producto_id, $serie_devuelta);
}
}
// ============================================
// FUNCIÓN: GENERAR SKU PROFESIONAL
// ============================================
if (!function_exists('generarSKUProfesional')) {
function generarSKUProfesional($categoria_slug) {
$prefix = strtoupper(substr($categoria_slug, 0, 3));
$random = strtoupper(substr(md5(uniqid()), 0, 6));
return "EMX-{$prefix}-{$random}";
}
}
// ============================================
// FUNCIÓN: GENERAR SERIE ÚNICA
// ============================================
if (!function_exists('generarSerieUnica')) {
function generarSerieUnica($marca) {
$year = date('Y');
$brandCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $marca), 0, 3));
$hash = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
return "{$brandCode}-{$year}-{$hash}";
}
}
// ============================================
// ⭐ NUEVO: FUNCIÓN PARA PROCESAR RANGOS DE DESCUENTO POR VOLUMEN
// ============================================
function procesarRangosVolumen($rangos_post) {
$rangos_validos = [];
if (empty($rangos_post) || !is_array($rangos_post)) {
return '[]';
}
foreach ($rangos_post as $index =>$rango) {
$cantidad_min = (int)($rango['cantidad_min'] ?? 0);
$cantidad_max_raw = $rango['cantidad_max'] ?? '';
$descuento = (float)($rango['descuento'] ?? 0);
$etiqueta = trim($rango['etiqueta'] ?? '');
if ($cantidad_min <= 0 || $descuento <= 0 || $descuento >100) {
continue;
}
$cantidad_max = ($cantidad_max_raw === '' || strtolower($cantidad_max_raw) === 'ilimitado' || strtolower($cantidad_max_raw) === 'null')
? null
: (int)$cantidad_max_raw;
if ($cantidad_max !== null && $cantidad_max < $cantidad_min) {
continue;
}
$rango_limpio = [
'cantidad_min' =>$cantidad_min,
'cantidad_max' =>$cantidad_max,
'descuento' =>$descuento
];
if (!empty($etiqueta)) {
$rango_limpio['etiqueta'] = $etiqueta;
}
$rangos_validos[] = $rango_limpio;
}
usort($rangos_validos, fn($a, $b) =>$a['cantidad_min'] <=>$b['cantidad_min']);
return json_encode($rangos_validos);
}

// ============================================
// ⭐ MOTOR MATEMÁTICO DE LOGÍSTICA (FASE 3)
// ============================================
if (!function_exists('calcularTiempoEntregaRealista')) {
function calcularTiempoEntregaRealista($capacidad_diaria, $tasa_defectos, $distancia_km, $velocidad_kmh, $tiempo_aduanas, $cantidad_solicitada, $margen_seguridad = 2) {
$cantidad_ajustada = $cantidad_solicitada * (1 + $tasa_defectos);
$dias_produccion = ($capacidad_diaria >0) ? ceil($cantidad_ajustada / $capacidad_diaria) : 1;
$dias_transporte = ceil((($velocidad_kmh >0) ? ($distancia_km / $velocidad_kmh) : 0) / 8);
$tiempo_total = $dias_produccion + 1 + $dias_transporte + 1 + (int)$tiempo_aduanas + (int)$margen_seguridad;
return ['tiempo_total_dias' =>$tiempo_total, 'fecha_estimada' =>date('Y-m-d', strtotime("+{$tiempo_total} days"))];
}
}
if (!function_exists('aplicarDescuentoVolumen')) {
function aplicarDescuentoVolumen($precio_base, $cantidad, $descuentos_json) {
$descuentos = json_decode($descuentos_json, true) ?: [];
if (empty($descuentos)) return $precio_base;
foreach ($descuentos as $rango) {
$min = (int)($rango['cantidad_min'] ?? 0);
$max = $rango['cantidad_max'] ?? null;
$descuento_pct = (float)($rango['descuento'] ?? 0);
if ($cantidad >= $min && ($max === null || $cantidad <= $max)) {
return $precio_base * (1 - ($descuento_pct / 100));
}
}
return $precio_base;
}
}
if (!function_exists('calcularScorePropuesta')) {
function calcularScorePropuesta($propuestas, $cantidad_solicitada) {
if (empty($propuestas)) return [];
$max_precio = max(array_column($propuestas, 'precio_unitario'));
$max_dias = max(array_column($propuestas, 'dias_entrega'));
$scores = [];
foreach ($propuestas as $prop) {
$norm_precio = ($max_precio >0) ? ($prop['precio_unitario'] / $max_precio) : 1;
$norm_dias = ($max_dias >0) ? ($prop['dias_entrega'] / $max_dias) : 1;
$scores[] = array_merge($prop, ['score' =>round((0.5 * $norm_precio) + (0.5 * $norm_dias), 3), 'es_mejor' =>false]);
}
usort($scores, fn($a, $b) =>$a['score'] <=>$b['score']);
if (!empty($scores)) $scores[0]['es_mejor'] = true;
return $scores;
}
}

//  PROTECCIÓN: Solo SUPERADMIN y ADMIN
emxRequireRole(['SUPERADMIN', 'ADMIN']);
$rol_actual = emxRolActual();
$module = $_GET['module'] ?? 'dashboard';
$action = $_GET['action'] ?? null;
$msg = $_GET['msg'] ?? null;
$msg_type = $_GET['msg_type'] ?? 'success';
if ($module === 'dashboard' || $module === 'productos' || $module === 'producto_proveedores') {
verificarYGenerarSolicitudes($pdo);
}
// ============================================
// FUNCIÓN: DETECCIÓN DE FRAUDE EN DEVOLUCIONES
// ============================================
function detectarFraudeDevoluciones($pdo) {
$alertas_fraude = [];
$stmt = $pdo->query("
SELECT u.id, u.nombres, u.apellidos, u.email, COUNT(d.id) as total_devoluciones
FROM devoluciones d
JOIN usuarios u ON d.usuario_id = u.id
WHERE d.created_at >NOW() - INTERVAL '30 days'
GROUP BY u.id, u.nombres, u.apellidos, u.email
HAVING COUNT(d.id) >= 3
ORDER BY total_devoluciones DESC
");
$usuarios_sospechosos = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($usuarios_sospechosos as $usuario) {
$alertas_fraude[] = [
'tipo' =>'usuario_repetitivo',
'usuario_id' =>$usuario['id'],
'usuario_nombre' =>$usuario['nombres'] . ' ' . $usuario['apellidos'],
'usuario_email' =>$usuario['email'],
'total_devoluciones' =>$usuario['total_devoluciones'],
'mensaje' =>"El usuario {$usuario['nombres']} {$usuario['apellidos']} ha realizado {$usuario['total_devoluciones']} devoluciones en los últimos 30 días"
];
}
$stmt = $pdo->query("
SELECT u.id as usuario_id, u.nombres, u.apellidos,
dp.producto_id, p.nombre as producto_nombre,
COUNT(d.id) as total_devoluciones_producto
FROM devoluciones d
JOIN usuarios u ON d.usuario_id = u.id
JOIN pedidos ped ON d.pedido_id = ped.id
JOIN detalle_pedidos dp ON ped.id = dp.pedido_id
JOIN productos p ON dp.producto_id = p.id
WHERE d.created_at >NOW() - INTERVAL '90 days'
GROUP BY u.id, u.nombres, u.apellidos, dp.producto_id, p.nombre
HAVING COUNT(d.id) >= 2
ORDER BY total_devoluciones_producto DESC
");
$productos_repetidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($productos_repetidos as $item) {
$alertas_fraude[] = [
'tipo' =>'producto_repetido',
'usuario_id' =>$item['usuario_id'],
'usuario_nombre' =>$item['nombres'] . ' ' . $item['apellidos'],
'producto_id' =>$item['producto_id'],
'producto_nombre' =>$item['producto_nombre'],
'total_devoluciones' =>$item['total_devoluciones_producto'],
'mensaje' =>"El usuario {$item['nombres']} ha devuelto el producto '{$item['producto_nombre']}' {$item['total_devoluciones_producto']} veces en los últimos 90 días"
];
}
$stmt = $pdo->query("
SELECT d.id as devolucion_id, u.nombres, u.apellidos, p.nombre as producto_nombre,
d.created_at as fecha_devolucion,
ped.created_at as fecha_pedido
FROM devoluciones d
JOIN usuarios u ON d.usuario_id = u.id
JOIN pedidos ped ON d.pedido_id = ped.id
JOIN detalle_pedidos dp ON ped.id = dp.pedido_id
JOIN productos p ON dp.producto_id = p.id
WHERE d.created_at >NOW() - INTERVAL '30 days'
AND (d.created_at - ped.created_at) < INTERVAL '24 hours'
ORDER BY d.created_at DESC
");
$devoluciones_rapidas = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($devoluciones_rapidas) >= 3) {
$alertas_fraude[] = [
'tipo' =>'devoluciones_rapidas',
'total' =>count($devoluciones_rapidas),
'mensaje' =>"Se detectaron " . count($devoluciones_rapidas) . " devoluciones realizadas menos de 24 horas después de la compra en los últimos 30 días"
];
}
return $alertas_fraude;
}
// ============================================
// PROCESAMIENTO DE FORMULARIOS (POST)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
try {
// ==========================================
// DATOS DE EMPRESA PARA FACTURACIÓN
// ==========================================
if ($module === 'empresa' && $action === 'guardar') {
    $campos = [
        'razon_social' =>trim($_POST['razon_social'] ?? ''),
        'nombre_comercial' =>trim($_POST['nombre_comercial'] ?? ''),
        'ruc' =>preg_replace('/\D+/', '', $_POST['ruc'] ?? ''),
        'direccion_matriz' =>trim($_POST['direccion_matriz'] ?? ''),
        'telefono' =>trim($_POST['telefono'] ?? ''),
        'email' =>trim($_POST['email'] ?? ''),
        // Valores fijos/simulados para no cargar al admin con campos que no usará.
        'ambiente' =>'PRODUCCION',
        'establecimiento' =>preg_replace('/\D+/', '', $_POST['establecimiento'] ?? '001'),
        'punto_emision' =>preg_replace('/\D+/', '', $_POST['punto_emision'] ?? '001'),
        'moneda' =>'USD',
        'website' =>'',
        'regimen' =>trim($_POST['regimen'] ?? 'Documento generado electrónicamente por ElectroMax.'),
        'obligado_contabilidad' =>'false'
    ];
    if ($campos['razon_social'] === '' || $campos['nombre_comercial'] === '' || $campos['ruc'] === '' || $campos['direccion_matriz'] === '') {
        throw new Exception('Razón social, nombre comercial, RUC y dirección matriz son obligatorios.');
    }
    if (!preg_match('/^\d{10,13}$/', $campos['ruc'])) {
        throw new Exception('El RUC/identificación de empresa debe tener entre 10 y 13 dígitos para la simulación.');
    }
    if ($campos['email'] !== '' && !filter_var($campos['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El correo de facturación no tiene un formato válido.');
    }
    if ($campos['regimen'] === '') {
        $campos['regimen'] = 'Documento generado electrónicamente por ElectroMax.';
    }
    $campos['establecimiento'] = str_pad(substr($campos['establecimiento'] ?: '001', 0, 3), 3, '0', STR_PAD_LEFT);
    $campos['punto_emision'] = str_pad(substr($campos['punto_emision'] ?: '001', 0, 3), 3, '0', STR_PAD_LEFT);

    $empresaActual = emxEmpresaConfig($pdo);
    $logoUrl = $empresaActual['logo_url'] ?? 'assets/electromax_logo.png';
    $logoPdfUrl = $empresaActual['logo_pdf_url'] ?? 'assets/electromax_logo_pdf.jpg';
    if (!empty($_FILES['logo_empresa']['name'])) {
        $logoUrl = emxSubirArchivoSeguro('logo_empresa', 'uploads/empresa/logos', ['prefijo' =>'logo_empresa', 'max_bytes' =>4 * 1024 * 1024]);
    }
    if (!empty($_FILES['logo_pdf']['name'])) {
        $logoPdfUrl = emxSubirArchivoSeguro('logo_pdf', 'uploads/empresa/logos/pdf', ['prefijo' =>'logo_pdf', 'max_bytes' =>4 * 1024 * 1024, 'mime' =>['image/jpeg' =>'jpg']]);
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS empresa_config (
        id INTEGER PRIMARY KEY DEFAULT 1 CHECK (id = 1),
        razon_social VARCHAR(180) NOT NULL DEFAULT 'ELECTROMAX S.A.S.',
        nombre_comercial VARCHAR(120) NOT NULL DEFAULT 'ElectroMax',
        ruc VARCHAR(13) NOT NULL DEFAULT '0999999999001',
        direccion_matriz TEXT NOT NULL DEFAULT 'Babahoyo, Los Ríos, Ecuador',
        telefono VARCHAR(50) DEFAULT '04-273-0000',
        email VARCHAR(160) DEFAULT 'facturacion@electromax.com',
        logo_url TEXT DEFAULT 'assets/electromax_logo.png',
        logo_pdf_url TEXT DEFAULT 'assets/electromax_logo_pdf.jpg',
        ambiente VARCHAR(30) DEFAULT 'PRODUCCION',
        establecimiento VARCHAR(3) DEFAULT '001',
        punto_emision VARCHAR(3) DEFAULT '001',
        moneda VARCHAR(10) DEFAULT 'USD',
        website VARCHAR(180),
        regimen TEXT,
        obligado_contabilidad BOOLEAN DEFAULT FALSE,
        updated_at TIMESTAMP DEFAULT NOW()
    )");
    foreach ([
        'logo_pdf_url TEXT', "establecimiento VARCHAR(3) DEFAULT '001'", "punto_emision VARCHAR(3) DEFAULT '001'",
        "moneda VARCHAR(10) DEFAULT 'USD'", 'website VARCHAR(180)', 'regimen TEXT'
    ] as $ddl) {
        try { $pdo->exec('ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS ' . $ddl); } catch (Throwable $e) {}
    }

    $stmt = $pdo->prepare("
        INSERT INTO empresa_config (id, razon_social, nombre_comercial, ruc, direccion_matriz, telefono, email, logo_url, logo_pdf_url, ambiente, establecimiento, punto_emision, moneda, website, regimen, obligado_contabilidad, updated_at)
        VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON CONFLICT (id) DO UPDATE SET
            razon_social = EXCLUDED.razon_social,
            nombre_comercial = EXCLUDED.nombre_comercial,
            ruc = EXCLUDED.ruc,
            direccion_matriz = EXCLUDED.direccion_matriz,
            telefono = EXCLUDED.telefono,
            email = EXCLUDED.email,
            logo_url = EXCLUDED.logo_url,
            logo_pdf_url = EXCLUDED.logo_pdf_url,
            ambiente = EXCLUDED.ambiente,
            establecimiento = EXCLUDED.establecimiento,
            punto_emision = EXCLUDED.punto_emision,
            moneda = EXCLUDED.moneda,
            website = EXCLUDED.website,
            regimen = EXCLUDED.regimen,
            obligado_contabilidad = EXCLUDED.obligado_contabilidad,
            updated_at = NOW()
    ");
    $stmt->execute([
        $campos['razon_social'], $campos['nombre_comercial'], $campos['ruc'], $campos['direccion_matriz'],
        $campos['telefono'], $campos['email'], $logoUrl, $logoPdfUrl, $campos['ambiente'], $campos['establecimiento'],
        $campos['punto_emision'], $campos['moneda'], $campos['website'], $campos['regimen'], $campos['obligado_contabilidad']
    ]);
    header('Location: ?module=empresa&msg=' . urlencode('Datos de empresa actualizados para facturación.') . '&msg_type=success');
    exit();
}
// ==========================================
// GESTIONAR DEVOLUCIÓN CON FLUJO PROFESIONAL
// ==========================================
if ($module === 'devoluciones' && $action === 'actualizar_estado') {
$dev_id = $_POST['dev_id'] ?? null;
$accion_flujo = $_POST['accion_flujo'] ?? null;
if (!$dev_id || !$accion_flujo) throw new Exception('Debes seleccionar una acción válida.');
$pdo->beginTransaction();
$resultado_flujo = emxEjecutarAccionDevolucion($pdo, $_POST, $_SESSION['usuario_id'], $rol_actual);
$pdo->commit();
header('Location: ?module=devoluciones&msg=' . urlencode('Acción aplicada. Nuevo estado: ' . emxTextoEstado($resultado_flujo['estado'])) . '&msg_type=success');
exit();
}

// ==========================================
// GESTIONAR CASOS DE GARANTÍA
// ==========================================
if ($module === 'garantias' && $action === 'actualizar_garantia') {
    $caso_id = $_POST['caso_id'] ?? null;
    $accion_garantia = $_POST['accion_garantia'] ?? null;
    $comentario = trim($_POST['comentario_admin'] ?? '');
    if (!$caso_id || !$accion_garantia) throw new Exception('Datos de garantía inválidos.');
    $map = [
        'tomar_revision' =>'en_revision_tecnica',
        'aprobar_reparacion' =>'aprobado_reparacion',
        'aprobar_reemplazo' =>'aprobado_reemplazo',
        'enviar_proveedor' =>'garantia_proveedor',
        'rechazar' =>'rechazado',
        'cerrar' =>'cerrado'
    ];
    if (!isset($map[$accion_garantia])) throw new Exception('Acción de garantía no permitida.');
    $pdo->beginTransaction();
    $st = $pdo->prepare("SELECT * FROM garantia_casos WHERE id = ? FOR UPDATE");
    $st->execute([$caso_id]);
    $caso = $st->fetch(PDO::FETCH_ASSOC);
    if (!$caso) throw new Exception('Caso de garantía no encontrado.');
    $estadoNuevo = $map[$accion_garantia];
    $hist = json_decode($caso['historial_estados'] ?: '[]', true); if (!is_array($hist)) $hist = [];
    $hist[] = ['estado'=>$estadoNuevo, 'descripcion'=>($comentario ?: 'Acción registrada por admin.'), 'fecha'=>date('Y-m-d H:i:s'), 'icono'=>'fa-shield-halved', 'admin_id'=>$_SESSION['usuario_id']];
    $pdo->prepare("UPDATE garantia_casos SET estado = ?, tecnico_id = COALESCE(tecnico_id, ?), diagnostico = COALESCE(NULLIF(?, ''), diagnostico), resolucion = COALESCE(NULLIF(?, ''), resolucion), historial_estados = ?::jsonb, updated_at = NOW() WHERE id = ?")
        ->execute([$estadoNuevo, $_SESSION['usuario_id'], $comentario, $comentario, json_encode($hist, JSON_UNESCAPED_UNICODE), $caso_id]);
    if (function_exists('enviarNotificacionCliente')) {
        enviarNotificacionCliente($pdo, $caso['usuario_id'], 'garantia', 'Actualización de garantía', 'Tu caso de garantía cambió a: ' . $estadoNuevo, 'garantia.php?detalle_id=' . urlencode($caso['detalle_pedido_id']), 'garantia');
    }
    $pdo->commit();
    header('Location: ?module=garantias&msg=' . urlencode('Caso de garantía actualizado.') . '&msg_type=success');
    exit();
}

if ($module === 'pedidos' && $action === 'accion_masiva') {
$pedido_ids = $_POST['pedido_ids'] ?? [];
$accion_pedido = $_POST['accion_pedido'] ?? null;
$comentario = trim($_POST['comentario_admin'] ?? '');
if (empty($pedido_ids) || !$accion_pedido) throw new Exception('Datos inválidos.');
$pdo->beginTransaction();
$actualizados = 0;
$omitidos = 0;
foreach ($pedido_ids as $pedido_id) {
    try {
        emxEjecutarAccionPedido($pdo, $pedido_id, $accion_pedido, $_SESSION['usuario_id'], $comentario);
        $actualizados++;
    } catch (Exception $e) {
        $omitidos++;
    }
}
if ($actualizados === 0) {
    throw new Exception('Ningún pedido de este grupo podía ejecutar esa acción en su estado actual.');
}
$pdo->commit();
$msg_masivo = $actualizados . ' pedidos actualizados con flujo guiado';
if ($omitidos >0) $msg_masivo .= ' (' . $omitidos . ' omitidos por estado no compatible)';
header('Location: ?module=pedidos&msg=' . urlencode($msg_masivo) . '&msg_type=success');
exit();
}
if ($module === 'pedidos' && $action === 'update_status') {
$pedido_id = $_POST['pedido_id'] ?? null;
$accion_pedido = $_POST['accion_pedido'] ?? null;
$comentario = trim($_POST['comentario_admin'] ?? '');
if (!$pedido_id || !$accion_pedido) throw new Exception('Datos inválidos.');
$pdo->beginTransaction();
$resultado_pedido = emxEjecutarAccionPedido($pdo, $pedido_id, $accion_pedido, $_SESSION['usuario_id'], $comentario);
$pdo->commit();
header('Location: ?module=pedidos&msg=' . urlencode('Acción aplicada. Nuevo estado: ' . $resultado_pedido['estado']) . '&msg_type=success');
exit();
}
if ($module === 'sucursales' && ($action === 'create' || $action === 'update')) {
$modo = $action === 'update' ? 'editar' : 'crear';
$sucursal_id = trim($_POST['sucursal_id'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$latitud = (float)($_POST['latitud'] ?? 0);
$longitud = (float)($_POST['longitud'] ?? 0);
$horario = trim($_POST['horario_atencion'] ?? '');
$es_matriz = isset($_POST['es_matriz']) ? 'true' : 'false';
$is_active = isset($_POST['is_active']) ? 'true' : 'false';
if (empty($nombre) || empty($ciudad)) throw new Exception('Nombre y ciudad obligatorios.');
$pdo->beginTransaction();
if ($es_matriz === 'true') $pdo->prepare("UPDATE sucursales SET es_matriz = 'false'")->execute();
if ($modo === 'editar') {
$stmt = $pdo->prepare("UPDATE sucursales SET nombre=?, direccion=?, ciudad=?, telefono=?, email=?, latitud=?, longitud=?, horario_atencion=?, es_matriz=?::boolean, is_active=?::boolean WHERE id=?");
$stmt->execute([$nombre, $direccion, $ciudad, $telefono, $email, $latitud, $longitud, $horario, $es_matriz, $is_active, $sucursal_id]);
} else {
$stmt = $pdo->prepare("INSERT INTO sucursales (nombre, direccion, ciudad, telefono, email, latitud, longitud, horario_atencion, es_matriz, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?::boolean, ?::boolean)");
$stmt->execute([$nombre, $direccion, $ciudad, $telefono, $email, $latitud, $longitud, $horario, $es_matriz, $is_active]);
}
$pdo->commit();
header('Location: ?module=sucursales&msg=Sucursal+guardada&msg_type=success');
exit();
}
if ($module === 'productos' && ($action === 'create' || $action === 'update')) {
$modo = $action === 'update' ? 'editar' : 'crear';
$producto_id = trim($_POST['producto_id'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$categoria_id = trim($_POST['categoria_id'] ?? '');
$marca_id = trim($_POST['marca_id'] ?? '');
$precio = (float)($_POST['precio'] ?? 0);
$costo_unitario = (float)($_POST['costo_unitario'] ?? 0);
$iva = (float)($_POST['iva_porcentaje'] ?? 15);
$stock = max(0, (int)($_POST['stock'] ?? 0));
$stock_max = max(0, (int)($_POST['stock_max'] ?? 0));
$desc = trim($_POST['descripcion_corta'] ?? '');
$descuento = max(0, min(100, (float)($_POST['descuento_porcentaje'] ?? 0)));
$descuento_desde = !empty($_POST['descuento_desde']) ? $_POST['descuento_desde'] : null;
$descuento_hasta = !empty($_POST['descuento_hasta']) ? $_POST['descuento_hasta'] : null;
$punto_reorden = max(0, (int)($_POST['punto_reorden'] ?? 5));
$sku = trim($_POST['sku'] ?? '');
$modelo = trim($_POST['modelo'] ?? '');
$rangos_volumen_json = procesarRangosVolumen($_POST['rangos_volumen'] ?? []);

if ($nombre === '' || $categoria_id === '' || $marca_id === '') throw new Exception('Nombre, categoría y marca son obligatorios.');
if ($precio <= 0) throw new Exception('El precio base debe ser mayor a 0.');
if ($modo === 'editar' && empty($producto_id)) throw new Exception('ID de producto vacío.');

// Confirmar que la categoría y marca existan antes de guardar.
$stmt_check_cat = $pdo->prepare("SELECT 1 FROM categorias WHERE id = ? LIMIT 1");
$stmt_check_cat->execute([$categoria_id]);
if (!$stmt_check_cat->fetchColumn()) throw new Exception('La categoría seleccionada no existe.');
$stmt_check_marca = $pdo->prepare("SELECT 1 FROM marcas WHERE id = ? LIMIT 1");
$stmt_check_marca->execute([$marca_id]);
if (!$stmt_check_marca->fetchColumn()) throw new Exception('La marca seleccionada no existe.');

if ($sku === '' && emxDbColumnExists($pdo, 'productos', 'sku')) {
$stmt_cat_slug = $pdo->prepare("SELECT slug FROM categorias WHERE id = ?");
$stmt_cat_slug->execute([$categoria_id]);
$cat_slug = $stmt_cat_slug->fetchColumn();
$sku = generarSKUProfesional($cat_slug ?: 'gen');
}

$specs = [];
if (!empty($_POST['custom_specs_keys']) && is_array($_POST['custom_specs_keys'])) {
foreach ($_POST['custom_specs_keys'] as $i =>$key) {
$key = trim((string)$key);
$val = trim((string)($_POST['custom_specs_values'][$i] ?? ''));
if ($key !== '' && $val !== '') {
$slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]+/', '_', $key), '_'));
if ($slug === '') continue;
$specs[$slug] = (strpos($val, ',') !== false) ? array_values(array_filter(array_map('trim', explode(',', $val)), fn($v) =>$v !== '')) : $val;
}
}
}

$base_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre), '-'));
if ($base_slug === '') $base_slug = 'producto';
$slug_prod = $base_slug;
$contador_slug = 2;
while (true) {
$stmt_slug = $pdo->prepare("SELECT id FROM productos WHERE slug = ? AND deleted_at IS NULL LIMIT 1");
$stmt_slug->execute([$slug_prod]);
$slug_existente = $stmt_slug->fetchColumn();
if (!$slug_existente || ($modo === 'editar' && $slug_existente == $producto_id)) break;
$slug_prod = $base_slug . '-' . $contador_slug++;
}

$datos_producto = [
'nombre' =>$nombre,
'slug' =>$slug_prod,
'categoria_id' =>$categoria_id,
'marca_id' =>$marca_id,
'precio_base' =>$precio,
'stock_actual_global' =>$stock,
'descripcion_corta' =>$desc,
'especificaciones_tecnicas' =>json_encode($specs, JSON_UNESCAPED_UNICODE)
];

$opcionales = [
'costo_unitario' =>$costo_unitario,
'iva_porcentaje' =>$iva,
'stock_maximo' =>$stock_max,
'descuento_porcentaje' =>$descuento,
'descuento_desde' =>$descuento_desde,
'descuento_hasta' =>$descuento_hasta,
'punto_reorden' =>$punto_reorden,
'sku' =>$sku,
'modelo' =>$modelo,
'descuentos_volumen_rangos' =>$rangos_volumen_json
];
foreach ($opcionales as $col =>$val) {
if (emxDbColumnExists($pdo, 'productos', $col)) $datos_producto[$col] = $val;
}

$pdo->beginTransaction();
$old_stock = 0;
$old_descuento = 0;
$old_precio_base = 0;
if ($modo === 'editar') {
$stmt_old = $pdo->prepare("SELECT stock_actual_global, descuento_porcentaje, precio_base, descuento_desde, descuento_hasta FROM productos WHERE id = ?");
$stmt_old->execute([$producto_id]);
$old_product = $stmt_old->fetch(PDO::FETCH_ASSOC);
if (!$old_product) throw new Exception('Producto no encontrado.');
$old_stock = (int)($old_product['stock_actual_global'] ?? 0);
$old_descuento = (float)($old_product['descuento_porcentaje'] ?? 0);
$old_precio_base = (float)($old_product['precio_base'] ?? 0);
$sets = [];
$values = [];
foreach ($datos_producto as $col =>$val) {
$sets[] = $col . ' = ?' . (in_array($col, ['especificaciones_tecnicas', 'descuentos_volumen_rangos'], true) ? '::jsonb' : '');
$values[] = $val;
}
if (emxDbColumnExists($pdo, 'productos', 'updated_at')) $sets[] = 'updated_at = NOW()';
$values[] = $producto_id;
$stmt = $pdo->prepare('UPDATE productos SET ' . implode(', ', $sets) . ' WHERE id = ?');
$stmt->execute($values);
} else {
$cols = array_keys($datos_producto);
$placeholders = [];
$values = [];
foreach ($datos_producto as $col =>$val) {
$placeholders[] = '?' . (in_array($col, ['especificaciones_tecnicas', 'descuentos_volumen_rangos'], true) ? '::jsonb' : '');
$values[] = $val;
}
$stmt = $pdo->prepare('INSERT INTO productos (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ') RETURNING id');
$stmt->execute($values);
$producto_id = $stmt->fetchColumn();
}

$imagenes_agregadas = 0;
if (!empty($_FILES['imagenes']['name'][0]) && $_FILES['imagenes']['name'][0] !== '') {
$stmt = $pdo->prepare("SELECT COALESCE(MAX(orden),0) FROM producto_multimedia WHERE producto_id=?");
$stmt->execute([$producto_id]);
$orden = (int)$stmt->fetchColumn();
$destino_producto = emxCarpetaProductoUploads($pdo, $producto_id);
$rutas_img = emxSubirArchivosMultiplesSeguro('imagenes', $destino_producto, [
'prefijo' =>'prod_' . preg_replace('/[^a-z0-9]/i', '', $producto_id),
'max_bytes' =>12 * 1024 * 1024
]);
foreach ($rutas_img as $i =>$ruta) {
$pdo->prepare("INSERT INTO producto_multimedia (producto_id, tipo, url, orden) VALUES (?,?,?,?)")->execute([$producto_id, 'FOTO', $ruta, $orden + $i + 1]);
$imagenes_agregadas++;
}
}

if ($modo === 'editar' && !empty($_POST['eliminar_imagenes']) && is_array($_POST['eliminar_imagenes'])) {
foreach ($_POST['eliminar_imagenes'] as $img_id) {
if (empty($img_id) || trim($img_id) === '') continue;
$stmt = $pdo->prepare("SELECT url FROM producto_multimedia WHERE id=? AND producto_id=?");
$stmt->execute([$img_id, $producto_id]);
$url = $stmt->fetchColumn();
if ($url) {
$pdo->prepare("DELETE FROM producto_multimedia WHERE id=? AND producto_id=?")->execute([$img_id, $producto_id]);
if (file_exists($url)) @unlink($url);
}
}
}

$pdo->commit();

// Notificaciones de wishlist: se ejecutan en segundo plano y no cambian el mensaje visible.
// Esto cubre descuentos 0→10, 10→20, cambios de precio y regreso de stock.
if ($modo === 'editar' && function_exists('emxWishlistNotificarCambioProducto')) {
    try {
        $stmt_new_prod = $pdo->prepare("SELECT stock_actual_global, descuento_porcentaje, precio_base, descuento_desde, descuento_hasta FROM productos WHERE id = ?");
        $stmt_new_prod->execute([$producto_id]);
        $new_product_notif = $stmt_new_prod->fetch(PDO::FETCH_ASSOC) ?: [];
        emxWishlistNotificarCambioProducto($pdo, $producto_id, is_array($old_product ?? null) ? $old_product : [], $new_product_notif);
    } catch (Throwable $e) {
        error_log('Error notificando wishlist desde admin: ' . $e->getMessage());
    }
}

$msg_ok = $modo === 'editar' ? 'Producto actualizado' : 'Producto creado';
if ($imagenes_agregadas >0) $msg_ok .= ' con ' . $imagenes_agregadas . ' imagen(es)';
header('Location: ?module=productos&msg=' . urlencode($msg_ok) . '&msg_type=success');
exit();
}
if ($module === 'marcas' && ($action === 'create' || $action === 'update')) {
$modo = $action === 'update' ? 'editar' : 'crear';
$marca_id = trim($_POST['marca_id'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$pais = trim($_POST['pais'] ?? '');
if (empty($nombre)) throw new Exception('Nombre obligatorio');
if ($modo === 'editar' && empty($marca_id)) throw new Exception('ID vacío');
$logo_url = null;
if (!empty($_FILES['logo']['name'])) {
$logo_url = emxSubirArchivoSeguro('logo', emxCarpetaMarcaUploads($nombre), ['prefijo' =>'marca']);
}
if ($modo === 'editar') {
if ($logo_url) {
$stmt = $pdo->prepare("SELECT logo_url FROM marcas WHERE id = ?");
$stmt->execute([$marca_id]);
$old_logo = $stmt->fetchColumn();
if ($old_logo && file_exists($old_logo)) unlink($old_logo);
$stmt = $pdo->prepare("UPDATE marcas SET nombre=?, pais_origen=?, logo_url=? WHERE id=?");
$stmt->execute([$nombre, $pais, $logo_url, $marca_id]);
} else {
$stmt = $pdo->prepare("UPDATE marcas SET nombre=?, pais_origen=? WHERE id=?");
$stmt->execute([$nombre, $pais, $marca_id]);
}
} else {
$stmt = $pdo->prepare("INSERT INTO marcas (nombre, pais_origen, logo_url) VALUES (?, ?, ?) RETURNING id");
$stmt->execute([$nombre, $pais, $logo_url]);
}
header('Location: ?module=marcas&msg=' . urlencode($modo === 'editar' ? 'Marca actualizada' : 'Marca creada') . '&msg_type=success');
exit();
}
if ($module === 'categorias' && ($action === 'create' || $action === 'update')) {
$modo = $action === 'update' ? 'editar' : 'crear';
$categoria_id = trim($_POST['categoria_id'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
if (empty($nombre)) throw new Exception('Nombre obligatorio');
if ($modo === 'editar' && empty($categoria_id)) throw new Exception('ID vacío');
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre), '-'));
if ($modo === 'editar') {
$stmt = $pdo->prepare("UPDATE categorias SET nombre=?, slug=? WHERE id=?");
$stmt->execute([$nombre, $slug, $categoria_id]);
} else {
$stmt = $pdo->prepare("INSERT INTO categorias (nombre, slug, filtros_disponibles) VALUES (?, ?, '[]'::jsonb) RETURNING id");
$stmt->execute([$nombre, $slug]);
}
header('Location: ?module=categorias&msg=' . urlencode($modo === 'editar' ? 'Categoría actualizada' : 'Categoría creada') . '&msg_type=success');
exit();
}
if ($module === 'usuarios' && ($action === 'create' || $action === 'update')) {
$modo = $action === 'update' ? 'editar' : 'crear';
$user_id = trim($_POST['user_id'] ?? '');
$nombres = trim($_POST['nombres'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$cedula = trim($_POST['cedula_ruc'] ?? '');
$rol_id = trim($_POST['rol_id'] ?? '');
$password = trim($_POST['password'] ?? '');
$tiene_badge_val = isset($_POST['tiene_badge_verificado']) ? 'true' : 'false';
if (empty($nombres) || empty($apellidos) || empty($email) || empty($rol_id)) throw new Exception('Campos obligatorios faltantes');
if ($modo === 'crear') {
if (empty($password)) throw new Exception('Contraseña obligatoria');
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND deleted_at IS NULL");
$stmt->execute([$email]);
if ($stmt->fetch()) throw new Exception('Correo ya registrado');
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO usuarios (rol_id, nombres, apellidos, email, password_hash, telefono, cedula_ruc, tiene_badge_verificado, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?::boolean, 'true') RETURNING id");
$stmt->execute([$rol_id, $nombres, $apellidos, $email, $hash, $telefono, $cedula, $tiene_badge_val]);
} else {
if ($modo === 'editar' && empty($user_id)) throw new Exception('ID vacío');
if (!empty($password)) {
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE usuarios SET rol_id=?, nombres=?, apellidos=?, email=?, password_hash=?, telefono=?, cedula_ruc=?, tiene_badge_verificado=?::boolean WHERE id=?");
$stmt->execute([$rol_id, $nombres, $apellidos, $email, $hash, $telefono, $cedula, $tiene_badge_val, $user_id]);
} else {
$stmt = $pdo->prepare("UPDATE usuarios SET rol_id=?, nombres=?, apellidos=?, email=?, telefono=?, cedula_ruc=?, tiene_badge_verificado=?::boolean WHERE id=?");
$stmt->execute([$rol_id, $nombres, $apellidos, $email, $telefono, $cedula, $tiene_badge_val, $user_id]);
}
}
header('Location: ?module=usuarios&msg=' . urlencode($modo === 'editar' ? 'Usuario actualizado' : 'Usuario creado') . '&msg_type=success');
exit();
}
if ($module === 'planes' && ($action === 'create' || $action === 'update')) {
$modo = $action === 'update' ? 'editar' : 'crear';
$plan_id = trim($_POST['plan_id'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$precio = $_POST['precio_mensual'] ?? 0;
$descripcion = trim($_POST['descripcion'] ?? '');
$orden = $_POST['orden'] ?? 1;
$es_prime_val = isset($_POST['es_prime']) ? 'true' : 'false';
$is_active_val = isset($_POST['is_active']) ? 'true' : 'false';
$beneficios = [];
if (!empty($_POST['beneficio_tipo'])) {
foreach ($_POST['beneficio_tipo'] as $index =>$tipo) {
if (!empty($tipo)) {
$beneficios[] = [
'tipo' =>trim($tipo),
'valor' =>trim($_POST['beneficio_valor'][$index] ?? '0'),
'unidad' =>trim($_POST['beneficio_unidad'][$index] ?? 'siempre'),
'descripcion' =>trim($_POST['beneficio_desc'][$index] ?? '')
];
}
}
}
if (empty($nombre) || empty($slug)) throw new Exception('Nombre y slug obligatorios');
if ($modo === 'editar') {
if (empty($plan_id)) throw new Exception('ID vacío');
$stmt = $pdo->prepare("UPDATE planes SET nombre=?, slug=?, precio_mensual=?, descripcion=?, beneficios=?::jsonb, es_prime=?::boolean, orden=?, is_active=?::boolean WHERE id=?");
$stmt->execute([$nombre, $slug, $precio, $descripcion, json_encode($beneficios), $es_prime_val, $orden, $is_active_val, $plan_id]);
} else {
$stmt = $pdo->prepare("INSERT INTO planes (nombre, slug, precio_mensual, descripcion, beneficios, es_prime, orden, is_active) VALUES (?, ?, ?, ?, ?::jsonb, ?::boolean, ?, ?::boolean) RETURNING id");
$stmt->execute([$nombre, $slug, $precio, $descripcion, json_encode($beneficios), $es_prime_val, $orden, $is_active_val]);
}
header('Location: ?module=planes&msg=' . urlencode($modo === 'editar' ? 'Plan actualizado' : 'Plan creado') . '&msg_type=success');
exit();
}
if ($module === 'producto_proveedores' && $action === 'asignar') {
$producto_id = $_POST['producto_id'] ?? null;
$proveedor_id = $_POST['proveedor_id'] ?? null;
if (!$producto_id || !$proveedor_id) throw new Exception('Datos inválidos');
$pdo->prepare("INSERT INTO producto_proveedor (producto_id, proveedor_id) VALUES (?, ?)")->execute([$producto_id, $proveedor_id]);
header('Location: ?module=producto_proveedores&msg=Proveedor+asignado&msg_type=success');
exit();
}
if ($module === 'producto_proveedores' && $action === 'aprobar_cotizacion') {
$cotizacion_id = $_POST['cotizacion_id'] ?? null;
if (!$cotizacion_id) throw new Exception('Cotización no válida');
aprobarCotizacion($pdo, $cotizacion_id);
header('Location: ?module=producto_proveedores&msg=Cotización+aprobada&msg_type=success');
exit();
}

// ⭐ NUEVO: SOLICITUD MANUAL DE REABASTECIMIENTO
if ($module === 'producto_proveedores' && $action === 'solicitar_reabastecimiento') {
$producto_id = $_POST['producto_id'] ?? null;
$cantidad_necesaria = (int)($_POST['cantidad_necesaria'] ?? 0);
$fecha_limite = !empty($_POST['fecha_limite']) ? $_POST['fecha_limite'] : date('Y-m-d', strtotime('+7 days'));
$notas_admin = trim($_POST['notas_admin'] ?? '');
if (!$producto_id || $cantidad_necesaria <= 0) throw new Exception('Datos inválidos');
$stmt_prov = $pdo->prepare("SELECT COUNT(*) FROM producto_proveedor WHERE producto_id = ?");
$stmt_prov->execute([$producto_id]);
$total_proveedores = (int)$stmt_prov->fetchColumn();
if ($total_proveedores === 0) throw new Exception('Este producto no tiene proveedores asignados.');
$stmt_prod = $pdo->prepare("SELECT nombre FROM productos WHERE id = ?");
$stmt_prod->execute([$producto_id]);
$producto_nombre = $stmt_prod->fetchColumn();
$pdo->beginTransaction();
// Antes de crear una nueva solicitud manual para el mismo producto,
// cerramos las solicitudes activas anteriores para evitar duplicados aprobables.
if (function_exists('emxCerrarSolicitudesDuplicadasProducto')) {
    emxCerrarSolicitudesDuplicadasProducto(
        $pdo,
        $producto_id,
        null,
        'Reemplazada automáticamente porque el administrador creó una nueva solicitud manual de reabastecimiento.'
    );
}
$tiene_notas_admin = function_exists('emxDbColumnExists') ? emxDbColumnExists($pdo, 'solicitudes_reabastecimiento', 'notas_admin') : false;
$tiene_sucursal_matriz = function_exists('emxDbColumnExists') ? emxDbColumnExists($pdo, 'solicitudes_reabastecimiento', 'sucursal_matriz_id') : false;
$sucursal_matriz_id = null;
if ($tiene_sucursal_matriz) {
    $stmt_matriz = $pdo->query("SELECT id FROM sucursales WHERE es_matriz = TRUE LIMIT 1");
    $sucursal_matriz_id = $stmt_matriz->fetchColumn() ?: null;
}

if ($tiene_notas_admin && $tiene_sucursal_matriz) {
    $stmt_sol = $pdo->prepare("INSERT INTO solicitudes_reabastecimiento (producto_id, sucursal_matriz_id, cantidad_necesaria, fecha_limite, estado, notas_admin, created_at) VALUES (?, ?, ?, ?, 'pendiente', ?, NOW()) RETURNING id");
    $stmt_sol->execute([$producto_id, $sucursal_matriz_id, $cantidad_necesaria, $fecha_limite, $notas_admin]);
} elseif ($tiene_notas_admin) {
    $stmt_sol = $pdo->prepare("INSERT INTO solicitudes_reabastecimiento (producto_id, cantidad_necesaria, fecha_limite, estado, notas_admin, created_at) VALUES (?, ?, ?, 'pendiente', ?, NOW()) RETURNING id");
    $stmt_sol->execute([$producto_id, $cantidad_necesaria, $fecha_limite, $notas_admin]);
} elseif ($tiene_sucursal_matriz) {
    $stmt_sol = $pdo->prepare("INSERT INTO solicitudes_reabastecimiento (producto_id, sucursal_matriz_id, cantidad_necesaria, fecha_limite, estado, created_at) VALUES (?, ?, ?, ?, 'pendiente', NOW()) RETURNING id");
    $stmt_sol->execute([$producto_id, $sucursal_matriz_id, $cantidad_necesaria, $fecha_limite]);
} else {
    $stmt_sol = $pdo->prepare("INSERT INTO solicitudes_reabastecimiento (producto_id, cantidad_necesaria, fecha_limite, estado, created_at) VALUES (?, ?, ?, 'pendiente', NOW()) RETURNING id");
    $stmt_sol->execute([$producto_id, $cantidad_necesaria, $fecha_limite]);
}
$solicitud_id = $stmt_sol->fetchColumn();
// En modo simulado se generan cotizaciones inmediatas para que el admin compare proveedores.
$cotizaciones_generadas = function_exists('emxGenerarCotizacionesSimuladas') ? emxGenerarCotizacionesSimuladas($pdo, $solicitud_id, $producto_id, $cantidad_necesaria) : 0;
$stmt_provs = $pdo->prepare("SELECT pp.proveedor_id, u.nombres, u.apellidos, u.email FROM producto_proveedor pp JOIN usuarios u ON pp.proveedor_id = u.id WHERE pp.producto_id = ?");
$stmt_provs->execute([$producto_id]);
$proveedores = $stmt_provs->fetchAll(PDO::FETCH_ASSOC);
$proveedores_notificados = 0;
foreach ($proveedores as $prov) {
enviarNotificacionCliente($pdo, $prov['proveedor_id'], 'solicitud_reabastecimiento', ' Nueva solicitud de reabastecimiento', "ElectroMax necesita {$cantidad_necesaria} unidades de '{$producto_nombre}'. Fecha límite: " . date('d/m/Y', strtotime($fecha_limite)) . ". Por favor, envía tu cotización desde tu portal de proveedor.", 'proveedor.php?seccion=solicitudes', 'solicitud');
$proveedores_notificados++;
}
$pdo->commit();
header('Location: ?module=producto_proveedores&msg=Solicitud+creada+y+solicitudes+anteriores+cerradas.+Proveedores+notificados:+' . $proveedores_notificados . '+Cotizaciones+calculadas:+' . ($cotizaciones_generadas ?? 0) . '&msg_type=success');
exit();
}

if ($module === 'banners' && $action === 'save_section') {
$nombre = trim($_POST['nombre'] ?? '');
$tipo = trim($_POST['tipo'] ?? 'grid_2');
$posicion = (int)($_POST['posicion'] ?? 1);
$is_active_val = isset($_POST['is_active']) ? 'true' : 'false';
if (empty($nombre)) throw new Exception('Nombre requerido');
$pdo->prepare("INSERT INTO page_sections (nombre, tipo, posicion, is_active) VALUES (?, ?, ?, ?::boolean)")->execute([$nombre, $tipo, $posicion, $is_active_val]);
header('Location: ?module=banners&msg=Sección+creada&msg_type=success');
exit();
}
if ($module === 'banners' && $action === 'update_section') {
$section_id = trim($_POST['section_id'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$tipo = trim($_POST['tipo'] ?? 'grid_2');
$posicion = (int)($_POST['posicion'] ?? 1);
$is_active_val = isset($_POST['is_active']) ? 'true' : 'false';
$pdo->prepare("UPDATE page_sections SET nombre=?, tipo=?, posicion=?, is_active=?::boolean WHERE id=?")->execute([$nombre, $tipo, $posicion, $is_active_val, $section_id]);
header('Location: ?module=banners&msg=Sección+actualizada&msg_type=success');
exit();
}
if ($module === 'banners' && ($action === 'save_banner' || $action === 'update_banner')) {
$banner_id = trim($_POST['banner_id'] ?? '');
$section_id = trim($_POST['section_id'] ?? '');
$titulo = trim($_POST['titulo'] ?? '');
$subtitulo = trim($_POST['subtitulo'] ?? '');
$redirect_type = trim($_POST['redirect_type'] ?? 'custom');
$redirect_target = trim($_POST['redirect_target'] ?? '');
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? null;
$orden = (int)($_POST['orden'] ?? 1);
$is_active_val = isset($_POST['is_active']) ? 'true' : 'false';
$pdo->beginTransaction();
try {
if ($action === 'update_banner') {
$stmt = $pdo->prepare("SELECT imagen_url FROM banners_promocionales WHERE id = ?");
$stmt->execute([$banner_id]);
$imagen_url = $stmt->fetchColumn();
if (!empty($_FILES['imagen']['name'])) {
$ruta = emxSubirArchivoSeguro('imagen', emxCarpetaBannerUploads($pdo, $section_id), ['prefijo' =>'banner']);
if ($ruta) {
if ($imagen_url && file_exists($imagen_url)) unlink($imagen_url);
$imagen_url = $ruta;
}
}
$pdo->prepare("UPDATE banners_promocionales SET section_id=?, titulo=?, subtitulo=?, imagen_url=?, redirect_type=?, redirect_target=?, fecha_inicio=?, fecha_fin=?, orden=?, is_active=?::boolean WHERE id=?")->execute([$section_id, $titulo, $subtitulo, $imagen_url, $redirect_type, $redirect_target, $fecha_inicio, $fecha_fin, $orden, $is_active_val, $banner_id]);
$pdo->prepare("DELETE FROM banner_productos_rel WHERE banner_id = ?")->execute([$banner_id]);
} else {
if (empty($section_id)) throw new Exception('Debes seleccionar una sección');
if (empty($_FILES['imagen']['name'])) throw new Exception('Debes subir una imagen');
$ruta = emxSubirArchivoSeguro('imagen', emxCarpetaBannerUploads($pdo, $section_id), ['prefijo' =>'banner']);
if (!$ruta) throw new Exception('Error al subir la imagen');
$stmt = $pdo->prepare("INSERT INTO banners_promocionales (section_id, titulo, subtitulo, imagen_url, redirect_type, redirect_target, fecha_inicio, fecha_fin, orden, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?::boolean) RETURNING id");
$stmt->execute([$section_id, $titulo, $subtitulo, $ruta, $redirect_type, $redirect_target, $fecha_inicio, $fecha_fin, $orden, $is_active_val]);
$banner_id = $stmt->fetchColumn();
}
if ($redirect_type === 'discount' && !empty($_POST['productos_seleccionados']) && is_array($_POST['productos_seleccionados'])) {
$descuento_campana = (float)$redirect_target;
if ($descuento_campana <= 0 || $descuento_campana >100) throw new Exception('Descuento inválido (1-100).');
$stmt_rel = $pdo->prepare("INSERT INTO banner_productos_rel (banner_id, producto_id, descuento_asignado) VALUES (?, ?, ?)");
$stmt_update_prod = $pdo->prepare("UPDATE productos SET descuento_porcentaje = ?, descuento_desde = ?, descuento_hasta = ? WHERE id = ?");
foreach ($_POST['productos_seleccionados'] as $prod_id) {
$stmt_rel->execute([$banner_id, $prod_id, $descuento_campana]);
$stmt_update_prod->execute([$descuento_campana, $fecha_inicio, $fecha_fin, $prod_id]);
}
}
elseif ($redirect_type === 'category' && !empty($redirect_target)) {
$parts = explode('|', $redirect_target);
$categoria_slug = $parts[0] ?? '';
$descuento_categoria = isset($parts[1]) ? (float)$parts[1] : 0;
if (empty($categoria_slug)) throw new Exception('Categoría inválida.');
if ($descuento_categoria <= 0 || $descuento_categoria >100) throw new Exception('Descuento inválido (1-100).');
$stmt_cat = $pdo->prepare("SELECT id FROM categorias WHERE slug = ? AND is_active = 'true'");
$stmt_cat->execute([$categoria_slug]);
$categoria_id = $stmt_cat->fetchColumn();
if (!$categoria_id) throw new Exception('La categoría no existe.');
$stmt_prod = $pdo->prepare("SELECT id FROM productos WHERE categoria_id = ? AND deleted_at IS NULL AND is_active = 'true'");
$stmt_prod->execute([$categoria_id]);
$productos_cat = $stmt_prod->fetchAll(PDO::FETCH_COLUMN);
if (empty($productos_cat)) throw new Exception('Sin productos activos.');
$stmt_rel = $pdo->prepare("INSERT INTO banner_productos_rel (banner_id, producto_id, descuento_asignado) VALUES (?, ?, ?)");
$stmt_update_prod = $pdo->prepare("UPDATE productos SET descuento_porcentaje = ?, descuento_desde = ?, descuento_hasta = ? WHERE id = ?");
foreach ($productos_cat as $prod_id) {
$stmt_rel->execute([$banner_id, $prod_id, $descuento_categoria]);
$stmt_update_prod->execute([$descuento_categoria, $fecha_inicio, $fecha_fin, $prod_id]);
}
}
elseif ($redirect_type === 'prime_exclusive' && !empty($_POST['prime_productos_seleccionados']) && is_array($_POST['prime_productos_seleccionados'])) {
$descuento_prime = isset($_POST['redirect_target_prime']) ? (float)$_POST['redirect_target_prime'] : 0;
if ($descuento_prime <= 0 || $descuento_prime >100) throw new Exception('Descuento Prime inválido (1-100).');
$stmt_rel = $pdo->prepare("INSERT INTO banner_productos_rel (banner_id, producto_id, descuento_asignado) VALUES (?, ?, ?)");
$stmt_update_prod = $pdo->prepare("UPDATE productos SET descuento_porcentaje = ?, descuento_desde = ?, descuento_hasta = ? WHERE id = ?");
foreach ($_POST['prime_productos_seleccionados'] as $prod_id) {
$stmt_rel->execute([$banner_id, $prod_id, $descuento_prime]);
$stmt_update_prod->execute([$descuento_prime, $fecha_inicio, $fecha_fin, $prod_id]);
}
}
$pdo->commit();
$msg_text = $action === 'update_banner' ? 'Banner actualizado' : 'Banner creado';
header('Location: ?module=banners&msg=' . urlencode($msg_text) . '&msg_type=success');
exit();
} catch (Exception $e) {
$pdo->rollBack();
header('Location: ?module=banners&msg=' . urlencode($e->getMessage()) . '&msg_type=error');
exit();
}
}
if ($module === 'planes' && $action === 'delete' && isset($_GET['id'])) {
$pdo->prepare("UPDATE planes SET is_active = 'false' WHERE id = ?")->execute([$_GET['id']]);
header('Location: ?module=planes&msg=Plan+desactivado&msg_type=success');
exit();
}
if ($module === 'banners' && $action === 'delete_section' && isset($_GET['id'])) {
$pdo->prepare("DELETE FROM page_sections WHERE id = ?")->execute([$_GET['id']]);
header('Location: ?module=banners&msg=Sección+eliminada&msg_type=success');
exit();
}
if ($module === 'banners' && $action === 'delete_banner' && isset($_GET['id'])) {
$stmt = $pdo->prepare("SELECT imagen_url FROM banners_promocionales WHERE id = ?");
$stmt->execute([$_GET['id']]);
$img = $stmt->fetchColumn();
if ($img && file_exists($img)) unlink($img);
$pdo->prepare("DELETE FROM banners_promocionales WHERE id = ?")->execute([$_GET['id']]);
header('Location: ?module=banners&msg=Banner+eliminado&msg_type=success');
exit();
}
} catch(Exception $e) {
if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) { $pdo->rollBack(); }
header('Location: ?module=' . $module . '&msg=' . urlencode($e->getMessage()) . '&msg_type=error');
exit();
}
}
if ($action === 'delete' && isset($_GET['id'])) {
try {
if ($module === 'productos') $pdo->prepare("UPDATE productos SET deleted_at = NOW() WHERE id = ?")->execute([$_GET['id']]);
elseif ($module === 'marcas' || $module === 'categorias') $pdo->prepare("UPDATE " . $module . " SET is_active = 'false' WHERE id = ?")->execute([$_GET['id']]);
elseif ($module === 'usuarios') $pdo->prepare("UPDATE usuarios SET deleted_at = NOW() WHERE id = ?")->execute([$_GET['id']]);
else
if ($module === 'garantias') {
    try {
        $stmt_gar = $pdo->query("
            SELECT gc.*, p.nombre AS producto_nombre, ped.nombre_cliente, ped.email, ped.created_at AS fecha_pedido
            FROM garantia_casos gc
            LEFT JOIN productos p ON p.id = gc.producto_id
            LEFT JOIN pedidos ped ON ped.id = gc.pedido_id
            ORDER BY gc.created_at DESC
        ");
        $garantias_casos = $stmt_gar->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { $garantias_casos = []; }
}

if ($module === 'sucursales') {
$stmt = $pdo->prepare("SELECT es_matriz FROM sucursales WHERE id = ?");
$stmt->execute([$_GET['id']]);
if ($stmt->fetchColumn()) {
header('Location: ?module=sucursales&msg=No+se+puede+eliminar+la+Matriz&msg_type=error');
exit();
}
$pdo->prepare("UPDATE sucursales SET is_active = 'false' WHERE id = ?")->execute([$_GET['id']]);
}
header('Location: ?module=' . $module . '&msg=Eliminado+exitosamente&msg_type=success');
exit();
} catch(PDOException $e) {
header('Location: ?module=' . $module . '&msg=' . urlencode($e->getMessage()) . '&msg_type=error');
exit();
}
}
// ============================================
// CARGAR DATOS
// ============================================
$productos = $categorias = $marcas = $usuarios = $roles = $planes = $pedidos = $sucursales_list = $devoluciones_list = $clientes_lista = [];
$stats = ['prod' =>0, 'cat' =>0, 'mar' =>0, 'stock_bajo' =>0, 'valor_inventario' =>0, 'pedidos_pendientes' =>0];
$clientes_vip = 0;
$filtro_tipo_cliente = $_GET['tipo_cliente'] ?? '';
$empresa_config = [];
if ($module === 'empresa') {
    $empresa_config = emxEmpresaConfig($pdo);
    try {
        $empresa_metricas = [
            'facturas' =>(int)$pdo->query("SELECT COUNT(*) FROM facturas")->fetchColumn(),
            'notas_credito' =>(int)$pdo->query("SELECT COUNT(*) FROM notas_credito")->fetchColumn(),
            'correos_pendientes' =>(int)$pdo->query("SELECT COUNT(*) FROM email_outbox WHERE estado = 'pendiente'")->fetchColumn()
        ];
    } catch (Throwable $e) {
        $empresa_metricas = ['facturas' =>0, 'notas_credito' =>0, 'correos_pendientes' =>0];
    }
}

if ($module === 'productos' || $module === 'dashboard') {
$stmt = $pdo->query("SELECT p.*, c.nombre as cat, m.nombre as marca, pm.url as img FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id LEFT JOIN marcas m ON p.marca_id = m.id LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.orden = 1 WHERE p.deleted_at IS NULL ORDER BY p.created_at DESC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stats['prod'] = count($productos);
$stats['stock_bajo'] = count(array_filter($productos, fn($p) =>$p['stock_actual_global'] <= ($p['punto_reorden'] ?? 5)));
$stats['valor_inventario'] = array_sum(array_map(fn($p) =>$p['precio_base'] * $p['stock_actual_global'], $productos));
}
if ($module === 'categorias' || $module === 'productos') {
$categorias = $pdo->query("SELECT * FROM categorias WHERE is_active = 'true' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$stats['cat'] = count($categorias);
}
if ($module === 'marcas' || $module === 'productos') {
$marcas = $pdo->query("SELECT * FROM marcas WHERE is_active = 'true' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$stats['mar'] = count($marcas);
}
if ($module === 'usuarios' || $module === 'dashboard') {
$roles = $pdo->query("SELECT id, nombre FROM roles ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
}
if ($module === 'usuarios') {
$stmt = $pdo->query("SELECT u.*, r.nombre as rol_nombre FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id WHERE u.deleted_at IS NULL ORDER BY u.created_at DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($module === 'planes') {
$planes = $pdo->query("SELECT * FROM planes ORDER BY orden")->fetchAll(PDO::FETCH_ASSOC);
}

// ⭐ NUEVO: CARGA DE DATOS DE CLIENTES
if ($module === 'clientes') {
$cliente_detalle_id = $_GET['cliente_id'] ?? null;
if ($cliente_detalle_id) {
$stmt_cli = $pdo->prepare("SELECT u.*, r.nombre as rol_nombre, p.nombre as plan_nombre, p.es_prime FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id LEFT JOIN planes p ON u.plan_id = p.id WHERE u.id = ?");
$stmt_cli->execute([$cliente_detalle_id]);
$cliente_detalle = $stmt_cli->fetch(PDO::FETCH_ASSOC);
$stmt_ped = $pdo->prepare("SELECT p.*, (SELECT COUNT(*) FROM detalle_pedidos WHERE pedido_id = p.id) as total_items FROM pedidos p WHERE p.usuario_id = ? ORDER BY p.created_at DESC");
$stmt_ped->execute([$cliente_detalle_id]);
$pedidos_cliente = $stmt_ped->fetchAll(PDO::FETCH_ASSOC);
$total_gastado = array_sum(array_column($pedidos_cliente, 'total'));
$total_pedidos = count($pedidos_cliente);
//  CORREGIDO: Ordenar por total DESC para encontrar la compra más grande
$compra_mas_grande = null;
if (!empty($pedidos_cliente)) {
usort($pedidos_cliente, function($a, $b) {
return (float)($b['total'] ?? 0) <=>(float)($a['total'] ?? 0);
});
$compra_mas_grande = $pedidos_cliente[0];
}
$primera_compra = !empty($pedidos_cliente) ? end($pedidos_cliente) : null;
$ultima_compra = !empty($pedidos_cliente) ? $pedidos_cliente[0] : null;
$stmt_prods = $pdo->prepare("SELECT pr.nombre, SUM(dp.cantidad) as total_comprado, COUNT(DISTINCT dp.pedido_id) as veces_comprado FROM detalle_pedidos dp JOIN pedidos p ON dp.pedido_id = p.id JOIN productos pr ON dp.producto_id = pr.id WHERE p.usuario_id = ? GROUP BY pr.nombre ORDER BY total_comprado DESC LIMIT 10");
$stmt_prods->execute([$cliente_detalle_id]);
$productos_favoritos = $stmt_prods->fetchAll(PDO::FETCH_ASSOC);
$etiqueta_cliente = 'Ocasional'; $color_etiqueta = 'bg-slate-100 text-slate-700';
if ($total_pedidos >= 10) { $etiqueta_cliente = 'VIP'; $color_etiqueta = 'bg-amber-100 text-amber-800'; }
elseif ($total_pedidos >= 5) { $etiqueta_cliente = 'Frecuente'; $color_etiqueta = 'bg-emerald-100 text-emerald-700'; }
elseif ($total_pedidos >= 2) { $etiqueta_cliente = 'Regular'; $color_etiqueta = 'bg-blue-100 text-blue-700'; }
} else {
$stmt_clientes = $pdo->query("SELECT u.id, u.nombres, u.apellidos, u.email, u.plan_id, p.nombre as plan_nombre, p.es_prime, COUNT(DISTINCT ped.id) as total_pedidos, COALESCE(SUM(ped.total), 0) as total_gastado, MAX(ped.created_at) as ultima_compra FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id LEFT JOIN planes p ON u.plan_id = p.id LEFT JOIN pedidos ped ON u.id = ped.usuario_id WHERE r.nombre = 'CLIENTE' AND u.deleted_at IS NULL GROUP BY u.id, u.nombres, u.apellidos, u.email, u.plan_id, p.nombre, p.es_prime ORDER BY total_gastado DESC");
$clientes_lista = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
$clientes_vip = count(array_filter($clientes_lista, fn($c) =>$c['total_pedidos'] >= 10));
}
}

if ($module === 'pedidos' || $module === 'dashboard') {
$filtro_estado = $_GET['filtro'] ?? '';
$query_pedidos = "SELECT p.id, p.nombre_cliente, p.email, p.total, p.estado, p.created_at, p.sucursal_asignada_id, p.fecha_estimada_entrega, p.ciudad, s.nombre as sucursal_nombre, u.plan_id, pl.nombre as plan_nombre, pl.es_prime, (SELECT COUNT(*) FROM pedidos WHERE estado = 'Pago confirmado') as total_pendientes FROM pedidos p LEFT JOIN sucursales s ON p.sucursal_asignada_id = s.id LEFT JOIN usuarios u ON p.usuario_id = u.id LEFT JOIN planes pl ON u.plan_id = pl.id";
$where_conditions = []; $params_pedidos = [];
if ($filtro_estado) { $where_conditions[] = "p.estado = ?"; $params_pedidos[] = $filtro_estado; }
if ($filtro_tipo_cliente === 'con_plan') { $where_conditions[] = "u.plan_id IS NOT NULL"; }
elseif ($filtro_tipo_cliente === 'sin_plan') { $where_conditions[] = "u.plan_id IS NULL"; }
elseif ($filtro_tipo_cliente === 'prime') { $where_conditions[] = "pl.es_prime = TRUE"; }
if (!empty($where_conditions)) {
$stmt_pedidos = $pdo->prepare($query_pedidos . " WHERE " . implode(' AND ', $where_conditions) . " ORDER BY p.created_at DESC");
$stmt_pedidos->execute($params_pedidos);
} else {
$stmt_pedidos = $pdo->query($query_pedidos . " ORDER BY p.created_at DESC");
}
$pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);
if (!empty($pedidos)) $stats['pedidos_pendientes'] = $pedidos[0]['total_pendientes'] ?? 0;
}
if ($module === 'devoluciones') {
emxGarantizarColumnasDevoluciones($pdo);
$stmt_dev = $pdo->query("
SELECT d.*, u.nombres, u.apellidos, u.email, p.id as pedido_id_str, p.total as total_pedido,
       COALESCE((
           SELECT jsonb_agg(jsonb_build_object(
               'producto', COALESCE(pr.nombre, dp.nombre_producto, 'Producto'),
               'series', COALESCE(dp.numero_serie_vendido::text, '')
           ))
           FROM detalle_pedidos dp
           LEFT JOIN productos pr ON pr.id = dp.producto_id
           WHERE dp.pedido_id = d.pedido_id
       ), '[]'::jsonb) AS series_vendidas_json
FROM devoluciones d
JOIN usuarios u ON d.usuario_id = u.id
JOIN pedidos p ON d.pedido_id = p.id
ORDER BY CASE d.estado
    WHEN 'pendiente_revision_fraude' THEN 1
    WHEN 'pendiente_revision' THEN 2
    WHEN 'esperando_decision_cliente' THEN 3
    WHEN 'cliente_eligio_reembolso' THEN 4
    WHEN 'cliente_eligio_cambio' THEN 5
    ELSE 6
END, d.created_at DESC");
$devoluciones_list = $stmt_dev->fetchAll(PDO::FETCH_ASSOC);
}

if ($module === 'garantias') {
    try {
        $stmt_gar = $pdo->query("
            SELECT gc.*, p.nombre AS producto_nombre, ped.nombre_cliente, ped.email, ped.created_at AS fecha_pedido
            FROM garantia_casos gc
            LEFT JOIN productos p ON p.id = gc.producto_id
            LEFT JOIN pedidos ped ON ped.id = gc.pedido_id
            ORDER BY gc.created_at DESC
        ");
        $garantias_casos = $stmt_gar->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { $garantias_casos = []; }
}

if ($module === 'sucursales') {
$sucursales_list = $pdo->query("SELECT * FROM sucursales ORDER BY es_matriz DESC, nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
}
$alertas_fraude = [];
if ($module === 'devoluciones' || $module === 'dashboard') {
$alertas_fraude = detectarFraudeDevoluciones($pdo);
}
$edit_data = null;
if ($action === 'edit' && isset($_GET['id'])) {
$id = $_GET['id'];
if ($module === 'productos') {
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$id]);
$edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
$edit_data['rangos_volumen'] = json_decode($edit_data['descuentos_volumen_rangos'] ?? '[]', true) ?: [];
$stmt2 = $pdo->prepare("SELECT id, url FROM producto_multimedia WHERE producto_id = ? ORDER BY orden ASC");
$stmt2->execute([$id]);
$edit_data['imagenes'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
$edit_data['specs'] = json_decode($edit_data['especificaciones_tecnicas'] ?? '{}', true) ?: [];
} elseif ($module === 'marcas') {
$stmt = $pdo->prepare("SELECT * FROM marcas WHERE id = ? AND is_active = 'true'");
$stmt->execute([$id]);
$edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($module === 'categorias') {
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ? AND is_active = 'true'");
$stmt->execute([$id]);
$edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($module === 'usuarios') {
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$id]);
$edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($module === 'planes') {
$stmt = $pdo->prepare("SELECT * FROM planes WHERE id = ?");
$stmt->execute([$id]);
$edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
$edit_data['beneficios'] = json_decode($edit_data['beneficios'] ?? '[]', true) ?: [];
} else
if ($module === 'garantias') {
    try {
        $stmt_gar = $pdo->query("
            SELECT gc.*, p.nombre AS producto_nombre, ped.nombre_cliente, ped.email, ped.created_at AS fecha_pedido
            FROM garantia_casos gc
            LEFT JOIN productos p ON p.id = gc.producto_id
            LEFT JOIN pedidos ped ON ped.id = gc.pedido_id
            ORDER BY gc.created_at DESC
        ");
        $garantias_casos = $stmt_gar->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { $garantias_casos = []; }
}

if ($module === 'sucursales') {
$stmt = $pdo->prepare("SELECT * FROM sucursales WHERE id = ?");
$stmt->execute([$id]);
$edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($module === 'banners') {
if ($action === 'edit_section') {
$stmt = $pdo->prepare("SELECT * FROM page_sections WHERE id = ?");
$stmt->execute([$id]);
$edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($action === 'edit_banner') {
$stmt = $pdo->prepare("SELECT * FROM banners_promocionales WHERE id = ?");
$stmt->execute([$id]);
$edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt_rel = $pdo->prepare("SELECT producto_id, descuento_asignado FROM banner_productos_rel WHERE banner_id = ?");
$stmt_rel->execute([$id]);
$edit_data['productos_rel'] = $stmt_rel->fetchAll(PDO::FETCH_ASSOC);
}
}
}

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/admin/admin_view.php
require EMX_VIEWS_PATH . '/admin/admin_view.php';
exit;
