<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
emxVerificarCsrfSiPOST();
require_once EMX_HELPERS_PATH . '/flujo_admin.php';
emxGarantizarColumnasDevoluciones($pdo);

date_default_timezone_set('America/Guayaquil');

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mi_cuenta.php?seccion=pedidos');
    exit();
}

$user_id = $_SESSION['usuario_id'];
$pedido_id = $_POST['pedido_id'] ?? null;
$motivo = $_POST['motivo'] ?? null;
$descripcion = trim($_POST['descripcion'] ?? '');

if (!$pedido_id || !$motivo || $descripcion === '') {
    header('Location: mi_cuenta.php?seccion=devoluciones&msg=' . urlencode('Faltan datos obligatorios.') . '&msg_type=error');
    exit();
}

function emxSubirFotosDevolucion($campo = 'fotos', $pedido_id = null) {
    $destino = emxCarpetaDevolucionUploads($pedido_id ?: ($_POST['pedido_id'] ?? 'sin-pedido'));
    return emxSubirArchivosMultiplesSeguro($campo, $destino, [
        'prefijo' =>'dev_' . preg_replace('/[^a-z0-9]/i', '', substr((string)($pedido_id ?: ($_POST['pedido_id'] ?? 'pedido')), 0, 8)),
        'max_bytes' =>5 * 1024 * 1024,
        'mime' =>[
            'image/jpeg' =>'jpg',
            'image/png' =>'png',
            'image/webp' =>'webp'
        ]
    ]);
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id, usuario_id, estado, created_at FROM pedidos WHERE id = ? AND usuario_id = ? FOR UPDATE");
    $stmt->execute([$pedido_id, $user_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        throw new Exception('Pedido no encontrado o no autorizado.');
    }

    if (in_array($pedido['estado'], ['Cancelado', 'Reembolsado'], true)) {
        throw new Exception('No se puede abrir una devolución para un pedido cancelado o ya reembolsado.');
    }

    $fecha_pedido = new DateTime($pedido['created_at']);
    $hoy = new DateTime();
    if ($hoy->diff($fecha_pedido)->days >30) {
        throw new Exception('El plazo de 30 días para devoluciones ha expirado.');
    }

    $stmtExiste = $pdo->prepare("SELECT id FROM devoluciones WHERE pedido_id = ? AND estado NOT IN ('cerrada', 'rechazada', 'reembolsado') LIMIT 1");
    $stmtExiste->execute([$pedido_id]);
    if ($stmtExiste->fetchColumn()) {
        throw new Exception('Ya existe un caso abierto para este pedido. Revisa la sección de devoluciones.');
    }

    $motivosCulpaTienda = ['defectuoso', 'producto_incorrecto', 'faltan_piezas', 'caja_abierta', 'danado_envio'];
    $motivosDecisionCliente = ['no_me_gusta', 'talla_color', 'mejor_precio', 'no_necesito', 'otro_decision_cliente'];
    $motivosCourier = ['no_recibido', 'extravio_courier'];
    $motivosPermitidos = array_merge($motivosCulpaTienda, $motivosDecisionCliente, $motivosCourier, ['otro']);
    if (!in_array($motivo, $motivosPermitidos, true)) {
        throw new Exception('Motivo de devolución no válido. Selecciona una opción de la lista.');
    }

    $palabras_culpa_tienda = [
        'dañado', 'dañada', 'roto', 'rota', 'no funciona', 'no enciende', 'no sirve',
        'falta', 'faltan', 'incompleto', 'incorrecto', 'diferente', 'abierto', 'sello roto',
        'golpe', 'golpes', 'rayado', 'rayada', 'defectuoso', 'falla', 'no prende',
        'fractura', 'quebrado', 'mal embalado', 'mal empacado'
    ];
    $palabras_decision_cliente = [
        'arrepentí', 'arrepenti', 'no necesito', 'no lo uso', 'cambié', 'cambie',
        'mejor precio', 'más barato', 'mas barato', 'no me gustó', 'no me gusto',
        'no era lo que esperaba', 'grande', 'pequeño', 'color', 'talla', 'modelo', 'variante', 'ya tengo', 'duplicado'
    ];
    $palabras_courier = ['no llegó', 'no llego', 'no recibí', 'no recibi', 'perdido', 'extraviado', 'entregado pero no'];

    $motivo_final = $motivo;
    $tipo_caso = 'devolucion';
    $estado_inicial = 'pendiente_revision';
    $costo_envio = 0.00;
    $tipo_reembolso = 'pendiente_definir';
    $requiere_fotos = false;

    if ($motivo === 'otro') {
        $motivo_personalizado = trim($_POST['motivo_otro'] ?? '');
        if ($motivo_personalizado === '') {
            throw new Exception('Debes especificar el motivo de la devolución.');
        }

        $texto_lower = strtolower($motivo_personalizado . ' ' . $descripcion);
        $score_tienda = 0;
        $score_cliente = 0;
        $score_courier = 0;
        foreach ($palabras_culpa_tienda as $palabra) if (strpos($texto_lower, $palabra) !== false) $score_tienda++;
        foreach ($palabras_decision_cliente as $palabra) if (strpos($texto_lower, $palabra) !== false) $score_cliente++;
        foreach ($palabras_courier as $palabra) if (strpos($texto_lower, $palabra) !== false) $score_courier++;

        if ($score_courier >0 && $score_courier >= $score_tienda) {
            $motivo_final = 'no_recibido';
            $tipo_caso = 'incidencia_courier';
            $estado_inicial = 'investigacion_courier';
        } elseif ($score_tienda >$score_cliente) {
            $motivo_final = 'otro_culpa_tienda';
            $tipo_caso = 'devolucion_con_evidencia';
            $requiere_fotos = true;
        } elseif ($score_cliente >$score_tienda) {
            $motivo_final = 'otro_decision_cliente';
            $tipo_caso = 'devolucion_cliente';
            $costo_envio = 0.00;
            $tipo_reembolso = 'pendiente_definir';
        } else {
            $motivo_final = 'otro_sin_clasificar';
            $tipo_caso = 'incidencia_sin_clasificar';
            $requiere_fotos = true;
        }
        $descripcion = "[Motivo personalizado: {$motivo_personalizado}]\n\n" . $descripcion;
    } elseif (in_array($motivo, $motivosCourier, true)) {
        $tipo_caso = 'incidencia_courier';
        $estado_inicial = 'investigacion_courier';
    } elseif (in_array($motivo, $motivosCulpaTienda, true)) {
        $tipo_caso = ($motivo === 'danado_envio') ? 'incidencia_entrega' : 'devolucion_con_evidencia';
        $requiere_fotos = true;
    } elseif (in_array($motivo, $motivosDecisionCliente, true)) {
        $tipo_caso = 'devolucion_cliente';
        $costo_envio = 0.00;
        $tipo_reembolso = 'pendiente_definir';
    }

    if ($requiere_fotos && empty($_FILES['fotos']['name'][0])) {
        throw new Exception('Debes adjuntar al menos una foto como evidencia.');
    }

    $fotos_urls = emxSubirFotosDevolucion('fotos');

    $stmtFraude = $pdo->prepare("SELECT COUNT(*) FROM devoluciones WHERE usuario_id = ? AND created_at >NOW() - INTERVAL '30 days'");
    $stmtFraude->execute([$user_id]);
    $devoluciones_recientes = (int)$stmtFraude->fetchColumn();
    if ($devoluciones_recientes >= 3 && $estado_inicial === 'pendiente_revision') {
        $estado_inicial = 'pendiente_revision_fraude';
    }

    $historial = emxAgregarHistorial('[]', $estado_inicial, 'Caso creado por el cliente. Motivo: ' . emxTextoMotivoDevolucion($motivo_final), 'fa-undo');

    $stmtInsert = $pdo->prepare("INSERT INTO devoluciones
        (pedido_id, usuario_id, motivo, descripcion, fotos_evidencia, estado, tipo_reembolso, costo_envio_retorno, tipo_caso, historial_estados, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?::jsonb, ?, ?, ?, ?, ?::jsonb, NOW(), NOW())");
    $stmtInsert->execute([
        $pedido_id,
        $user_id,
        $motivo_final,
        $descripcion,
        json_encode($fotos_urls, JSON_UNESCAPED_UNICODE),
        $estado_inicial,
        $tipo_reembolso,
        $costo_envio,
        $tipo_caso,
        $historial
    ]);

    $pdo->prepare("UPDATE usuarios SET total_devoluciones = COALESCE(total_devoluciones, 0) + 1 WHERE id = ?")->execute([$user_id]);

    if (in_array($tipo_caso, ['incidencia_courier', 'incidencia_entrega'], true)) {
        $pedidoHistorial = emxAgregarHistorial(null, 'En Revisión', 'Se abrió caso de posventa: ' . emxTextoMotivoDevolucion($motivo_final), 'fa-exclamation-triangle');
        $stmtHist = $pdo->prepare("SELECT historial_estados FROM pedidos WHERE id = ?");
        $stmtHist->execute([$pedido_id]);
        $pedidoHistorial = emxAgregarHistorial($stmtHist->fetchColumn(), 'En Revisión', 'Se abrió caso de posventa: ' . emxTextoMotivoDevolucion($motivo_final), 'fa-exclamation-triangle');
        $pdo->prepare("UPDATE pedidos SET estado = 'En Revisión', historial_estados = ?::jsonb WHERE id = ?")->execute([$pedidoHistorial, $pedido_id]);
    }

    $pdo->commit();
    header('Location: mi_cuenta.php?seccion=devoluciones&msg=' . urlencode('Caso enviado correctamente. El admin verá las acciones secuenciales para resolverlo.') . '&msg_type=success');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: mi_cuenta.php?seccion=devoluciones&msg=' . urlencode($e->getMessage()) . '&msg_type=error');
    exit();
}
?>