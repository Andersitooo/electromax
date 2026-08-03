<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
if (file_exists(EMX_HELPERS_PATH . '/funciones_notificaciones.php')) require_once EMX_HELPERS_PATH . '/funciones_notificaciones.php';
require_once EMX_HELPERS_PATH . '/funciones_soporte.php';

emxRequireLogin();

$usuarioId = $_SESSION['usuario_id'];
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
        if ($accion === 'crear_ticket') {
            $motivo = $_POST['motivo'] ?? 'general';
            if (!isset($motivos[$motivo])) $motivo = 'general';

            $asunto = trim((string)($_POST['asunto'] ?? ''));
            $mensaje = trim((string)($_POST['mensaje'] ?? ''));
            $pedidoId = trim((string)($_POST['pedido_id'] ?? ''));

            if (mb_strlen($asunto) < 4) throw new Exception('Escribe un asunto un poco más claro.');
            if (mb_strlen($mensaje) < 10) throw new Exception('Describe tu consulta con un poco más de detalle.');

            $pedidoIdFinal = null;
            if ($pedidoId !== '') {
                $stPed = $pdo->prepare("SELECT id FROM pedidos WHERE id = ? AND usuario_id = ? LIMIT 1");
                $stPed->execute([$pedidoId, $usuarioId]);
                $pedidoIdFinal = $stPed->fetchColumn();
                if (!$pedidoIdFinal) throw new Exception('El pedido seleccionado no pertenece a tu cuenta.');
            }

            $prioridad = emxSoportePrioridadPorMotivo($motivo);
            $adjunto = emxSoporteUpload('adjunto');

            $pdo->beginTransaction();
            $st = $pdo->prepare("
                INSERT INTO soporte_tickets
                (usuario_id, pedido_id, asunto, motivo, estado, prioridad, ultimo_mensaje, ultimo_mensaje_por)
                VALUES (?, ?, ?, ?, 'abierto', ?, ?, 'cliente')
                RETURNING id
            ");
            $st->execute([$usuarioId, $pedidoIdFinal, $asunto, $motivo, $prioridad, mb_substr($mensaje, 0, 500)]);
            $ticketId = $st->fetchColumn();

            $stMsg = $pdo->prepare("
                INSERT INTO soporte_mensajes (ticket_id, usuario_id, enviado_por, mensaje, adjunto_url)
                VALUES (?, ?, 'cliente', ?, ?)
            ");
            $stMsg->execute([$ticketId, $usuarioId, $mensaje, $adjunto]);

            $pdo->commit();

            emxSoporteNotificarAdmins(
                $pdo,
                'Nuevo ticket de soporte',
                'El cliente creó el ticket #' . emxSoporteCodigo($ticketId) . ': ' . $asunto,
                'soporte_admin.php?ticket=' . urlencode($ticketId)
            );

            header('Location: soporte.php?ticket=' . urlencode($ticketId) . '&msg=Ticket+creado+correctamente&msg_type=success');
            exit;
        }

        if ($accion === 'responder_ticket') {
            $ticketId = trim((string)($_POST['ticket_id'] ?? ''));
            $mensaje = trim((string)($_POST['mensaje'] ?? ''));
            if ($mensaje === '' || mb_strlen($mensaje) < 3) throw new Exception('Escribe una respuesta válida.');

            $st = $pdo->prepare("SELECT * FROM soporte_tickets WHERE id = ? AND usuario_id = ? LIMIT 1");
            $st->execute([$ticketId, $usuarioId]);
            $ticket = $st->fetch(PDO::FETCH_ASSOC);
            if (!$ticket) throw new Exception('Ticket no encontrado.');
            if ($ticket['estado'] === 'cerrado') throw new Exception('Este ticket ya está cerrado.');

            $adjunto = emxSoporteUpload('adjunto');

            $pdo->beginTransaction();
            $pdo->prepare("
                INSERT INTO soporte_mensajes (ticket_id, usuario_id, enviado_por, mensaje, adjunto_url)
                VALUES (?, ?, 'cliente', ?, ?)
            ")->execute([$ticketId, $usuarioId, $mensaje, $adjunto]);

            $pdo->prepare("
                UPDATE soporte_tickets
                SET estado = 'en_revision',
                    ultimo_mensaje = ?,
                    ultimo_mensaje_por = 'cliente',
                    cerrado_at = NULL
                WHERE id = ?
            ")->execute([mb_substr($mensaje, 0, 500), $ticketId]);

            $pdo->commit();

            emxSoporteNotificarAdmins(
                $pdo,
                'Cliente respondió ticket',
                'El cliente respondió el ticket #' . emxSoporteCodigo($ticketId),
                'soporte_admin.php?ticket=' . urlencode($ticketId)
            );

            header('Location: soporte.php?ticket=' . urlencode($ticketId) . '&msg=Respuesta+enviada&msg_type=success');
            exit;
        }

        if ($accion === 'cerrar_ticket') {
            $ticketId = trim((string)($_POST['ticket_id'] ?? ''));
            $st = $pdo->prepare("SELECT id FROM soporte_tickets WHERE id = ? AND usuario_id = ? LIMIT 1");
            $st->execute([$ticketId, $usuarioId]);
            if (!$st->fetchColumn()) throw new Exception('Ticket no encontrado.');

            $pdo->prepare("
                UPDATE soporte_tickets
                SET estado = 'cerrado',
                    cerrado_at = NOW(),
                    ultimo_mensaje = 'Ticket cerrado por el cliente.',
                    ultimo_mensaje_por = 'cliente'
                WHERE id = ?
            ")->execute([$ticketId]);

            header('Location: soporte.php?msg=Ticket+cerrado&msg_type=success');
            exit;
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header('Location: soporte.php?msg=' . urlencode($e->getMessage()) . '&msg_type=error');
        exit;
    }
}

$pedidos = [];
$tickets = [];
$ticketActual = null;
$mensajes = [];

if ($tablasOk) {
    $pedidos = [];
    try {
        $stPed = $pdo->prepare("
            SELECT id, created_at, total, estado
            FROM pedidos
            WHERE usuario_id = ?
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stPed->execute([$usuarioId]);
        $pedidos = $stPed->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}

    $stTickets = $pdo->prepare("
        SELECT t.*, p.total AS pedido_total, p.estado AS pedido_estado, p.created_at AS pedido_fecha
        FROM soporte_tickets t
        LEFT JOIN pedidos p ON p.id = t.pedido_id
        WHERE t.usuario_id = ?
        ORDER BY t.updated_at DESC
    ");
    $stTickets->execute([$usuarioId]);
    $tickets = $stTickets->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($_GET['ticket'])) {
        $st = $pdo->prepare("
            SELECT t.*, p.total AS pedido_total, p.estado AS pedido_estado, p.created_at AS pedido_fecha
            FROM soporte_tickets t
            LEFT JOIN pedidos p ON p.id = t.pedido_id
            WHERE t.id = ? AND t.usuario_id = ?
            LIMIT 1
        ");
        $st->execute([$_GET['ticket'], $usuarioId]);
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
// La vista está separada en: views/frontend/soporte_view.php
require EMX_VIEWS_PATH . '/frontend/soporte_view.php';
exit;
