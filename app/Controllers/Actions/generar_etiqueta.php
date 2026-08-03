<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_CONFIG_PATH . '/company.php';
emxRequireLogin();
$dev_id = $_GET['id'] ?? null;
if (!emxIsUuid($dev_id)) { http_response_code(400); exit('Devolución inválida'); }

$stmt = $pdo->prepare("\n    SELECT d.*, u.nombres, u.apellidos, u.telefono, u.email, p.direccion AS direccion_cliente, p.ciudad\n    FROM devoluciones d\n    JOIN usuarios u ON d.usuario_id = u.id\n    JOIN pedidos p ON d.pedido_id = p.id\n    WHERE d.id = ?\n");
$stmt->execute([$dev_id]);
$dev = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$dev) die('Devolución no encontrada');
if (!emxEsAdmin() && $dev['usuario_id'] !== emxUsuarioId()) { http_response_code(403); exit('No puedes ver esta etiqueta.'); }
if (!in_array($dev['estado'], ['autorizada_retorno','en_camino_retorno','recibido_almacen','en_inspeccion'], true)) { http_response_code(403); exit('La etiqueta solo está disponible cuando el retorno fue autorizado.'); }
$codigo = $dev['codigo_etiqueta'] ?: ($dev['codigo_guia'] ?: 'EMX-RET-' . strtoupper(substr($dev['id'],0,8)));
?><!DOCTYPE html><html><head>
<!-- Favicon ElectroMax global -->
<link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
<link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
<link rel="manifest" href="assets/favicon/site.webmanifest">
<meta name="theme-color" content="#0b4da2">
<title>Etiqueta de Devolución #<?= htmlspecialchars($codigo) ?></title><script src="https://cdn.tailwindcss.com"></script></head><body class="p-8 bg-slate-100"><div class="max-w-2xl mx-auto border-2 border-dashed border-slate-400 p-6 bg-white"><div class="text-center mb-6"><img src="<?= htmlspecialchars(EMX_EMPRESA_LOGO) ?>" class="h-20 mx-auto mb-3" alt="ElectroMax"><p class="text-slate-600 font-semibold">Etiqueta de Devolución Autorizada</p></div><div class="bg-slate-100 p-4 rounded mb-6 text-center"><p class="text-sm text-slate-600 mb-1">Código de Autorización:</p><p class="text-4xl font-bold text-slate-900"><?= htmlspecialchars($codigo) ?></p></div><div class="grid grid-cols-2 gap-6 mb-6"><div><h3 class="font-bold text-slate-800 mb-2">DESTINATARIO:</h3><p class="text-sm"><strong><?= htmlspecialchars(EMX_EMPRESA_NOMBRE) ?></strong>- Centro de Devoluciones</p><p class="text-sm"><?= htmlspecialchars(EMX_EMPRESA_DIRECCION) ?></p><p class="text-sm">Tel: <?= htmlspecialchars(EMX_EMPRESA_TELEFONO) ?></p></div><div><h3 class="font-bold text-slate-800 mb-2">REMITE:</h3><p class="text-sm"><?= htmlspecialchars($dev['nombres'] . ' ' . $dev['apellidos']) ?></p><p class="text-sm"><?= htmlspecialchars($dev['direccion_cliente']) ?></p><p class="text-sm"><?= htmlspecialchars($dev['ciudad']) ?></p><p class="text-sm"><?= htmlspecialchars($dev['telefono']) ?></p></div></div><div class="border-t border-slate-300 pt-4"><p class="text-sm"><strong>Pedido Original:</strong>#<?= strtoupper(substr($dev['pedido_id'], 0, 8)) ?></p><p class="text-sm"><strong>Motivo:</strong><?= htmlspecialchars(str_replace('_',' ', $dev['motivo'])) ?></p><p class="text-sm"><strong>Estado:</strong><?= htmlspecialchars(str_replace('_',' ', $dev['estado'])) ?></p></div><div class="mt-8 text-center text-xs text-slate-500"><p>Imprima esta etiqueta y péguela en el paquete. No envíe productos no autorizados.</p><p class="mt-2"><?= date('d/m/Y H:i') ?></p></div><div class="mt-6 no-print"><button onclick="window.print()" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700">Imprimir Etiqueta</button></div></div><script src="assets/emx_modales.js"></script></body></html>