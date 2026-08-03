<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
emxVerificarCsrfSiPOST();
require_once EMX_HELPERS_PATH . '/flujo_admin.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth.php?action=login');
    exit();
}

$rol_actual = $_SESSION['usuario_rol'] ?? 'CLIENTE';
if (!in_array($rol_actual, ['SUPERADMIN', 'ADMIN'], true)) {
    header('Location: mi_cuenta.php?seccion=devoluciones&msg=' . urlencode('No tienes permisos para recibir devoluciones.') . '&msg_type=error');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dev_id = $_POST['dev_id'] ?? null;
    $comentario = trim($_POST['comentario_admin'] ?? 'Producto recibido físicamente en almacén.');

    try {
        if (!$dev_id) throw new Exception('ID inválido.');
        $pdo->beginTransaction();
        emxEjecutarAccionDevolucion($pdo, [
            'dev_id' =>$dev_id,
            'accion_flujo' =>'recibir_almacen',
            'comentario_admin' =>$comentario
        ], $_SESSION['usuario_id'], $rol_actual);
        $pdo->commit();
        header('Location: admin.php?module=devoluciones&msg=' . urlencode('Producto recibido en almacén. Siguiente paso: inspección técnica.') . '&msg_type=success');
        exit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header('Location: admin.php?module=devoluciones&msg=' . urlencode($e->getMessage()) . '&msg_type=error');
        exit();
    }
}

$dev_id = $_GET['id'] ?? null;
if (!$dev_id) {
    header('Location: admin.php?module=devoluciones&msg=' . urlencode('ID inválido.') . '&msg_type=error');
    exit();
}

$stmt = $pdo->prepare("SELECT d.*, u.nombres, u.apellidos FROM devoluciones d LEFT JOIN usuarios u ON d.usuario_id = u.id WHERE d.id = ?");
$stmt->execute([$dev_id]);
$dev = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dev) {
    header('Location: admin.php?module=devoluciones&msg=' . urlencode('Devolución no encontrada.') . '&msg_type=error');
    exit();
}
?><!DOCTYPE html><html lang="es"><head>
<!-- Favicon ElectroMax global -->
<link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
<link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
<link rel="manifest" href="assets/favicon/site.webmanifest">
<meta name="theme-color" content="#0b4da2">
<meta charset="UTF-8"><title>Confirmar recepción - ElectroMax</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head><body class="bg-slate-100 min-h-screen flex items-center justify-center p-6"><div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6"><div class="w-14 h-14 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center mb-4"><i class="fas fa-warehouse text-2xl"></i></div><h1 class="text-xl font-bold text-slate-900 mb-2">Confirmar recepción en almacén</h1><p class="text-sm text-slate-600 mb-4">Esta acción ya no se ejecuta por GET automáticamente. Confirma solo si el producto llegó físicamente.
        </p><div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-4 text-sm"><p><strong>Caso:</strong>#<?= htmlspecialchars(strtoupper(substr($dev['id'], 0, 8))) ?></p><p><strong>Cliente:</strong><?= htmlspecialchars(($dev['nombres'] ?? '') . ' ' . ($dev['apellidos'] ?? '')) ?></p><p><strong>Estado actual:</strong><?= htmlspecialchars(emxTextoEstado(emxEstadoDevolucionNormalizado($dev['estado']))) ?></p></div><form method="POST" class="space-y-4"><?= emxCsrfCampo() ?><input type="hidden" name="dev_id" value="<?= htmlspecialchars($dev_id) ?>"><div><label class="block text-sm font-semibold text-slate-700 mb-1">Comentario de recepción</label><textarea name="comentario_admin" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Ej: caja recibida completa, pendiente inspección técnica.">Producto recibido físicamente en almacén.</textarea></div><div class="flex gap-3 justify-end"><a href="admin.php?module=devoluciones" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200">Cancelar</a><button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold"><i class="fas fa-check mr-1"></i>Confirmar recepción
                </button></div></form></div><script src="assets/emx_modales.js"></script></body></html>