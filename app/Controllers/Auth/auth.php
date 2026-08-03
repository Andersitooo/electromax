<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_CONFIG_PATH . '/google.php';

if (!function_exists('emxAuthDestinoSeguro')) {
function emxAuthDestinoSeguro($fallback = 'index.php?msg=bienvenido') {
    $destino = $_SESSION['redirect_after_login'] ?? '';
    unset($_SESSION['redirect_after_login']);

    $destino = trim(str_replace(["\r", "\n"], '', (string)$destino));
    if ($destino === '') {
        return $fallback;
    }

    // Evitar redirecciones externas.
    if (preg_match('/^https?:\/\//i', $destino)) {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $parts = parse_url($destino);
        if (!$host || empty($parts['host']) || strcasecmp($parts['host'], $host) !== 0) {
            return $fallback;
        }
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
        return ($path ?: 'index.php') . $query;
    }

    if (str_starts_with($destino, '//')) {
        return $fallback;
    }

    return $destino;
}
}


if (isset($_SESSION['usuario_id'])) {
    $rol = $_SESSION['usuario_rol'] ?? 'CLIENTE';
    if ($rol === 'SUPERADMIN' || $rol === 'ADMIN') {
        header('Location: admin.php');
    } elseif ($rol === 'PROVEEDOR') {
        header('Location: proveedor.php');
    } else {
        header('Location: ' . emxAuthDestinoSeguro('index.php'));
    }
    exit();
}

$action = $_GET['action'] ?? 'login';
$error = $_GET['msg'] ?? null;
$msg_type = $_GET['msg_type'] ?? 'error';
emxVerificarCsrfSiPOST();

// ============================================
// PROCESAR REGISTRO
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'registro') {
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $cedula = trim($_POST['cedula_ruc'] ?? '');

    if (empty($nombres) || empty($apellidos) || empty($email) || empty($password)) {
        $error = 'Los campos obligatorios deben estar llenos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        try {
            // Verificar si existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND deleted_at IS NULL");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Este correo ya está registrado.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Obtener rol CLIENTE
                $stmt_rol = $pdo->prepare("SELECT id FROM roles WHERE nombre = 'CLIENTE'");
                $stmt_rol->execute();
                $rol_id = $stmt_rol->fetchColumn();
                
                if (!$rol_id) {
                    throw new Exception("No existe el rol CLIENTE");
                }

                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (
                        rol_id, nombres, apellidos, email, password_hash, 
                        telefono, cedula_ruc, tiene_badge_verificado, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, FALSE, TRUE)
                    RETURNING id
                ");
                $stmt->execute([
                    $rol_id, $nombres, $apellidos, $email, 
                    $hash, $telefono, $cedula
                ]);
                $user_id = $stmt->fetchColumn();

                // Iniciar sesión
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $user_id;
                $_SESSION['usuario_nombre'] = $nombres . ' ' . $apellidos;
                $_SESSION['usuario_email'] = $email;
                $_SESSION['usuario_rol'] = 'CLIENTE';

                header('Location: ' . emxAuthDestinoSeguro('index.php?msg=bienvenido'));
                exit();
            }
        } catch (Exception $e) {
            $error = 'No se pudo completar la operación. Revisa los datos e intenta nuevamente.';
        }
    }
}

// ============================================
// PROCESAR LOGIN
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Ingresa tu correo y contraseña.';
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                WHERE u.email = ? AND u.deleted_at IS NULL AND u.is_active = TRUE
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = 'Usuario no encontrado o inactivo.';
            } elseif (!password_verify($password, $user['password_hash'])) {
                $error = 'Contraseña incorrecta.';
            } else {
                // Iniciar sesión
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombres'] . ' ' . $user['apellidos'];
                $_SESSION['usuario_email'] = $user['email'];
                $_SESSION['usuario_rol'] = $user['rol_nombre'] ?? 'CLIENTE';
                $_SESSION['es_prime'] = (bool)($user['es_prime'] ?? false);
                $_SESSION['es_verificado'] = (bool)($user['tiene_badge_verificado'] ?? false);

                // Actualizar IP
                $ip = $_SERVER['REMOTE_ADDR'];
                $pdo->prepare("UPDATE usuarios SET ultima_ip = ? WHERE id = ?")
                    ->execute([$ip, $user['id']]);

                // Redirección según rol
                $rol = $user['rol_nombre'] ?? 'CLIENTE';
                if ($rol === 'SUPERADMIN' || $rol === 'ADMIN') {
                    header('Location: admin.php');
                } elseif ($rol === 'PROVEEDOR') {
                    header('Location: proveedor.php');
                } else {
                    header('Location: ' . emxAuthDestinoSeguro('index.php?msg=bienvenido'));
                }
                exit();
            }
        } catch (PDOException $e) {
            $error = 'Error de base de datos: ' . $e->getMessage();
        }
    }
}

// ============================================
// Fase 5: carga de vista separada
// ============================================
// En esta fase la ruta antigua se conserva.
// Este archivo prepara datos, procesa formularios y luego carga la vista.
// La vista está separada en: views/auth/auth_view.php
require EMX_VIEWS_PATH . '/auth/auth_view.php';
exit;
