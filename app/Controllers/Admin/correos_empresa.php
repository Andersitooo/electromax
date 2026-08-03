<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_HELPERS_PATH . '/funciones_facturacion.php';
emxRequireRole(['SUPERADMIN','ADMIN']);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function tablaExiste($pdo, $tabla){
    try { $st=$pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name=? LIMIT 1"); $st->execute([$tabla]); return (bool)$st->fetchColumn(); }
    catch(Throwable $e){ return false; }
}
if (!tablaExiste($pdo, 'email_outbox')) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS email_outbox (
        id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
        usuario_id UUID REFERENCES usuarios(id) ON DELETE SET NULL,
        email_destino VARCHAR(180) NOT NULL,
        asunto VARCHAR(250) NOT NULL,
        cuerpo_html TEXT NOT NULL,
        archivo_adjunto TEXT,
        tipo VARCHAR(50) DEFAULT 'general',
        estado VARCHAR(30) DEFAULT 'pendiente',
        error_msg TEXT,
        created_at TIMESTAMP DEFAULT NOW(),
        enviado_at TIMESTAMP
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_email_outbox_estado ON email_outbox(estado, created_at)");
}

$estado = $_GET['estado'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$q = trim($_GET['q'] ?? '');
$allowedEstados = ['pendiente','enviado','error'];
$where=[]; $params=[];
if (in_array($estado, $allowedEstados, true)) { $where[]='eo.estado = ?'; $params[]=$estado; }
if ($tipo !== '') { $where[]='eo.tipo = ?'; $params[]=$tipo; }
if ($q !== '') { $where[]='(eo.email_destino ILIKE ? OR eo.asunto ILIKE ?)'; $params[]='%'.$q.'%'; $params[]='%'.$q.'%'; }
$sqlWhere = $where ? ('WHERE '.implode(' AND ', $where)) : '';

$tipos = [];
try { $tipos = $pdo->query("SELECT DISTINCT tipo FROM email_outbox WHERE tipo IS NOT NULL ORDER BY tipo")->fetchAll(PDO::FETCH_COLUMN); } catch(Throwable $e) {}
$stats = ['total'=>0,'enviado'=>0,'pendiente'=>0,'error'=>0];
try {
    foreach ($pdo->query("SELECT estado, COUNT(*) total FROM email_outbox GROUP BY estado") as $r) { $stats[$r['estado'] ?: 'pendiente']=(int)$r['total']; $stats['total'] += (int)$r['total']; }
} catch(Throwable $e) {}

$st = $pdo->prepare("SELECT eo.*, u.nombres, u.apellidos FROM email_outbox eo LEFT JOIN usuarios u ON u.id = eo.usuario_id $sqlWhere ORDER BY eo.created_at DESC LIMIT 200");
$st->execute($params);
$correos = $st->fetchAll(PDO::FETCH_ASSOC);

$preview = null;
if (!empty($_GET['ver'])) {
    $st = $pdo->prepare("SELECT * FROM email_outbox WHERE id = ? LIMIT 1");
    $st->execute([$_GET['ver']]);
    $preview = $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/admin/correos_empresa_view.php
require EMX_VIEWS_PATH . '/admin/correos_empresa_view.php';
exit;
