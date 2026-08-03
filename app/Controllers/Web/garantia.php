<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
emxVerificarCsrfSiPOST();
require_once EMX_HELPERS_PATH . '/funciones_garantias.php';

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: auth.php?action=login');
    exit;
}

$userId = $_SESSION['usuario_id'];
$detalleId = trim($_GET['detalle_id'] ?? $_POST['detalle_id'] ?? '');
$msg = null; $error = null;

$stmt = $pdo->prepare("\n    SELECT dp.*, p.nombre AS producto_nombre, p.slug, ped.id AS pedido_id, ped.created_at AS fecha_pedido, ped.usuario_id, ped.estado AS pedido_estado\n    FROM detalle_pedidos dp\n    INNER JOIN pedidos ped ON ped.id = dp.pedido_id\n    LEFT JOIN productos p ON p.id = dp.producto_id\n    WHERE dp.id = ? AND ped.usuario_id = ?\n");
$stmt->execute([$detalleId, $userId]);
$detalle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$detalle) {
    http_response_code(404);
    exit('Producto de pedido no encontrado.');
}

$garantias = [];
if (!empty($detalle['garantia_snapshot'])) {
    $garantias = json_decode($detalle['garantia_snapshot'], true);
}
if (!is_array($garantias) || !$garantias) $garantias = emxObtenerGarantiasProducto($pdo, $detalle['producto_id']);
$vigente = emxDetalleTieneGarantiaVigente($detalle);
$diasDesdeCompra = floor((time() - strtotime($detalle['fecha_pedido'])) / 86400);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$vigente) throw new Exception('La garantía de este producto ya no está vigente.');
        $componente = trim($_POST['componente_afectado'] ?? 'Garantía general');
        $serie = trim($_POST['numero_serie'] ?? '');
        $descripcion = trim($_POST['descripcion_falla'] ?? '');
        if ($descripcion === '') throw new Exception('Describe la falla para abrir el caso de garantía.');
        $historial = [[
            'estado' =>'pendiente_revision',
            'descripcion' =>'Caso de garantía creado por el cliente.',
            'fecha' =>date('Y-m-d H:i:s'),
            'icono' =>'fa-shield-halved'
        ]];
        $st = $pdo->prepare("INSERT INTO garantia_casos (pedido_id, detalle_pedido_id, producto_id, usuario_id, numero_serie, componente_afectado, descripcion_falla, estado, historial_estados) VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente_revision', ?::jsonb)");
        $st->execute([$detalle['pedido_id'], $detalleId, $detalle['producto_id'], $userId, $serie, $componente, $descripcion, json_encode($historial, JSON_UNESCAPED_UNICODE)]);
        $msg = 'Caso de garantía registrado. El equipo técnico lo revisará desde admin.';
    } catch (Exception $e) { $error = $e->getMessage(); }
}

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/frontend/garantia_view.php
require EMX_VIEWS_PATH . '/frontend/garantia_view.php';
exit;
