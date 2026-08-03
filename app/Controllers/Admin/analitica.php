<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
emxRequireRole(['SUPERADMIN', 'ADMIN']);

function emxACol($pdo, $table, $col) {
    static $cache = [];
    $key = $table . '.' . $col;
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $st = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=? AND column_name=? LIMIT 1");
        $st->execute([$table, $col]);
        return $cache[$key] = (bool)$st->fetchColumn();
    } catch (Throwable $e) { return $cache[$key] = false; }
}
function emxATable($pdo, $table) {
    static $cache = [];
    if (array_key_exists($table, $cache)) return $cache[$table];
    try {
        $st = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name=? LIMIT 1");
        $st->execute([$table]);
        return $cache[$table] = (bool)$st->fetchColumn();
    } catch (Throwable $e) { return $cache[$table] = false; }
}
function emxQAll($pdo, $sql, $params = []) {
    try {
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('[analitica] ' . $e->getMessage() . ' SQL: ' . $sql);
        return [];
    }
}
function emxQOne($pdo, $sql, $params = []) {
    $r = emxQAll($pdo, $sql, $params);
    return $r[0] ?? [];
}
function emxMoney($v) { return is_numeric($v) ? '$' . number_format((float)$v, 2) : 'N/D'; }
function emxNum($v) { return number_format((float)$v, 0, ',', '.'); }
function emxPct($v) { return is_numeric($v) ? number_format((float)$v, 1) . '%' : 'N/D'; }
function emxVar($a, $b) {
    $a=(float)$a; $b=(float)$b;
    if (abs($b) < 0.00001) return $a > 0 ? 100 : 0;
    return round((($a-$b)/$b)*100,1);
}
function emxCleanLabel($s, $len=34) {
    $s = trim((string)$s);
    if (function_exists('mb_strlen') && mb_strlen($s) > $len) return mb_substr($s, 0, $len - 1) . '…';
    return strlen($s) > $len ? substr($s, 0, $len - 1) . '…' : $s;
}
function emxJsonNum($rows, $col, $cast = 'float') {
    return array_map($cast === 'int' ? 'intval' : 'floatval', array_column($rows, $col));
}
function emxCsv($v) {
    return '"' . str_replace('"','""',(string)$v) . '"';
}

$hasCosto = emxACol($pdo, 'productos', 'costo_unitario');
$costProd = $hasCosto ? "NULLIF(prod.costo_unitario,0)" : "NULL::numeric";
$knownProd = $hasCosto ? "NULLIF(prod.costo_unitario,0) IS NOT NULL" : "FALSE";
$costP = $hasCosto ? "NULLIF(p.costo_unitario,0)" : "NULL::numeric";
$knownP = $hasCosto ? "NULLIF(p.costo_unitario,0) IS NOT NULL" : "FALSE";

$hoy = new DateTimeImmutable('today');
$preset = $_GET['preset'] ?? 'mes';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

switch ($preset) {
    case 'hoy': $ini=$hoy; $fin=$hoy; break;
    case 'semana': $ini=$hoy->modify('monday this week'); $fin=$hoy; break;
    case 'ultimos_30': $ini=$hoy->modify('-29 days'); $fin=$hoy; break;
    case 'ultimos_90': $ini=$hoy->modify('-89 days'); $fin=$hoy; break;
    case 'trimestre':
        $m=(int)$hoy->format('n'); $startMonth=(($m-1)-(($m-1)%3))+1;
        $ini=new DateTimeImmutable($hoy->format('Y').'-'.str_pad((string)$startMonth,2,'0',STR_PAD_LEFT).'-01'); $fin=$hoy; break;
    case 'anio': $ini=new DateTimeImmutable($hoy->format('Y').'-01-01'); $fin=$hoy; break;
    case 'custom':
        $ini=$fecha_inicio ? new DateTimeImmutable($fecha_inicio) : $hoy->modify('first day of this month');
        $fin=$fecha_fin ? new DateTimeImmutable($fecha_fin) : $hoy;
        break;
    case 'mes':
    default:
        $preset='mes'; $ini=$hoy->modify('first day of this month'); $fin=$hoy; break;
}
if ($fin < $ini) { $tmp=$ini; $ini=$fin; $fin=$tmp; }
$fecha_inicio=$ini->format('Y-m-d');
$fecha_fin=$fin->format('Y-m-d');

$days=max(1, (int)$ini->diff($fin)->days + 1);
$compare=$_GET['compare'] ?? 'periodo_anterior';
switch ($compare) {
    case 'mes_anterior':
        $iniComp=$ini->modify('first day of previous month');
        $finComp=$ini->modify('last day of previous month');
        break;
    case 'anio_anterior':
        $iniComp=$ini->modify('-1 year');
        $finComp=$fin->modify('-1 year');
        break;
    case 'periodo_anterior':
    default:
        $compare='periodo_anterior';
        $finComp=$ini->modify('-1 day');
        $iniComp=$finComp->modify('-'.($days-1).' days');
        break;
}
$fecha_inicio_comp=$iniComp->format('Y-m-d');
$fecha_fin_comp=$finComp->format('Y-m-d');

$group=$_GET['group'] ?? 'dia';
if (!in_array($group, ['dia','semana','mes'], true)) $group='dia';
$groupSql = $group === 'mes' ? 'month' : ($group === 'semana' ? 'week' : 'day');
$groupLabel = $group === 'mes' ? 'Mon YYYY' : ($group === 'semana' ? 'IYYY-IW' : 'DD Mon');
$estadoOK = "LOWER(COALESCE(ped.estado,'')) NOT IN ('cancelado','cancelada','rechazado','rechazada')";

$kpi = emxQOne($pdo, "
    SELECT COUNT(*) AS pedidos,
           COALESCE(SUM(ped.total),0) AS ingresos,
           COALESCE(SUM(ped.subtotal),0) AS subtotal,
           COALESCE(SUM(ped.iva_total),0) AS iva,
           COALESCE(SUM(ped.descuento_aplicado),0) AS descuento,
           COALESCE(AVG(ped.total),0) AS ticket,
           COUNT(DISTINCT ped.usuario_id) AS clientes,
           COALESCE(SUM(CASE WHEN COALESCE(u.es_prime,false)=true OR LOWER(COALESCE(ped.membresia_usada,'')) LIKE '%prime%' THEN ped.total ELSE 0 END),0) AS ingresos_prime,
           COALESCE(SUM(CASE WHEN COALESCE(u.es_prime,false)=true OR LOWER(COALESCE(ped.membresia_usada,'')) LIKE '%prime%' THEN 1 ELSE 0 END),0) AS pedidos_prime
    FROM pedidos ped
    LEFT JOIN usuarios u ON u.id = ped.usuario_id
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?", [$fecha_inicio, $fecha_fin]);

$kpiC = emxQOne($pdo, "
    SELECT COUNT(*) AS pedidos,
           COALESCE(SUM(ped.total),0) AS ingresos,
           COALESCE(AVG(ped.total),0) AS ticket,
           COUNT(DISTINCT ped.usuario_id) AS clientes
    FROM pedidos ped
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?", [$fecha_inicio_comp, $fecha_fin_comp]);

$margen = emxQOne($pdo, "
    SELECT COALESCE(SUM(dp.total),0) AS ventas_totales,
           COALESCE(SUM(dp.cantidad),0) AS unidades_totales,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END),0) AS ventas_con_costo,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.cantidad ELSE 0 END),0) AS unidades_con_costo,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.cantidad * {$costProd} ELSE 0 END),0) AS costos,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END),0) AS utilidad,
           CASE WHEN COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END),0)>0
                THEN (SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END) / SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END))*100
                ELSE NULL END AS margen,
           COALESCE(SUM(CASE WHEN NOT ({$knownProd}) THEN dp.total ELSE 0 END),0) AS ventas_sin_costo
    FROM detalle_pedidos dp
    JOIN pedidos ped ON ped.id = dp.pedido_id
    JOIN productos prod ON prod.id = dp.producto_id
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?", [$fecha_inicio, $fecha_fin]);

$margenC = emxQOne($pdo, "
    SELECT COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END),0) AS utilidad,
           CASE WHEN COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END),0)>0
                THEN (SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END) / SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END))*100
                ELSE NULL END AS margen
    FROM detalle_pedidos dp
    JOIN pedidos ped ON ped.id = dp.pedido_id
    JOIN productos prod ON prod.id = dp.producto_id
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?", [$fecha_inicio_comp, $fecha_fin_comp]);

$coberturaCosto = ((float)($margen['ventas_totales'] ?? 0)) > 0
    ? ((float)($margen['ventas_con_costo'] ?? 0) / (float)$margen['ventas_totales']) * 100
    : 0;

$notasCredito = emxATable($pdo, 'notas_credito')
    ? emxQOne($pdo, "SELECT COUNT(*) AS notas, COALESCE(SUM(total),0) AS total FROM notas_credito WHERE DATE(COALESCE(fecha_emision,created_at)) BETWEEN ? AND ?", [$fecha_inicio, $fecha_fin])
    : ['notas'=>0,'total'=>0];

$devolucionesKpi = emxATable($pdo, 'devoluciones')
    ? emxQOne($pdo, "SELECT COUNT(*) AS casos, COALESCE(SUM(CASE WHEN estado IN ('reembolsado','cerrada','reemplazo_entregado') THEN 1 ELSE 0 END),0) AS cerradas FROM devoluciones WHERE DATE(created_at) BETWEEN ? AND ?", [$fecha_inicio, $fecha_fin])
    : ['casos'=>0,'cerradas'=>0];

$tasaDevolucion = ((float)($kpi['pedidos'] ?? 0)) > 0 ? ((float)($devolucionesKpi['casos'] ?? 0) / (float)$kpi['pedidos']) * 100 : 0;

$series = emxQAll($pdo, "
    SELECT TO_CHAR(date_trunc('{$groupSql}', ped.created_at), '{$groupLabel}') AS label,
           date_trunc('{$groupSql}', ped.created_at) AS orden,
           COALESCE(SUM(ped.total),0) AS ingresos,
           COUNT(*) AS pedidos,
           COUNT(DISTINCT ped.usuario_id) AS clientes,
           COALESCE(AVG(ped.total),0) AS ticket
    FROM pedidos ped
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?
    GROUP BY orden, label
    ORDER BY orden", [$fecha_inicio, $fecha_fin]);

$seriesFin = emxQAll($pdo, "
    SELECT TO_CHAR(date_trunc('{$groupSql}', ped.created_at), '{$groupLabel}') AS label,
           date_trunc('{$groupSql}', ped.created_at) AS orden,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END),0) AS utilidad,
           CASE WHEN COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END),0)>0
                THEN (SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END) / SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END))*100
                ELSE NULL END AS margen
    FROM detalle_pedidos dp
    JOIN pedidos ped ON ped.id = dp.pedido_id
    JOIN productos prod ON prod.id = dp.producto_id
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?
    GROUP BY orden, label
    ORDER BY orden", [$fecha_inicio, $fecha_fin]);

$seriesFinByLabel = [];
foreach ($seriesFin as $r) $seriesFinByLabel[$r['label']] = $r;
foreach ($series as &$r) {
    $f = $seriesFinByLabel[$r['label']] ?? [];
    $r['utilidad'] = (float)($f['utilidad'] ?? 0);
    $r['margen'] = isset($f['margen']) && $f['margen'] !== null ? (float)$f['margen'] : null;
}
unset($r);

$mensual3 = emxQAll($pdo, "
    SELECT TO_CHAR(date_trunc('month', ped.created_at), 'Mon YYYY') AS mes,
           date_trunc('month', ped.created_at) AS orden,
           COALESCE(SUM(ped.total),0) AS ingresos,
           COUNT(*) AS pedidos,
           COALESCE(AVG(ped.total),0) AS ticket
    FROM pedidos ped
    WHERE {$estadoOK} AND ped.created_at >= date_trunc('month', CURRENT_DATE) - INTERVAL '2 months'
    GROUP BY orden, mes
    ORDER BY orden");

$mensual12 = emxQAll($pdo, "
    SELECT TO_CHAR(date_trunc('month', ped.created_at), 'Mon YYYY') AS mes,
           date_trunc('month', ped.created_at) AS orden,
           COALESCE(SUM(ped.total),0) AS ingresos,
           COUNT(*) AS pedidos
    FROM pedidos ped
    WHERE {$estadoOK} AND ped.created_at >= date_trunc('month', CURRENT_DATE) - INTERVAL '11 months'
    GROUP BY orden, mes
    ORDER BY orden");

$topProductos = emxQAll($pdo, "
    SELECT prod.id, prod.nombre AS producto, COALESCE(prod.sku,'') AS sku,
           COALESCE(c.nombre,'Sin categoría') AS categoria,
           COALESCE(m.nombre,'Sin marca') AS marca,
           COALESCE(SUM(dp.cantidad),0) AS unidades,
           COALESCE(SUM(dp.total),0) AS ingresos,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.cantidad * {$costProd} ELSE 0 END),0) AS costos,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END),0) AS utilidad,
           CASE WHEN COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END),0)>0
                THEN (SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END) / SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END))*100
                ELSE NULL END AS margen,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END),0) AS ventas_con_costo,
           COALESCE(SUM(CASE WHEN NOT ({$knownProd}) THEN dp.total ELSE 0 END),0) AS ventas_sin_costo
    FROM detalle_pedidos dp
    JOIN pedidos ped ON ped.id = dp.pedido_id
    JOIN productos prod ON prod.id = dp.producto_id
    LEFT JOIN categorias c ON c.id = prod.categoria_id
    LEFT JOIN marcas m ON m.id = prod.marca_id
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?
    GROUP BY prod.id, prod.nombre, prod.sku, c.nombre, m.nombre
    ORDER BY unidades DESC, ingresos DESC
    LIMIT 20", [$fecha_inicio, $fecha_fin]);

$topUtilidad = emxQAll($pdo, "
    SELECT prod.nombre AS producto, COALESCE(m.nombre,'Sin marca') AS marca,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END),0) AS utilidad,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END),0) AS ingresos,
           CASE WHEN COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END),0)>0
                THEN (SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END) / SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END))*100
                ELSE NULL END AS margen
    FROM detalle_pedidos dp
    JOIN pedidos ped ON ped.id = dp.pedido_id
    JOIN productos prod ON prod.id = dp.producto_id
    LEFT JOIN marcas m ON m.id = prod.marca_id
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ? AND {$knownProd}
    GROUP BY prod.nombre, m.nombre
    ORDER BY utilidad DESC
    LIMIT 12", [$fecha_inicio, $fecha_fin]);

$menosVendidos = emxQAll($pdo, "
    SELECT p.nombre AS producto, COALESCE(p.sku,'') AS sku, COALESCE(m.nombre,'Sin marca') AS marca,
           COALESCE(s.unidades,0) AS unidades, COALESCE(s.ingresos,0) AS ingresos
    FROM productos p
    LEFT JOIN marcas m ON m.id = p.marca_id
    LEFT JOIN (
        SELECT dp.producto_id, SUM(dp.cantidad) AS unidades, SUM(dp.total) AS ingresos
        FROM detalle_pedidos dp
        JOIN pedidos ped ON ped.id = dp.pedido_id
        WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?
        GROUP BY dp.producto_id
    ) s ON s.producto_id = p.id
    WHERE p.deleted_at IS NULL AND COALESCE(p.is_active,true)=true
    ORDER BY COALESCE(s.unidades,0) ASC, COALESCE(s.ingresos,0) ASC, p.nombre ASC
    LIMIT 12", [$fecha_inicio, $fecha_fin]);

$categorias = emxQAll($pdo, "
    SELECT COALESCE(c.nombre,'Sin categoría') AS categoria,
           COALESCE(SUM(dp.total),0) AS ingresos,
           COALESCE(SUM(dp.cantidad),0) AS unidades,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END),0) AS utilidad,
           CASE WHEN COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END),0)>0
                THEN (SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END) / SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END))*100
                ELSE NULL END AS margen
    FROM detalle_pedidos dp
    JOIN pedidos ped ON ped.id = dp.pedido_id
    JOIN productos prod ON prod.id = dp.producto_id
    LEFT JOIN categorias c ON c.id = prod.categoria_id
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?
    GROUP BY c.nombre
    ORDER BY ingresos DESC
    LIMIT 14", [$fecha_inicio, $fecha_fin]);

$marcas = emxQAll($pdo, "
    SELECT COALESCE(m.nombre,'Sin marca') AS marca,
           COALESCE(SUM(dp.total),0) AS ingresos,
           COALESCE(SUM(dp.cantidad),0) AS unidades,
           COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END),0) AS utilidad,
           CASE WHEN COALESCE(SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END),0)>0
                THEN (SUM(CASE WHEN {$knownProd} THEN dp.total - (dp.cantidad * {$costProd}) ELSE 0 END) / SUM(CASE WHEN {$knownProd} THEN dp.total ELSE 0 END))*100
                ELSE NULL END AS margen
    FROM detalle_pedidos dp
    JOIN pedidos ped ON ped.id = dp.pedido_id
    JOIN productos prod ON prod.id = dp.producto_id
    LEFT JOIN marcas m ON m.id = prod.marca_id
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?
    GROUP BY m.nombre
    ORDER BY ingresos DESC
    LIMIT 14", [$fecha_inicio, $fecha_fin]);

$pagos = emxQAll($pdo, "
    SELECT COALESCE(NULLIF(ped.metodo_pago,''),'No especificado') AS metodo,
           COUNT(*) AS transacciones,
           COALESCE(SUM(ped.total),0) AS ingresos
    FROM pedidos ped
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?
    GROUP BY metodo
    ORDER BY ingresos DESC", [$fecha_inicio, $fecha_fin]);

$ciudades = emxQAll($pdo, "
    SELECT COALESCE(NULLIF(ped.ciudad,''),'Sin ciudad') AS ciudad,
           COUNT(*) AS pedidos,
           COALESCE(SUM(ped.total),0) AS ingresos,
           COALESCE(AVG(ped.total),0) AS ticket
    FROM pedidos ped
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?
    GROUP BY ciudad
    ORDER BY ingresos DESC
    LIMIT 12", [$fecha_inicio, $fecha_fin]);

$topClientes = emxQAll($pdo, "
    SELECT COALESCE(u.nombres || ' ' || u.apellidos, ped.nombre_cliente, 'Cliente') AS cliente,
           COALESCE(u.email, ped.email, '') AS email,
           COUNT(*) AS pedidos,
           COALESCE(SUM(ped.total),0) AS total_gastado,
           COALESCE(AVG(ped.total),0) AS ticket
    FROM pedidos ped
    LEFT JOIN usuarios u ON u.id = ped.usuario_id
    WHERE {$estadoOK} AND DATE(ped.created_at) BETWEEN ? AND ?
    GROUP BY cliente, email
    ORDER BY total_gastado DESC
    LIMIT 12", [$fecha_inicio, $fecha_fin]);

$stock = emxQOne($pdo, "
    SELECT COUNT(*) FILTER (WHERE p.stock_actual_global <= p.punto_reorden) AS bajo,
           COALESCE(SUM(p.stock_actual_global),0) AS unidades_stock,
           COALESCE(SUM(CASE WHEN {$knownP} THEN p.stock_actual_global * {$costP} ELSE 0 END),0) AS valor_costo,
           COALESCE(SUM(p.stock_actual_global * p.precio_base),0) AS valor_venta,
           COUNT(*) FILTER (WHERE NOT ({$knownP})) AS productos_sin_costo
    FROM productos p
    WHERE p.deleted_at IS NULL AND COALESCE(p.is_active,true)=true");

$stockBajo = emxQAll($pdo, "
    SELECT p.nombre, COALESCE(c.nombre,'Sin categoría') AS categoria,
           p.stock_actual_global, p.punto_reorden,
           COALESCE(p.precio_base,0) AS precio,
           {$costP} AS costo
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    WHERE p.deleted_at IS NULL AND COALESCE(p.is_active,true)=true AND p.stock_actual_global <= p.punto_reorden
    ORDER BY p.stock_actual_global ASC, p.nombre ASC
    LIMIT 12");

$devEstados = [];
$devMotivos = [];
$fraude = ['casos'=>0];
if (emxATable($pdo, 'devoluciones')) {
    $devEstados = emxQAll($pdo, "SELECT COALESCE(estado,'Sin estado') AS estado, COUNT(*) AS total FROM devoluciones WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY estado ORDER BY total DESC", [$fecha_inicio, $fecha_fin]);
    $devMotivos = emxQAll($pdo, "SELECT COALESCE(motivo,'Sin motivo') AS motivo, COUNT(*) AS total FROM devoluciones WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY motivo ORDER BY total DESC LIMIT 10", [$fecha_inicio, $fecha_fin]);
    if (emxACol($pdo, 'devoluciones', 'fraude_detectado')) {
        $fraude = emxQOne($pdo, "SELECT COUNT(*) AS casos FROM devoluciones WHERE fraude_detectado = true AND DATE(created_at) BETWEEN ? AND ?", [$fecha_inicio, $fecha_fin]);
    }
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="electromax_analitica_productos_' . $fecha_inicio . '_' . $fecha_fin . '.csv"');
    echo "\xEF\xBB\xBF";
    echo "Producto,SKU,Categoria,Marca,Unidades,Ingresos,CostosRegistrados,UtilidadReal,MargenReal,VentasSinCosto\n";
    foreach ($topProductos as $p) {
        echo emxCsv($p['producto']) . ',' . emxCsv($p['sku']) . ',' . emxCsv($p['categoria']) . ',' . emxCsv($p['marca']) . ',' .
             (float)$p['unidades'] . ',' . (float)$p['ingresos'] . ',' . (float)$p['costos'] . ',' . (float)$p['utilidad'] . ',' .
             (is_numeric($p['margen']) ? (float)$p['margen'] : '') . ',' . (float)$p['ventas_sin_costo'] . "\n";
    }
    exit;
}

$charts = [
    'series_labels' => array_column($series, 'label'),
    'series_ingresos' => emxJsonNum($series, 'ingresos'),
    'series_utilidad' => emxJsonNum($series, 'utilidad'),
    'series_pedidos' => emxJsonNum($series, 'pedidos', 'int'),
    'series_ticket' => emxJsonNum($series, 'ticket'),
    'series_margen' => array_map(fn($r)=>$r['margen'] === null ? null : (float)$r['margen'], $series),

    'cat_labels' => array_map(fn($r)=>emxCleanLabel($r['categoria'],30), $categorias),
    'cat_ingresos' => emxJsonNum($categorias, 'ingresos'),
    'cat_unidades' => emxJsonNum($categorias, 'unidades', 'int'),
    'cat_utilidad' => emxJsonNum($categorias, 'utilidad'),
    'cat_margen' => array_map(fn($r)=>$r['margen'] === null ? null : (float)$r['margen'], $categorias),

    'marca_labels' => array_map(fn($r)=>emxCleanLabel($r['marca'],30), $marcas),
    'marca_ingresos' => emxJsonNum($marcas, 'ingresos'),
    'marca_utilidad' => emxJsonNum($marcas, 'utilidad'),
    'marca_unidades' => emxJsonNum($marcas, 'unidades', 'int'),
    'marca_margen' => array_map(fn($r)=>$r['margen'] === null ? null : (float)$r['margen'], $marcas),

    'top_labels' => array_map(fn($r)=>emxCleanLabel($r['producto'],34), $topProductos),
    'top_units' => emxJsonNum($topProductos, 'unidades', 'int'),
    'top_income' => emxJsonNum($topProductos, 'ingresos'),

    'profit_labels' => array_map(fn($r)=>emxCleanLabel($r['producto'],34), $topUtilidad),
    'profit_values' => emxJsonNum($topUtilidad, 'utilidad'),
    'profit_margin' => array_map(fn($r)=>$r['margen'] === null ? null : (float)$r['margen'], $topUtilidad),

    'menos_labels' => array_map(fn($r)=>emxCleanLabel($r['producto'],30), $menosVendidos),
    'menos_units' => emxJsonNum($menosVendidos, 'unidades', 'int'),

    'pagos_labels' => array_column($pagos, 'metodo'),
    'pagos_values' => emxJsonNum($pagos, 'ingresos'),
    'pagos_tx' => emxJsonNum($pagos, 'transacciones', 'int'),

    'mes3_labels' => array_column($mensual3, 'mes'),
    'mes3_ingresos' => emxJsonNum($mensual3, 'ingresos'),
    'mes3_pedidos' => emxJsonNum($mensual3, 'pedidos', 'int'),
    'mes3_ticket' => emxJsonNum($mensual3, 'ticket'),

    'mes12_labels' => array_column($mensual12, 'mes'),
    'mes12_ingresos' => emxJsonNum($mensual12, 'ingresos'),
    'mes12_pedidos' => emxJsonNum($mensual12, 'pedidos', 'int'),

    'dev_labels' => array_column($devEstados, 'estado'),
    'dev_values' => emxJsonNum($devEstados, 'total', 'int'),
    'motivo_labels' => array_column($devMotivos, 'motivo'),
    'motivo_values' => emxJsonNum($devMotivos, 'total', 'int'),
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<!-- Favicon ElectroMax global -->
<link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
<link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
<link rel="manifest" href="assets/favicon/site.webmanifest">
<meta name="theme-color" content="#0b4da2">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analítica Financiera Real - ElectroMax</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{font-family:Inter,system-ui,sans-serif}
body{background:#eef3f9;color:#0f172a}
.panel{background:#fff;border:1px solid #dbe6f3;box-shadow:0 12px 35px rgba(15,23,42,.06)}
.dark-card{background:linear-gradient(135deg,#07142f,#0b3270);color:#fff}
.metric{transition:.2s}.metric:hover{transform:translateY(-2px)}
.chart-box{height:340px}.chart-tall{height:430px}.chart-wide{height:390px}
.kpi-grid{grid-template-columns:repeat(auto-fit,minmax(210px,1fr))}
@media print{.no-print{display:none!important}body{background:white}.panel{box-shadow:none}}
</style>
</head>
<body>
<header class="dark-card no-print">
  <div class="max-w-[1600px] mx-auto px-6 py-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div>
      <p class="text-blue-200 text-xs font-black uppercase tracking-[.25em]">ElectroMax Business Intelligence</p>
      <h1 class="text-2xl lg:text-3xl font-black mt-1">Dashboard financiero real</h1>
      <p class="text-blue-100 text-sm mt-1">Período: <?= htmlspecialchars($fecha_inicio) ?> al <?= htmlspecialchars($fecha_fin) ?> · Comparación: <?= htmlspecialchars($fecha_inicio_comp) ?> al <?= htmlspecialchars($fecha_fin_comp) ?></p>
      <p class="text-blue-200 text-xs mt-2">Los ingresos salen de pedidos reales. La utilidad y margen solo usan productos con costo_unitario registrado; no se inventan costos.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="admin.php" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-sm font-bold"><i class="fas fa-arrow-left mr-2"></i>Admin</a>
      <a href="?<?= http_build_query(array_merge($_GET, ['export'=>'csv'])) ?>" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-sm font-bold"><i class="fas fa-file-csv mr-2"></i>CSV</a>
      <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-white text-slate-900 text-sm font-bold"><i class="fas fa-print mr-2"></i>Imprimir</button>
    </div>
  </div>
</header>

<main class="max-w-[1600px] mx-auto px-6 py-6">
  <form method="GET" class="panel rounded-2xl p-4 mb-6 no-print">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-7 gap-3 items-end">
      <div><label class="text-xs font-black text-slate-500 uppercase">Rango</label><select name="preset" class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200">
        <?php foreach(['hoy'=>'Hoy','semana'=>'Esta semana','mes'=>'Este mes','ultimos_30'=>'Últimos 30 días','ultimos_90'=>'Últimos 3 meses','trimestre'=>'Trimestre actual','anio'=>'Año actual','custom'=>'Personalizado'] as $k=>$v): ?>
          <option value="<?= $k ?>" <?= $preset===$k?'selected':'' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select></div>
      <div><label class="text-xs font-black text-slate-500 uppercase">Inicio</label><input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>" class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200"></div>
      <div><label class="text-xs font-black text-slate-500 uppercase">Fin</label><input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>" class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200"></div>
      <div><label class="text-xs font-black text-slate-500 uppercase">Agrupar</label><select name="group" class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200"><option value="dia" <?= $group==='dia'?'selected':'' ?>>Día</option><option value="semana" <?= $group==='semana'?'selected':'' ?>>Semana</option><option value="mes" <?= $group==='mes'?'selected':'' ?>>Mes</option></select></div>
      <div><label class="text-xs font-black text-slate-500 uppercase">Comparar</label><select name="compare" class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200"><option value="periodo_anterior" <?= $compare==='periodo_anterior'?'selected':'' ?>>Período anterior</option><option value="mes_anterior" <?= $compare==='mes_anterior'?'selected':'' ?>>Mes anterior</option><option value="anio_anterior" <?= $compare==='anio_anterior'?'selected':'' ?>>Año anterior</option></select></div>
      <div class="xl:col-span-2"><button class="w-full px-5 py-2.5 rounded-xl bg-slate-900 text-white font-black hover:bg-slate-800"><i class="fas fa-filter mr-2"></i>Aplicar filtros</button></div>
    </div>
  </form>

  <?php if (!$hasCosto || $coberturaCosto < 100): ?>
  <div class="panel rounded-2xl p-4 mb-6 border-amber-200 bg-amber-50">
    <div class="flex items-start gap-3">
      <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center"><i class="fas fa-database"></i></div>
      <div>
        <h3 class="font-black text-amber-900">Aviso de datos financieros reales</h3>
        <p class="text-sm text-amber-800 mt-1">
          No se están usando costos ficticios. Cobertura de costo real en ventas del período:
          <strong><?= emxPct($coberturaCosto) ?></strong>.
          Los márgenes y utilidades solo se calculan con productos que tienen <strong>costo_unitario</strong> registrado.
        </p>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <section class="grid kpi-grid gap-4 mb-6">
    <?php
      $cards = [
        ['Ingresos', emxMoney($kpi['ingresos'] ?? 0), emxVar($kpi['ingresos'] ?? 0, $kpiC['ingresos'] ?? 0), 'fa-dollar-sign', 'emerald'],
        ['Utilidad real', emxMoney($margen['utilidad'] ?? 0), emxVar($margen['utilidad'] ?? 0, $margenC['utilidad'] ?? 0), 'fa-chart-line', 'blue'],
        ['Margen real', emxPct($margen['margen'] ?? null), is_numeric($margenC['margen'] ?? null) ? round(((float)($margen['margen'] ?? 0) - (float)$margenC['margen']),1) : 0, 'fa-percent', 'violet'],
        ['Pedidos', emxNum($kpi['pedidos'] ?? 0), emxVar($kpi['pedidos'] ?? 0, $kpiC['pedidos'] ?? 0), 'fa-receipt', 'slate'],
        ['Ticket promedio', emxMoney($kpi['ticket'] ?? 0), emxVar($kpi['ticket'] ?? 0, $kpiC['ticket'] ?? 0), 'fa-ticket', 'amber'],
        ['Devoluciones', emxPct($tasaDevolucion), 0, 'fa-rotate-left', 'red'],
        ['Ingresos Prime', emxMoney($kpi['ingresos_prime'] ?? 0), 0, 'fa-crown', 'yellow'],
        ['Cobertura costos', emxPct($coberturaCosto), 0, 'fa-database', 'cyan'],
      ];
    ?>
    <?php foreach($cards as $c): $trend = (float)$c[2]; ?>
    <div class="panel metric rounded-2xl p-5">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs font-black uppercase tracking-widest text-slate-500"><?= $c[0] ?></p>
          <p class="text-2xl font-black text-slate-900 mt-2"><?= $c[1] ?></p>
          <p class="text-xs mt-2 <?= $trend >= 0 ? 'text-emerald-600' : 'text-red-600' ?>">
            <i class="fas <?= $trend >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' ?> mr-1"></i><?= $trend ?>% vs comparación
          </p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center"><i class="fas <?= $c[3] ?>"></i></div>
      </div>
    </div>
    <?php endforeach; ?>
  </section>

  <section class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="panel rounded-2xl p-6 xl:col-span-2">
      <div class="flex items-center justify-between mb-4"><h3 class="font-black">Ingresos, utilidad real, pedidos y margen</h3><span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-lg font-bold">Datos reales</span></div>
      <div class="chart-tall"><canvas id="ventasUtilidad"></canvas></div>
    </div>
    <div class="panel rounded-2xl p-6">
      <h3 class="font-black mb-4">Composición por método de pago</h3>
      <div class="chart-box"><canvas id="pagosChart"></canvas></div>
    </div>
  </section>

  <section class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Ventas por categoría</h3><div class="chart-box"><canvas id="categoriasChart"></canvas></div></div>
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Margen real por categoría</h3><div class="chart-box"><canvas id="catMargenChart"></canvas></div></div>
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Marca más vendida</h3><div class="chart-box"><canvas id="marcasChart"></canvas></div></div>
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Productos más vendidos</h3><div class="chart-box"><canvas id="topProductosChart"></canvas></div></div>
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Productos con mayor utilidad real</h3><div class="chart-box"><canvas id="utilidadChart"></canvas></div></div>
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Productos menos vendidos</h3><div class="chart-box"><canvas id="menosVendidosChart"></canvas></div></div>
  </section>

  <section class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Comparación últimos 3 meses</h3><div class="chart-wide"><canvas id="ultimos3Chart"></canvas></div></div>
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Tendencia últimos 12 meses</h3><div class="chart-wide"><canvas id="mes12Chart"></canvas></div></div>
  </section>

  <section class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Devoluciones por estado</h3><div class="chart-box"><canvas id="devEstadosChart"></canvas></div></div>
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Motivos de devolución</h3><div class="chart-box"><canvas id="devMotivosChart"></canvas></div></div>
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Riesgo, inventario y crédito</h3><div class="space-y-4">
      <div class="p-4 rounded-xl bg-red-50 border border-red-100"><p class="text-xs text-red-600 font-black uppercase">Alertas de fraude</p><p class="text-3xl font-black text-red-700"><?= (int)($fraude['casos'] ?? 0) ?></p></div>
      <div class="p-4 rounded-xl bg-amber-50 border border-amber-100"><p class="text-xs text-amber-700 font-black uppercase">Productos bajo reorden</p><p class="text-3xl font-black text-amber-700"><?= (int)($stock['bajo'] ?? 0) ?></p></div>
      <div class="p-4 rounded-xl bg-blue-50 border border-blue-100"><p class="text-xs text-blue-700 font-black uppercase">Inventario a costo real registrado</p><p class="text-2xl font-black text-blue-800"><?= emxMoney($stock['valor_costo'] ?? 0) ?></p><p class="text-xs text-blue-700 mt-1"><?= (int)($stock['productos_sin_costo'] ?? 0) ?> productos sin costo registrado</p></div>
      <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100"><p class="text-xs text-emerald-700 font-black uppercase">Inventario a precio venta</p><p class="text-2xl font-black text-emerald-800"><?= emxMoney($stock['valor_venta'] ?? 0) ?></p></div>
      <div class="p-4 rounded-xl bg-slate-50 border border-slate-200"><p class="text-xs text-slate-600 font-black uppercase">Notas de crédito</p><p class="text-2xl font-black text-slate-900"><?= (int)($notasCredito['notas'] ?? 0) ?> · <?= emxMoney($notasCredito['total'] ?? 0) ?></p></div>
    </div></div>
  </section>

  <section class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Ventas por ciudad</h3><div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="py-2">Ciudad</th><th class="text-right">Pedidos</th><th class="text-right">Ingresos</th><th class="text-right">Ticket</th></tr></thead><tbody><?php foreach($ciudades as $r): ?><tr class="border-b border-slate-100"><td class="py-2 font-semibold"><?= htmlspecialchars($r['ciudad']) ?></td><td class="text-right"><?= emxNum($r['pedidos']) ?></td><td class="text-right font-bold text-emerald-700"><?= emxMoney($r['ingresos']) ?></td><td class="text-right"><?= emxMoney($r['ticket']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
    <div class="panel rounded-2xl p-6"><h3 class="font-black mb-4">Clientes con mayor compra</h3><div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="py-2">Cliente</th><th class="text-right">Pedidos</th><th class="text-right">Total</th><th class="text-right">Ticket</th></tr></thead><tbody><?php foreach($topClientes as $r): ?><tr class="border-b border-slate-100"><td class="py-2"><strong><?= htmlspecialchars($r['cliente']) ?></strong><br><span class="text-xs text-slate-500"><?= htmlspecialchars($r['email']) ?></span></td><td class="text-right"><?= emxNum($r['pedidos']) ?></td><td class="text-right font-bold text-emerald-700"><?= emxMoney($r['total_gastado']) ?></td><td class="text-right"><?= emxMoney($r['ticket']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
  </section>

  <section class="panel rounded-2xl p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-4">
      <div><h3 class="font-black">Tabla financiera de productos</h3><p class="text-xs text-slate-500 mt-1">Si un producto no tiene costo_unitario, utilidad y margen se muestran como no calculables para no inventar datos.</p></div>
      <a href="?<?= http_build_query(array_merge($_GET, ['export'=>'csv'])) ?>" class="no-print inline-flex items-center justify-center px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-black"><i class="fas fa-file-csv mr-2"></i>Exportar CSV</a>
    </div>
    <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="py-2">Producto</th><th>Marca</th><th>Categoría</th><th class="text-right">Unidades</th><th class="text-right">Ingresos</th><th class="text-right">Costos reales</th><th class="text-right">Utilidad real</th><th class="text-right">Margen real</th><th class="text-right">Ventas sin costo</th></tr></thead><tbody><?php foreach($topProductos as $r): ?><tr class="border-b border-slate-100"><td class="py-2"><strong><?= htmlspecialchars($r['producto']) ?></strong><br><span class="text-xs text-slate-500"><?= htmlspecialchars($r['sku']) ?></span></td><td><?= htmlspecialchars($r['marca']) ?></td><td><?= htmlspecialchars($r['categoria']) ?></td><td class="text-right"><?= emxNum($r['unidades']) ?></td><td class="text-right"><?= emxMoney($r['ingresos']) ?></td><td class="text-right"><?= (float)$r['ventas_con_costo'] > 0 ? emxMoney($r['costos']) : 'N/D' ?></td><td class="text-right font-bold <?= (float)$r['utilidad'] >= 0 ? 'text-emerald-700' : 'text-red-700' ?>"><?= (float)$r['ventas_con_costo'] > 0 ? emxMoney($r['utilidad']) : 'N/D' ?></td><td class="text-right font-bold"><?= $r['margen'] !== null ? emxPct($r['margen']) : 'N/D' ?></td><td class="text-right <?= (float)$r['ventas_sin_costo'] > 0 ? 'text-amber-700 font-bold' : 'text-slate-400' ?>"><?= emxMoney($r['ventas_sin_costo']) ?></td></tr><?php endforeach; ?></tbody></table></div>
  </section>

  <section class="panel rounded-2xl p-6 mb-10">
    <h3 class="font-black mb-4">Productos con stock bajo</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
      <?php foreach($stockBajo as $r): ?>
      <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
        <p class="font-black text-slate-900"><?= htmlspecialchars($r['nombre']) ?></p>
        <p class="text-xs text-slate-500"><?= htmlspecialchars($r['categoria']) ?></p>
        <div class="flex justify-between text-sm mt-3"><span>Stock: <strong><?= (int)$r['stock_actual_global'] ?></strong></span><span>Mínimo: <strong><?= (int)$r['punto_reorden'] ?></strong></span></div>
        <div class="flex justify-between text-xs mt-2 text-slate-600"><span>Costo registrado: <?= is_numeric($r['costo']) ? emxMoney($r['costo']) : 'N/D' ?></span><span>Precio: <?= emxMoney($r['precio']) ?></span></div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<script>
const D = <?= json_encode($charts, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK) ?>;
Chart.defaults.font.family = 'Inter';
Chart.defaults.color = '#475569';
Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';
Chart.defaults.plugins.tooltip.padding = 12;
Chart.defaults.plugins.legend.labels.usePointStyle = true;

const money = v => '$' + Number(v || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
const pct = v => (v === null || v === undefined || isNaN(Number(v))) ? 'N/D' : Number(v).toFixed(1) + '%';

function hasCanvas(id){ return document.getElementById(id); }

function bar(id, labels, data, label, horizontal=false, formatter='money'){
  if(!hasCanvas(id)) return;
  new Chart(document.getElementById(id), {
    type:'bar',
    data:{labels, datasets:[{label, data, borderWidth:1, borderRadius:10, maxBarThickness:36}]},
    options:{
      responsive:true, maintainAspectRatio:false, indexAxis: horizontal?'y':'x',
      plugins:{legend:{display:false}, tooltip:{callbacks:{label:c=> label + ': ' + (formatter === 'unit' ? Number(c.raw || 0).toLocaleString('es-EC') : money(c.raw))}}},
      scales:{y:{beginAtZero:true, ticks:{callback:v=> horizontal ? v : (formatter === 'unit' ? v : money(v))}}, x:{ticks:{autoSkip:false, maxRotation:horizontal?0:45}}}
    }
  });
}
function doughnut(id, labels, data, label, formatter='money'){
  if(!hasCanvas(id)) return;
  new Chart(document.getElementById(id), {
    type:'doughnut',
    data:{labels, datasets:[{label, data, borderWidth:2}]},
    options:{responsive:true, maintainAspectRatio:false, cutout:'62%', plugins:{legend:{position:'bottom'}, tooltip:{callbacks:{label:c=> c.label + ': ' + (formatter === 'unit' ? c.raw : money(c.raw))}}}}
  });
}

if(hasCanvas('ventasUtilidad')){
  new Chart(document.getElementById('ventasUtilidad'), {
    data:{
      labels:D.series_labels,
      datasets:[
        {type:'bar', label:'Ingresos', data:D.series_ingresos, borderRadius:10, yAxisID:'y'},
        {type:'line', label:'Utilidad real', data:D.series_utilidad, tension:.35, borderWidth:3, fill:false, yAxisID:'y'},
        {type:'line', label:'Margen real %', data:D.series_margen, tension:.35, borderWidth:2, yAxisID:'y2'},
        {type:'bar', label:'Pedidos', data:D.series_pedidos, borderRadius:10, yAxisID:'y1'}
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false, interaction:{mode:'index', intersect:false},
      plugins:{tooltip:{callbacks:{label:c=> c.dataset.label.includes('%') ? c.dataset.label + ': ' + pct(c.raw) : (c.dataset.label === 'Pedidos' ? 'Pedidos: '+c.raw : c.dataset.label + ': ' + money(c.raw))}}},
      scales:{y:{beginAtZero:true, ticks:{callback:money}}, y1:{beginAtZero:true, position:'right', grid:{drawOnChartArea:false}}, y2:{beginAtZero:true, position:'right', grid:{drawOnChartArea:false}, ticks:{callback:v=>v+'%'}}}
    }
  });
}
doughnut('pagosChart', D.pagos_labels, D.pagos_values, 'Ingresos');
new Chart(document.getElementById('categoriasChart'), {type:'bar', data:{labels:D.cat_labels, datasets:[{label:'Ingresos', data:D.cat_ingresos, borderRadius:10},{label:'Utilidad real', data:D.cat_utilidad, borderRadius:10}]}, options:{responsive:true, maintainAspectRatio:false, scales:{y:{beginAtZero:true, ticks:{callback:money}}, x:{ticks:{autoSkip:false, maxRotation:45}}}}});
bar('catMargenChart', D.cat_labels, D.cat_margen, 'Margen real', true, 'percent');
new Chart(document.getElementById('marcasChart'), {type:'bar', data:{labels:D.marca_labels, datasets:[{label:'Ingresos', data:D.marca_ingresos, borderRadius:10},{label:'Utilidad real', data:D.marca_utilidad, borderRadius:10},{label:'Unidades', data:D.marca_unidades, yAxisID:'y1', borderRadius:10}]}, options:{responsive:true, maintainAspectRatio:false, indexAxis:'y', scales:{x:{beginAtZero:true, ticks:{callback:money}}, y1:{beginAtZero:true, position:'right', grid:{drawOnChartArea:false}}}}});
new Chart(document.getElementById('topProductosChart'), {type:'bar', data:{labels:D.top_labels, datasets:[{label:'Unidades', data:D.top_units, borderRadius:10, xAxisID:'x1'},{label:'Ingresos', data:D.top_income, borderRadius:10, xAxisID:'x'}]}, options:{responsive:true, maintainAspectRatio:false, indexAxis:'y', scales:{x:{beginAtZero:true, ticks:{callback:money}}, x1:{beginAtZero:true, position:'top', grid:{drawOnChartArea:false}}}}});
new Chart(document.getElementById('utilidadChart'), {type:'bar', data:{labels:D.profit_labels, datasets:[{label:'Utilidad real', data:D.profit_values, borderRadius:10},{label:'Margen real %', data:D.profit_margin, type:'line', yAxisID:'y1', tension:.3}]}, options:{responsive:true, maintainAspectRatio:false, indexAxis:'y', scales:{x:{beginAtZero:true, ticks:{callback:money}}, y1:{beginAtZero:true, position:'right', grid:{drawOnChartArea:false}, ticks:{callback:v=>v+'%'}}}}});
bar('menosVendidosChart', D.menos_labels, D.menos_units, 'Unidades vendidas', true, 'unit');
new Chart(document.getElementById('ultimos3Chart'), {type:'bar', data:{labels:D.mes3_labels, datasets:[{label:'Ingresos', data:D.mes3_ingresos, borderRadius:10},{label:'Pedidos', data:D.mes3_pedidos, yAxisID:'y1', borderRadius:10},{label:'Ticket promedio', data:D.mes3_ticket, type:'line', tension:.35}]}, options:{responsive:true, maintainAspectRatio:false, scales:{y:{beginAtZero:true, ticks:{callback:money}}, y1:{beginAtZero:true, position:'right', grid:{drawOnChartArea:false}}}}});
new Chart(document.getElementById('mes12Chart'), {type:'line', data:{labels:D.mes12_labels, datasets:[{label:'Ingresos', data:D.mes12_ingresos, tension:.35, fill:true, borderWidth:3},{label:'Pedidos', data:D.mes12_pedidos, type:'bar', yAxisID:'y1', borderRadius:8}]}, options:{responsive:true, maintainAspectRatio:false, scales:{y:{beginAtZero:true, ticks:{callback:money}}, y1:{beginAtZero:true, position:'right', grid:{drawOnChartArea:false}}}}});
doughnut('devEstadosChart', D.dev_labels, D.dev_values, 'Casos', 'unit');
bar('devMotivosChart', D.motivo_labels, D.motivo_values, 'Casos', true, 'unit');
</script>
<script src="assets/emx_modales.js"></script>
</body>
</html>
