<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_garantias.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

/**
 * Garantías ElectroMax
 * - Guarda snapshot de garantía al momento de la venta.
 * - Permite validar si un detalle vendido sigue en garantía.
 */

if (!function_exists('emxGarantiaColumnExists')) {
function emxGarantiaColumnExists($pdo, $tabla, $columna) {
    static $cache = [];
    $key = $tabla.'.'.$columna;
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $st = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=? AND column_name=? LIMIT 1");
        $st->execute([$tabla, $columna]);
        $cache[$key] = (bool)$st->fetchColumn();
    } catch (Throwable $e) { $cache[$key] = false; }
    return $cache[$key];
}
}

if (!function_exists('emxObtenerGarantiasProducto')) {
function emxObtenerGarantiasProducto($pdo, $producto_id) {
    try {
        $st = $pdo->prepare("SELECT componente, duracion_meses, cobertura, condiciones FROM producto_garantias WHERE producto_id = ? AND is_active = TRUE ORDER BY duracion_meses DESC, componente ASC");
        $st->execute([$producto_id]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) return $rows;
    } catch (Throwable $e) {}
    return [[
        'componente' =>'Garantía general',
        'duracion_meses' =>12,
        'cobertura' =>'Defectos de fábrica bajo uso normal.',
        'condiciones' =>'No cubre golpes, humedad, manipulación indebida o daños eléctricos externos.'
    ]];
}
}

if (!function_exists('emxSnapshotGarantiaProducto')) {
function emxSnapshotGarantiaProducto($pdo, $producto_id) {
    $garantias = emxObtenerGarantiasProducto($pdo, $producto_id);
    $maxMeses = 0;
    foreach ($garantias as $g) $maxMeses = max($maxMeses, (int)($g['duracion_meses'] ?? 0));
    if ($maxMeses <= 0) $maxMeses = 12;
    return [
        'garantias' =>$garantias,
        'duracion_maxima_meses' =>$maxMeses,
        'capturado_en' =>date('Y-m-d H:i:s')
    ];
}
}

if (!function_exists('emxAplicarGarantiaADetalle')) {
function emxAplicarGarantiaADetalle($pdo, $detalle_id, $producto_id, $fecha_inicio = null) {
    if (!$detalle_id || !$producto_id) return false;
    if (!emxGarantiaColumnExists($pdo, 'detalle_pedidos', 'garantia_snapshot')) return false;
    $snapshot = emxSnapshotGarantiaProducto($pdo, $producto_id);
    $inicio = $fecha_inicio ? date('Y-m-d', strtotime($fecha_inicio)) : date('Y-m-d');
    $fin = date('Y-m-d', strtotime($inicio . ' +' . (int)$snapshot['duracion_maxima_meses'] . ' months'));
    $st = $pdo->prepare("UPDATE detalle_pedidos SET garantia_snapshot = ?::jsonb, garantia_inicio = ?, garantia_fin = ? WHERE id = ?");
    return $st->execute([json_encode($snapshot['garantias'], JSON_UNESCAPED_UNICODE), $inicio, $fin, $detalle_id]);
}
}

if (!function_exists('emxDetalleTieneGarantiaVigente')) {
function emxDetalleTieneGarantiaVigente($detalle) {
    if (empty($detalle['garantia_fin'])) return false;
    return strtotime($detalle['garantia_fin']) >= strtotime(date('Y-m-d'));
}
}
?>