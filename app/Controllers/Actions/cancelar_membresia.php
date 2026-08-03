<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
emxVerificarCsrfSiPOST();

//  PROTECCIÓN: Solo usuarios logueados
if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth.php?action=login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['usuario_id'];
    
    try {
        // Revertir al plan básico (plan_id = NULL) y quitar beneficios
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET plan_id = NULL, 
                plan_activo = FALSE, 
                plan_expira_en = NULL, 
                es_prime = FALSE, 
                tiene_badge_verificado = FALSE
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        
        // Actualizar variables de sesión inmediatamente
        $_SESSION['es_prime'] = false;
        $_SESSION['es_verificado'] = false;
        
        header('Location: mi_cuenta.php?msg=plan_cancelado&msg_type=success');
        exit();
        
    } catch (Exception $e) {
        header('Location: mi_cuenta.php?msg=error_cancelacion&msg_type=error');
        exit();
    }
}

// Si intentan acceder por GET, redirigir
header('Location: mi_cuenta.php');
exit();
?>