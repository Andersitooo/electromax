<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_google_auth.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

// Cargar configuración real de Google desde la estructura nueva.
// Antes apuntaba a EMX_ROOT/config_google.php, archivo que ya no existe en la estructura final,
// y eso podía dejar google_auth.php en blanco con error 500.
if (!function_exists('emxGoogleClientId')) {
    $googleConfigNuevo = defined('EMX_CONFIG_PATH') ? EMX_CONFIG_PATH . '/google.php' : EMX_ROOT . '/app/Config/google.php';
    $googleConfigLegacy = EMX_ROOT . '/config_google.php';

    if (is_file($googleConfigNuevo)) {
        require_once $googleConfigNuevo;
    } elseif (is_file($googleConfigLegacy)) {
        require_once $googleConfigLegacy;
    } else {
        throw new Exception('No se encontró la configuración de Google Login. Revisa app/Config/google.php.');
    }
}

function emxGoogleColumnExists($pdo, $tabla, $columna) {
    if (function_exists('emxDbColumnExists')) return emxDbColumnExists($pdo, $tabla, $columna);
    static $cache = [];
    $key = $tabla . '.' . $columna;
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $st = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=? AND column_name=? LIMIT 1");
        $st->execute([$tabla, $columna]);
        return $cache[$key] = (bool)$st->fetchColumn();
    } catch (Throwable $e) {
        return $cache[$key] = false;
    }
}

function emxGoogleTableExists($pdo, $tabla) {
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

function emxGoogleBase64UrlDecode($data) {
    $data = strtr((string)$data, '-_', '+/');
    $pad = strlen($data) % 4;
    if ($pad) $data .= str_repeat('=', 4 - $pad);
    return base64_decode($data, true);
}

function emxGoogleHttpJson($url, $cacheFile = null, $ttl = 3600) {
    if ($cacheFile && is_file($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
        $cached = json_decode((string)file_get_contents($cacheFile), true);
        if (is_array($cached)) return $cached;
    }

    $body = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Accept: application/json']
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => ['timeout' => 8, 'header' => "Accept: application/json\r\n"]]);
        $body = @file_get_contents($url, false, $ctx);
    }

    if ($body === false || trim((string)$body) === '') {
        throw new Exception('No se pudo contactar con Google para verificar el token.');
    }

    $json = json_decode($body, true);
    if (!is_array($json)) throw new Exception('Respuesta inválida de Google.');

    if ($cacheFile) {
        @file_put_contents($cacheFile, json_encode($json, JSON_UNESCAPED_SLASHES));
    }
    return $json;
}

function emxGoogleVerificarIdToken($credential) {
    $clientId = emxGoogleClientId();
    if (!emxGoogleActivo()) throw new Exception('Google Login no está configurado. Falta EMX_GOOGLE_CLIENT_ID.');

    $parts = explode('.', (string)$credential);
    if (count($parts) !== 3) throw new Exception('Token de Google inválido.');

    [$h64, $p64, $s64] = $parts;
    $header = json_decode(emxGoogleBase64UrlDecode($h64), true);
    $payload = json_decode(emxGoogleBase64UrlDecode($p64), true);
    $signature = emxGoogleBase64UrlDecode($s64);

    if (!is_array($header) || !is_array($payload) || $signature === false) {
        throw new Exception('Token de Google mal formado.');
    }
    if (($header['alg'] ?? '') !== 'RS256' || empty($header['kid'])) {
        throw new Exception('Token de Google con firma no aceptada.');
    }

    $certs = emxGoogleHttpJson(
        'https://www.googleapis.com/oauth2/v1/certs',
        sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'emx_google_certs.json',
        3600
    );
    $kid = $header['kid'];
    if (empty($certs[$kid])) {
        @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'emx_google_certs.json');
        $certs = emxGoogleHttpJson('https://www.googleapis.com/oauth2/v1/certs', null, 0);
    }
    if (empty($certs[$kid])) throw new Exception('No se encontró el certificado de Google para verificar el token.');

    $publicKey = openssl_pkey_get_public($certs[$kid]);
    if (!$publicKey) throw new Exception('No se pudo leer el certificado público de Google.');

    $ok = openssl_verify($h64 . '.' . $p64, $signature, $publicKey, OPENSSL_ALGO_SHA256);
    if ($ok !== 1) throw new Exception('La firma del token de Google no es válida.');

    $iss = $payload['iss'] ?? '';
    if (!in_array($iss, ['accounts.google.com', 'https://accounts.google.com'], true)) {
        throw new Exception('El emisor del token de Google no es válido.');
    }
    if (($payload['aud'] ?? '') !== $clientId) {
        throw new Exception('El token de Google no pertenece a esta aplicación.');
    }
    if (empty($payload['sub'])) throw new Exception('Google no devolvió identificador de usuario.');
    if (empty($payload['email']) || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Google no devolvió un correo válido.');
    }
    if (isset($payload['exp']) && (int)$payload['exp'] < time()) {
        throw new Exception('El token de Google expiró.');
    }
    if (!filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
        throw new Exception('Google no confirmó que el correo esté verificado.');
    }

    return $payload;
}

function emxGoogleAsegurarMigracion($pdo) {
    if (!emxGoogleColumnExists($pdo, 'usuarios', 'google_id')) {
        throw new Exception('Falta ejecutar migracion_google_login.sql antes de usar Google Login.');
    }
}

function emxGoogleRegistrarEventoAuth($pdo, $usuarioId, $tipo, $detalle = '') {
    try {
        if (!emxGoogleTableExists($pdo, 'usuarios_auth_eventos')) return;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);
        $st = $pdo->prepare("INSERT INTO usuarios_auth_eventos (usuario_id, tipo, detalle, ip, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $st->execute([$usuarioId, $tipo, $detalle, $ip, $ua]);
    } catch (Throwable $e) {
        error_log('[google-auth-event] ' . $e->getMessage());
    }
}

function emxGoogleBuscarUsuarioPorGoogleId($pdo, $googleId) {
    $st = $pdo->prepare("SELECT u.*, r.nombre AS rol_nombre FROM usuarios u LEFT JOIN roles r ON r.id = u.rol_id WHERE u.google_id = ? AND u.deleted_at IS NULL LIMIT 1");
    $st->execute([$googleId]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

function emxGoogleBuscarUsuarioPorEmail($pdo, $email) {
    $st = $pdo->prepare("SELECT u.*, r.nombre AS rol_nombre FROM usuarios u LEFT JOIN roles r ON r.id = u.rol_id WHERE LOWER(u.email) = LOWER(?) AND u.deleted_at IS NULL LIMIT 1");
    $st->execute([$email]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

function emxGoogleRolIdCliente($pdo) {
    $st = $pdo->prepare("SELECT id FROM roles WHERE nombre = 'CLIENTE' LIMIT 1");
    $st->execute();
    $id = $st->fetchColumn();
    if (!$id) throw new Exception('No existe el rol CLIENTE.');
    return $id;
}

function emxGoogleActualizarUsuario($pdo, $usuarioId, array $campos) {
    $sets = [];
    $params = [];
    foreach ($campos as $col => $val) {
        if (emxGoogleColumnExists($pdo, 'usuarios', $col)) {
            $sets[] = $col . ' = ?';
            $params[] = $val;
        }
    }
    if (!$sets) return;
    if (emxGoogleColumnExists($pdo, 'usuarios', 'updated_at')) $sets[] = 'updated_at = NOW()';
    $params[] = $usuarioId;
    $sql = 'UPDATE usuarios SET ' . implode(', ', $sets) . ' WHERE id = ?';
    $pdo->prepare($sql)->execute($params);
}

function emxGoogleIniciarSesion($pdo, array $user) {
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario_nombre'] = trim(($user['nombres'] ?? '') . ' ' . ($user['apellidos'] ?? ''));
    $_SESSION['usuario_email'] = $user['email'] ?? '';
    $_SESSION['usuario_rol'] = $user['rol_nombre'] ?? 'CLIENTE';
    $_SESSION['es_prime'] = (bool)($user['es_prime'] ?? false);
    $_SESSION['es_verificado'] = (bool)($user['tiene_badge_verificado'] ?? false);

    $campos = ['ultima_ip' => $_SERVER['REMOTE_ADDR'] ?? null, 'ultimo_login_google' => date('Y-m-d H:i:s')];
    emxGoogleActualizarUsuario($pdo, $user['id'], $campos);
}

function emxGoogleRedirectPorRol($rol) {
    $rol = strtoupper((string)$rol);
    if ($rol === 'SUPERADMIN' || $rol === 'ADMIN') return 'admin.php';
    if ($rol === 'PROVEEDOR') return 'proveedor.php';
    $next = $_SESSION['redirect_after_login'] ?? 'index.php?msg=bienvenido';
    unset($_SESSION['redirect_after_login']);
    return function_exists('emxSafeRedirect') ? emxSafeRedirect($next, 'index.php?msg=bienvenido') : 'index.php?msg=bienvenido';
}

function emxGoogleCrearCliente($pdo, array $payload) {
    $rolId = emxGoogleRolIdCliente($pdo);
    $email = strtolower(trim($payload['email']));
    $nombre = trim((string)($payload['given_name'] ?? ''));
    $apellido = trim((string)($payload['family_name'] ?? ''));
    if ($nombre === '') {
        $name = trim((string)($payload['name'] ?? 'Cliente'));
        $parts = preg_split('/\s+/', $name);
        $nombre = $parts[0] ?? 'Cliente';
        $apellido = trim(implode(' ', array_slice($parts, 1))) ?: 'Google';
    }
    if ($apellido === '') $apellido = 'Google';

    $cols = ['rol_id', 'nombres', 'apellidos', 'email', 'password_hash', 'telefono', 'cedula_ruc', 'tiene_badge_verificado', 'is_active'];
    $vals = ['?', '?', '?', '?', '?', '?', '?', 'FALSE', 'TRUE'];
    $params = [$rolId, $nombre, $apellido, $email, password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT), '', ''];

    $extra = [
        'google_id' => $payload['sub'],
        'google_email_verified' => true,
        'google_foto_url' => $payload['picture'] ?? null,
        'auth_provider' => 'google',
        'ultimo_login_google' => date('Y-m-d H:i:s'),
    ];
    foreach ($extra as $col => $val) {
        if (emxGoogleColumnExists($pdo, 'usuarios', $col)) {
            $cols[] = $col;
            $vals[] = '?';
            $params[] = $val;
        }
    }
    if (!empty($payload['picture']) && emxGoogleColumnExists($pdo, 'usuarios', 'foto_perfil_url')) {
        $cols[] = 'foto_perfil_url';
        $vals[] = '?';
        $params[] = $payload['picture'];
    }

    $sql = 'INSERT INTO usuarios (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ') RETURNING id';
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $id = $st->fetchColumn();
    return emxGoogleBuscarUsuarioPorEmail($pdo, $email);
}

function emxGoogleAutenticar($pdo, $credential) {
    emxGoogleAsegurarMigracion($pdo);
    $payload = emxGoogleVerificarIdToken($credential);
    $googleId = $payload['sub'];
    $email = strtolower(trim($payload['email']));

    $user = emxGoogleBuscarUsuarioPorGoogleId($pdo, $googleId);
    if ($user) {
        if (empty($user['is_active']) || $user['is_active'] === 'f') throw new Exception('La cuenta está inactiva.');
        emxGoogleActualizarUsuario($pdo, $user['id'], [
            'google_email_verified' => true,
            'google_foto_url' => $payload['picture'] ?? ($user['google_foto_url'] ?? null),
            'foto_perfil_url' => !empty($user['foto_perfil_url']) ? $user['foto_perfil_url'] : ($payload['picture'] ?? null),
            'ultimo_login_google' => date('Y-m-d H:i:s')
        ]);
        $user = emxGoogleBuscarUsuarioPorGoogleId($pdo, $googleId);
        emxGoogleIniciarSesion($pdo, $user);
        emxGoogleRegistrarEventoAuth($pdo, $user['id'], 'login_google', 'Ingreso con Google');
        return $user;
    }

    $user = emxGoogleBuscarUsuarioPorEmail($pdo, $email);
    if ($user) {
        if (empty($user['is_active']) || $user['is_active'] === 'f') throw new Exception('La cuenta está inactiva.');
        $rol = strtoupper($user['rol_nombre'] ?? 'CLIENTE');
        if (!empty($user['google_id']) && $user['google_id'] !== $googleId) {
            throw new Exception('Este correo ya está vinculado a otra cuenta de Google.');
        }
        if ($rol !== 'CLIENTE') {
            throw new Exception('Por seguridad, las cuentas administrativas o de proveedor no se vinculan automáticamente con Google. Entra con contraseña.');
        }
        emxGoogleActualizarUsuario($pdo, $user['id'], [
            'google_id' => $googleId,
            'google_email_verified' => true,
            'google_foto_url' => $payload['picture'] ?? null,
            'foto_perfil_url' => !empty($user['foto_perfil_url']) ? $user['foto_perfil_url'] : ($payload['picture'] ?? null),
            'auth_provider' => (($user['auth_provider'] ?? 'local') === 'local' ? 'local_google' : ($user['auth_provider'] ?? 'local_google')),
            'ultimo_login_google' => date('Y-m-d H:i:s')
        ]);
        $user = emxGoogleBuscarUsuarioPorGoogleId($pdo, $googleId);
        emxGoogleIniciarSesion($pdo, $user);
        emxGoogleRegistrarEventoAuth($pdo, $user['id'], 'google_vinculado_auto', 'Vinculado por correo verificado existente');
        return $user;
    }

    $user = emxGoogleCrearCliente($pdo, $payload);
    emxGoogleIniciarSesion($pdo, $user);
    emxGoogleRegistrarEventoAuth($pdo, $user['id'], 'registro_google', 'Cuenta creada con Google');
    return $user;
}

function emxGoogleVincularCuentaActual($pdo, $credential, $usuarioId) {
    emxGoogleAsegurarMigracion($pdo);
    $payload = emxGoogleVerificarIdToken($credential);
    $email = strtolower(trim($payload['email']));
    $googleId = $payload['sub'];

    $st = $pdo->prepare("SELECT u.*, r.nombre AS rol_nombre FROM usuarios u LEFT JOIN roles r ON r.id = u.rol_id WHERE u.id = ? AND u.deleted_at IS NULL LIMIT 1");
    $st->execute([$usuarioId]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    if (!$user) throw new Exception('Usuario no encontrado.');
    if (strtolower($user['email']) !== $email) {
        throw new Exception('La cuenta de Google debe usar el mismo correo registrado en ElectroMax.');
    }

    $other = emxGoogleBuscarUsuarioPorGoogleId($pdo, $googleId);
    if ($other && $other['id'] !== $usuarioId) {
        throw new Exception('Esta cuenta de Google ya está vinculada a otro usuario.');
    }

    emxGoogleActualizarUsuario($pdo, $usuarioId, [
        'google_id' => $googleId,
        'google_email_verified' => true,
        'google_foto_url' => $payload['picture'] ?? null,
        'foto_perfil_url' => !empty($user['foto_perfil_url']) ? $user['foto_perfil_url'] : ($payload['picture'] ?? null),
        'auth_provider' => (($user['auth_provider'] ?? 'local') === 'local' ? 'local_google' : ($user['auth_provider'] ?? 'local_google')),
        'ultimo_login_google' => date('Y-m-d H:i:s')
    ]);
    emxGoogleRegistrarEventoAuth($pdo, $usuarioId, 'google_vinculado_manual', 'Vinculado desde Mi Cuenta');
    return true;
}

function emxGoogleDesvincularCuentaActual($pdo, $usuarioId) {
    emxGoogleAsegurarMigracion($pdo);
    $st = $pdo->prepare("SELECT password_hash, auth_provider FROM usuarios WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    $st->execute([$usuarioId]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    if (!$user) throw new Exception('Usuario no encontrado.');
    if (empty($user['password_hash'])) {
        throw new Exception('Primero debes crear una contraseña antes de desvincular Google.');
    }
    emxGoogleActualizarUsuario($pdo, $usuarioId, [
        'google_id' => null,
        'google_email_verified' => false,
        'google_foto_url' => null,
        'auth_provider' => 'local'
    ]);
    emxGoogleRegistrarEventoAuth($pdo, $usuarioId, 'google_desvinculado', 'Desvinculado desde Mi Cuenta');
}
?>
