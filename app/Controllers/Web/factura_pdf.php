<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    exit('Debes iniciar sesión.');
}

$id = trim($_GET['id'] ?? '');
if (!preg_match('/^[a-f0-9-]{36}$/i', $id)) {
    http_response_code(400);
    exit('Factura inválida.');
}

$rol = $_SESSION['usuario_rol'] ?? 'CLIENTE';
$sql = "SELECT f.*, p.usuario_id FROM facturas f LEFT JOIN pedidos p ON p.id = f.pedido_id WHERE f.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$factura = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$factura) {
    http_response_code(404);
    exit('Factura no encontrada.');
}

$esAdmin = in_array($rol, ['ADMIN','SUPERADMIN'], true);
$esDueno = ($factura['usuario_id'] ?? null) === ($_SESSION['usuario_id'] ?? null);
if (!$esAdmin && !$esDueno) {
    http_response_code(403);
    exit('No puedes ver esta factura.');
}

$path = EMX_ROOT . '/' . ltrim($factura['pdf_url'] ?? '', '/');
if (!is_file($path)) {
    http_response_code(404);
    exit('PDF no encontrado.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="factura_' . preg_replace('/[^0-9A-Za-z_-]/', '_', $factura['numero_factura']) . '.pdf"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
