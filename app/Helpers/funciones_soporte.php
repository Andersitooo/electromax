<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_soporte.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

// funciones_soporte.php - Funciones compartidas del Centro de Soporte

if (!function_exists('emxSoporteTableExists')) {
function emxSoporteTableExists($pdo, $tabla) {
    static $cache = [];
    if (array_key_exists($tabla, $cache)) return $cache[$tabla];
    try {
        $st = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name=? LIMIT 1");
        $st->execute([$tabla]);
        return $cache[$tabla] = (bool)$st->fetchColumn();
    } catch (Throwable $e) {
        return $cache[$tabla] = false;
    }
}
}

if (!function_exists('emxSoporteMotivos')) {
function emxSoporteMotivos() {
    return [
        'pedido' => 'Consulta sobre pedido',
        'pago_factura' => 'Pago o factura',
        'entrega' => 'Entrega o seguimiento',
        'devolucion_garantia' => 'Devolución o garantía',
        'cuenta' => 'Cuenta o acceso',
        'general' => 'Consulta general',
    ];
}
}

if (!function_exists('emxSoporteEstados')) {
function emxSoporteEstados() {
    return [
        'abierto' => 'Abierto',
        'en_revision' => 'En revisión',
        'respondido' => 'Respondido',
        'esperando_cliente' => 'Esperando cliente',
        'cerrado' => 'Cerrado',
    ];
}
}

if (!function_exists('emxSoportePrioridades')) {
function emxSoportePrioridades() {
    return [
        'baja' => 'Baja',
        'media' => 'Media',
        'alta' => 'Alta',
    ];
}
}

if (!function_exists('emxSoportePrioridadPorMotivo')) {
function emxSoportePrioridadPorMotivo($motivo) {
    $motivo = (string)$motivo;
    if (in_array($motivo, ['pago_factura','entrega','devolucion_garantia'], true)) return 'alta';
    if (in_array($motivo, ['pedido','cuenta'], true)) return 'media';
    return 'baja';
}
}

if (!function_exists('emxSoporteLabel')) {
function emxSoporteLabel($mapa, $valor) {
    return $mapa[$valor] ?? ucfirst(str_replace('_', ' ', (string)$valor));
}
}

if (!function_exists('emxSoporteEstadoClase')) {
function emxSoporteEstadoClase($estado) {
    return [
        'abierto' => 'bg-blue-50 text-blue-700 border-blue-200',
        'en_revision' => 'bg-amber-50 text-amber-700 border-amber-200',
        'respondido' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'esperando_cliente' => 'bg-purple-50 text-purple-700 border-purple-200',
        'cerrado' => 'bg-slate-100 text-slate-600 border-slate-200',
    ][$estado] ?? 'bg-slate-100 text-slate-600 border-slate-200';
}
}

if (!function_exists('emxSoportePrioridadClase')) {
function emxSoportePrioridadClase($prioridad) {
    return [
        'alta' => 'bg-red-50 text-red-700 border-red-200',
        'media' => 'bg-amber-50 text-amber-700 border-amber-200',
        'baja' => 'bg-slate-50 text-slate-600 border-slate-200',
    ][$prioridad] ?? 'bg-slate-50 text-slate-600 border-slate-200';
}
}

if (!function_exists('emxSoporteCodigo')) {
function emxSoporteCodigo($id) {
    return strtoupper(substr(str_replace('-', '', (string)$id), 0, 8));
}
}

if (!function_exists('emxSoporteUpload')) {
function emxSoporteUpload($campo = 'adjunto') {
    if (empty($_FILES[$campo]['name']) || !is_uploaded_file($_FILES[$campo]['tmp_name'])) return null;

    $maxBytes = 5 * 1024 * 1024;
    if ((int)($_FILES[$campo]['size'] ?? 0) > $maxBytes) {
        throw new Exception('El archivo adjunto no debe superar 5 MB.');
    }

    $ext = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg','jpeg','png','webp','pdf'];
    if (!in_array($ext, $permitidas, true)) {
        throw new Exception('Adjunto no permitido. Usa JPG, PNG, WEBP o PDF.');
    }

    $baseDir = EMX_ROOT . '/uploads/soporte/' . date('Y/m');
    if (!is_dir($baseDir)) @mkdir($baseDir, 0775, true);

    $nombre = 'ticket_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destino = $baseDir . '/' . $nombre;

    if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $destino)) {
        throw new Exception('No se pudo guardar el archivo adjunto.');
    }

    return 'uploads/soporte/' . date('Y/m') . '/' . $nombre;
}
}

if (!function_exists('emxSoporteNotificarAdmins')) {
function emxSoporteNotificarAdmins($pdo, $titulo, $mensaje, $enlace) {
    if (!function_exists('enviarNotificacionCliente')) return;
    try {
        $st = $pdo->query("
            SELECT u.id
            FROM usuarios u
            JOIN roles r ON r.id = u.rol_id
            WHERE UPPER(r.nombre) IN ('ADMIN','SUPERADMIN')
              AND u.deleted_at IS NULL
        ");
        foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $adminId) {
            enviarNotificacionCliente($pdo, $adminId, 'soporte', $titulo, $mensaje, $enlace, 'soporte');
        }
    } catch (Throwable $e) {
        error_log('[soporte] No se pudo notificar admins: ' . $e->getMessage());
    }
}
}

if (!function_exists('emxSoporteNotificarCliente')) {
function emxSoporteNotificarCliente($pdo, $usuarioId, $titulo, $mensaje, $enlace) {
    if (!function_exists('enviarNotificacionCliente')) return;
    try {
        enviarNotificacionCliente($pdo, $usuarioId, 'soporte', $titulo, $mensaje, $enlace, 'soporte');
    } catch (Throwable $e) {
        error_log('[soporte] No se pudo notificar cliente: ' . $e->getMessage());
    }
}
}
?>
