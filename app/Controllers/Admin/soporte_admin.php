<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
if (file_exists(EMX_HELPERS_PATH . '/funciones_notificaciones.php')) require_once EMX_HELPERS_PATH . '/funciones_notificaciones.php';
require_once EMX_HELPERS_PATH . '/funciones_soporte.php';

emxRequireRole(['SUPERADMIN','ADMIN']);

$motivos = emxSoporteMotivos();
$estados = emxSoporteEstados();
$prioridades = emxSoportePrioridades();
$msg = $_GET['msg'] ?? '';
$msgType = $_GET['msg_type'] ?? 'success';
$tablasOk = emxSoporteTableExists($pdo, 'soporte_tickets') && emxSoporteTableExists($pdo, 'soporte_mensajes');

if ($tablasOk && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    emxVerificarCsrf();
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'responder') {
            $ticketId = trim((string)($_POST['ticket_id'] ?? ''));
            $mensaje = trim((string)($_POST['mensaje'] ?? ''));
            $nuevoEstado = $_POST['estado'] ?? 'respondido';

            if (!isset($estados[$nuevoEstado]) || $nuevoEstado === 'abierto') $nuevoEstado = 'respondido';
            if ($mensaje === '' || mb_strlen($mensaje) < 3) throw new Exception('Escribe una respuesta válida.');

            $st = $pdo->prepare("SELECT * FROM soporte_tickets WHERE id = ? LIMIT 1");
            $st->execute([$ticketId]);
            $ticket = $st->fetch(PDO::FETCH_ASSOC);
            if (!$ticket) throw new Exception('Ticket no encontrado.');

            $pdo->beginTransaction();
            $pdo->prepare("
                INSERT INTO soporte_mensajes (ticket_id, usuario_id, enviado_por, mensaje)
                VALUES (?, ?, 'admin', ?)
            ")->execute([$ticketId, $_SESSION['usuario_id'], $mensaje]);

            $cerradoAt = $nuevoEstado === 'cerrado' ? ', cerrado_at = NOW()' : ', cerrado_at = NULL';
            $pdo->prepare("
                UPDATE soporte_tickets
                SET estado = ?,
                    ultimo_mensaje = ?,
                    ultimo_mensaje_por = 'admin'
                    {$cerradoAt}
                WHERE id = ?
            ")->execute([$nuevoEstado, mb_substr($mensaje, 0, 500), $ticketId]);

            $pdo->commit();

            emxSoporteNotificarCliente(
                $pdo,
                $ticket['usuario_id'],
                'Respuesta de soporte',
                'ElectroMax respondió tu ticket #' . emxSoporteCodigo($ticketId),
                'soporte.php?ticket=' . urlencode($ticketId)
            );

            header('Location: soporte_admin.php?ticket=' . urlencode($ticketId) . '&msg=Respuesta+enviada&msg_type=success');
            exit;
        }

        if ($accion === 'cambiar_estado') {
            $ticketId = trim((string)($_POST['ticket_id'] ?? ''));
            $estado = $_POST['estado'] ?? 'en_revision';
            if (!isset($estados[$estado])) throw new Exception('Estado no válido.');

            $st = $pdo->prepare("SELECT usuario_id FROM soporte_tickets WHERE id = ? LIMIT 1");
            $st->execute([$ticketId]);
            $usuarioId = $st->fetchColumn();
            if (!$usuarioId) throw new Exception('Ticket no encontrado.');

            $cerradoAt = $estado === 'cerrado' ? ', cerrado_at = NOW()' : ', cerrado_at = NULL';
            $pdo->prepare("UPDATE soporte_tickets SET estado = ? {$cerradoAt} WHERE id = ?")->execute([$estado, $ticketId]);

            emxSoporteNotificarCliente(
                $pdo,
                $usuarioId,
                'Estado de soporte actualizado',
                'Tu ticket #' . emxSoporteCodigo($ticketId) . ' cambió a: ' . emxSoporteLabel($estados, $estado),
                'soporte.php?ticket=' . urlencode($ticketId)
            );

            header('Location: soporte_admin.php?ticket=' . urlencode($ticketId) . '&msg=Estado+actualizado&msg_type=success');
            exit;
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header('Location: soporte_admin.php?msg=' . urlencode($e->getMessage()) . '&msg_type=error');
        exit;
    }
}

$fEstado = $_GET['estado'] ?? '';
$fMotivo = $_GET['motivo'] ?? '';
$q = trim((string)($_GET['q'] ?? ''));

$tickets = [];
$ticketActual = null;
$mensajes = [];
$stats = ['abiertos'=>0, 'revision'=>0, 'respondidos'=>0, 'cerrados'=>0];

if ($tablasOk) {
    $stats = [
        'abiertos' => (int)$pdo->query("SELECT COUNT(*) FROM soporte_tickets WHERE estado = 'abierto'")->fetchColumn(),
        'revision' => (int)$pdo->query("SELECT COUNT(*) FROM soporte_tickets WHERE estado = 'en_revision'")->fetchColumn(),
        'respondidos' => (int)$pdo->query("SELECT COUNT(*) FROM soporte_tickets WHERE estado IN ('respondido','esperando_cliente')")->fetchColumn(),
        'cerrados' => (int)$pdo->query("SELECT COUNT(*) FROM soporte_tickets WHERE estado = 'cerrado'")->fetchColumn(),
    ];

    $where = [];
    $params = [];

    if ($fEstado !== '' && isset($estados[$fEstado])) {
        $where[] = 't.estado = ?';
        $params[] = $fEstado;
    }
    if ($fMotivo !== '' && isset($motivos[$fMotivo])) {
        $where[] = 't.motivo = ?';
        $params[] = $fMotivo;
    }
    if ($q !== '') {
        $where[] = "(LOWER(t.asunto) LIKE LOWER(?) OR LOWER(u.email) LIKE LOWER(?) OR LOWER(u.nombres || ' ' || u.apellidos) LIKE LOWER(?))";
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $st = $pdo->prepare("
        SELECT t.*, u.nombres, u.apellidos, u.email,
               p.total AS pedido_total, p.estado AS pedido_estado, p.created_at AS pedido_fecha
        FROM soporte_tickets t
        JOIN usuarios u ON u.id = t.usuario_id
        LEFT JOIN pedidos p ON p.id = t.pedido_id
        {$whereSql}
        ORDER BY
            CASE t.prioridad WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END,
            CASE t.estado WHEN 'abierto' THEN 1 WHEN 'en_revision' THEN 2 WHEN 'esperando_cliente' THEN 3 WHEN 'respondido' THEN 4 ELSE 5 END,
            t.updated_at DESC
        LIMIT 150
    ");
    $st->execute($params);
    $tickets = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($_GET['ticket'])) {
        $st = $pdo->prepare("
            SELECT t.*, u.nombres, u.apellidos, u.email, u.telefono,
                   p.total AS pedido_total, p.estado AS pedido_estado, p.created_at AS pedido_fecha
            FROM soporte_tickets t
            JOIN usuarios u ON u.id = t.usuario_id
            LEFT JOIN pedidos p ON p.id = t.pedido_id
            WHERE t.id = ?
            LIMIT 1
        ");
        $st->execute([$_GET['ticket']]);
        $ticketActual = $st->fetch(PDO::FETCH_ASSOC);

        if ($ticketActual) {
            $stMsg = $pdo->prepare("
                SELECT m.*, u.nombres, u.apellidos
                FROM soporte_mensajes m
                LEFT JOIN usuarios u ON u.id = m.usuario_id
                WHERE m.ticket_id = ?
                ORDER BY m.created_at ASC
            ");
            $stMsg->execute([$ticketActual['id']]);
            $mensajes = $stMsg->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/admin/soporte_admin_view.php
require EMX_VIEWS_PATH . '/admin/soporte_admin_view.php';
exit;
