<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_HELPERS_PATH . '/funciones_google_auth.php';

$action = $_GET['action'] ?? 'login';

try {
    if ($action === 'login') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método no permitido.');
        emxVerificarCsrf();
        $credential = $_POST['credential'] ?? '';
        if (!$credential) throw new Exception('Google no devolvió credencial.');
        $user = emxGoogleAutenticar($pdo, $credential);
        header('Location: ' . emxGoogleRedirectPorRol($user['rol_nombre'] ?? 'CLIENTE'));
        exit;
    }

    if ($action === 'link') {
        emxRequireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método no permitido.');
        emxVerificarCsrf();
        $credential = $_POST['credential'] ?? '';
        if (!$credential) throw new Exception('Google no devolvió credencial.');
        emxGoogleVincularCuentaActual($pdo, $credential, $_SESSION['usuario_id']);
        header('Location: mi_cuenta.php?seccion=seguridad&msg=Cuenta+de+Google+vinculada&msg_type=success');
        exit;
    }

    if ($action === 'unlink') {
        emxRequireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método no permitido.');
        emxVerificarCsrf();
        emxGoogleDesvincularCuentaActual($pdo, $_SESSION['usuario_id']);
        header('Location: mi_cuenta.php?seccion=seguridad&msg=Cuenta+de+Google+desvinculada&msg_type=success');
        exit;
    }

    throw new Exception('Acción no válida.');
} catch (Throwable $e) {
    error_log('[google_auth] ' . $e->getMessage());
    $msg = urlencode($e->getMessage());
    if ($action === 'link' || $action === 'unlink') {
        header('Location: mi_cuenta.php?seccion=seguridad&msg=' . $msg . '&msg_type=error');
    } else {
        header('Location: auth.php?action=login&msg=' . $msg . '&msg_type=error');
    }
    exit;
}
?>
