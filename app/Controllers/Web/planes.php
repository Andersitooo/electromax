<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
emxVerificarCsrfSiPOST();

$action = $_GET['action'] ?? 'ver';
$msg = $_GET['msg'] ?? null;
$msg_type = $_GET['msg_type'] ?? 'success';
$error_msg = null;

// ============================================
// OBTENER PLANES CON CONTEO REAL DE SUSCRIPTORES
// ============================================
$stmt = $pdo->query("
    SELECT p.*, COUNT(u.id) as total_suscriptores 
    FROM planes p 
    LEFT JOIN usuarios u ON p.id = u.plan_id AND u.plan_activo = TRUE 
    WHERE p.is_active = TRUE 
    GROUP BY p.id 
    ORDER BY p.orden ASC
");
$planes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determinar cuál es el plan más comprado dinámicamente
$plan_mas_popular_id = null;
$max_suscriptores = -1;

foreach ($planes as $plan) {
    if ($plan['total_suscriptores'] >$max_suscriptores) {
        $max_suscriptores = $plan['total_suscriptores'];
        $plan_mas_popular_id = $plan['id'];
    }
}

// Fallback: Si ningún plan tiene suscriptores aún, marcar el que tenga slug 'pro' o 'premium'
if ($max_suscriptores === 0) {
    foreach ($planes as $plan) {
        if (in_array($plan['slug'], ['pro', 'premium', 'plus'])) {
            $plan_mas_popular_id = $plan['id'];
            break;
        }
    }
}

// ============================================
// PROCESAR SUSCRIPCIÓN
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suscribir'])) {
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: auth.php?action=login&msg=debes_iniciar_sesion');
        exit();
    }

    $plan_id = $_POST['plan_id'] ?? null;
    $user_id = $_SESSION['usuario_id'];

    $card_number = str_replace(' ', '', $_POST['card_number'] ?? '');
    $card_name = trim($_POST['card_name'] ?? '');
    $card_expiry = trim($_POST['card_expiry'] ?? '');
    $card_cvv = trim($_POST['card_cvv'] ?? '');

    if (empty($card_number) || empty($card_name) || empty($card_expiry) || empty($card_cvv)) {
        $error_msg = 'Completa todos los datos de la tarjeta.';
    } elseif (!validarLuhn($card_number)) {
        $error_msg = 'Número de tarjeta inválido.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM planes WHERE id = ? AND is_active = TRUE");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$plan) throw new Exception('Plan no válido.');

            // Simular procesamiento de pago
            sleep(1.5);

            // LÓGICA DE PRUEBA: 7 días si se marca la casilla, sino 30 días
            $es_prueba = isset($_POST['es_prueba']) && $_POST['es_prueba'] === '1';
            $dias = $es_prueba ? 7 : 30;
            $expira_en = date('Y-m-d H:i:s', strtotime("+{$dias} days"));

            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET plan_id = ?, 
                    plan_activo = TRUE, 
                    plan_expira_en = ?, 
                    es_prime = ?, 
                    tiene_badge_verificado = ?,
                    es_prueba = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $plan_id, 
                $expira_en, 
                $plan['es_prime'], 
                $plan['es_prime'], 
                $es_prueba, // <-- Guardamos el estado de prueba
                $user_id
            ]);

            $_SESSION['es_prime'] = (bool)$plan['es_prime'];
            $_SESSION['es_verificado'] = (bool)$plan['es_prime'];

            $success_msg = $es_prueba 
                ? '¡Prueba gratuita activada! Disfruta de 7 días gratis. Se te cobrará automáticamente al finalizar.' 
                : '¡Suscripción exitosa! Ya disfrutas de los beneficios de tu plan.';
            
            header('Location: planes.php?msg=' . urlencode($success_msg) . '&msg_type=success');
            exit();

        } catch (Exception $e) {
            $error_msg = $e->getMessage();
        }
    }
}

function validarLuhn($numero) {
    $suma = 0; 
    $longitud = strlen($numero); 
    $paridad = $longitud % 2;
    for ($i = 0; $i < $longitud; $i++) {
        $digito = (int)$numero[$i];
        if ($i % 2 == $paridad) { 
            $digito *= 2; 
            if ($digito >9) $digito -= 9; 
        }
        $suma += $digito;
    }
    return ($suma % 10 == 0);
}

// Verificar plan actual del usuario
$plan_actual_usuario = null;
if (isset($_SESSION['usuario_id'])) {
    $stmt = $pdo->prepare("SELECT u.*, p.nombre as plan_nombre, p.precio_mensual FROM usuarios u LEFT JOIN planes p ON u.plan_id = p.id WHERE u.id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $plan_actual_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/frontend/planes_view.php
require EMX_VIEWS_PATH . '/frontend/planes_view.php';
exit;
