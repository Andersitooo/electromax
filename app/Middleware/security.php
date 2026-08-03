<?php
/**
 * Seguridad base centralizada.
 *
 * Responsabilidad:
 * - Iniciar sesión con parámetros seguros.
 * - Proteger formularios con CSRF.
 * - Validar roles y permisos.
 * - Ayudar con redirecciones internas y subida segura de archivos.
 *
 * Este archivo reemplaza la implementación antigua de `seguridad.php`.
 * La ruta antigua sigue existiendo como adaptador.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

/**
 * Seguridad base ElectroMax.
 * - Configuración segura de sesión.
 * - Roles y permisos por ruta.
 * - CSRF para formularios POST.
 * - Validación simple de UUID y redirecciones internas.
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' =>0,
            'path' =>'/',
            'domain' =>'',
            'secure' =>$secure,
            'httponly' =>true,
            'samesite' =>'Lax'
        ]);
    }
    session_start();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function emxUsuarioId() {
    return $_SESSION['usuario_id'] ?? null;
}

function emxRolActual() {
    return strtoupper($_SESSION['usuario_rol'] ?? 'INVITADO');
}

function emxEsAdmin() {
    return in_array(emxRolActual(), ['SUPERADMIN', 'ADMIN'], true);
}

function emxEsProveedor() {
    return emxRolActual() === 'PROVEEDOR';
}

function emxRequireLogin($redirect = true) {
    if (!isset($_SESSION['usuario_id'])) {
        if ($redirect) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
            header('Location: auth.php?action=login&msg=debes_iniciar_sesion');
            exit;
        }
        http_response_code(401);
        echo 'Debes iniciar sesión.';
        exit;
    }
}

function emxRequireRole(array $roles) {
    emxRequireLogin();
    $roles = array_map('strtoupper', $roles);
    if (!in_array(emxRolActual(), $roles, true)) {
        http_response_code(403);
        echo '<div style="font-family:Arial;padding:32px"><h2>Acceso denegado</h2><p>No tienes permiso para abrir esta ruta.</p><a href="index.php">Volver al inicio</a></div>';
        exit;
    }
}

function emxIsUuid($value) {
    return is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
}

function emxCsrfToken() {
    return $_SESSION['csrf_token'] ?? '';
}


function emxDbColumnExists($pdo, $tabla, $columna) {
    static $cache = [];
    $key = $tabla . '.' . $columna;
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = 'public' AND table_name = ? AND column_name = ? LIMIT 1");
        $stmt->execute([$tabla, $columna]);
        $cache[$key] = (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        $cache[$key] = false;
    }
    return $cache[$key];
}

function emxCsrfCampo() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(emxCsrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function emxVerificarCsrf() {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Sesión expirada o formulario inválido. Recarga la página e intenta nuevamente.');
    }
}

function emxVerificarCsrfSiPOST() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        emxVerificarCsrf();
    }
}

function emxSafeRedirect($url, $fallback = 'index.php') {
    $url = trim((string)$url);
    if ($url === '') return $fallback;
    if (preg_match('/^https?:\/\//i', $url) || str_starts_with($url, '//')) return $fallback;
    if (str_contains($url, "\n") || str_contains($url, "\r")) return $fallback;
    return $url;
}

function emxRegistrarEvento($mensaje, array $contexto = []) {
    $linea = '[' . date('Y-m-d H:i:s') . '] ' . $mensaje;
    if ($contexto) $linea .= ' ' . json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    error_log($linea . PHP_EOL, 3, EMX_ROOT . '/debug_error.log');
}


function emxSlugCarpeta($texto, $fallback = 'item') {
    $texto = trim((string)$texto);
    if ($texto === '') return $fallback;
    if (function_exists('iconv')) {
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if ($convertido !== false) $texto = $convertido;
    }
    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    $texto = trim($texto, '-');
    return $texto !== '' ? $texto : $fallback;
}

function emxSegmentoCarpeta($texto, $fallback = 'item') {
    $slug = emxSlugCarpeta($texto, $fallback);
    return preg_replace('/[^a-z0-9\-]/', '', $slug) ?: $fallback;
}

function emxRutaUploads(array $segmentos) {
    $limpios = [];
    foreach ($segmentos as $segmento) {
        $segmento = trim((string)$segmento);
        if ($segmento === '') continue;
        $segmento = str_replace(['..', '\\'], ['', '/'], $segmento);
        $segmento = trim($segmento, '/');
        if ($segmento !== '') $limpios[] = $segmento;
    }
    return implode('/', $limpios);
}

function emxAplicarProteccionUploads($destino) {
    $normalizado = trim(str_replace('\\', '/', (string)$destino), '/');
    if ($normalizado === '' || !str_starts_with($normalizado, 'uploads')) return;
    if (!is_dir('uploads')) return;

    $htaccess = 'uploads/.htaccess';
    if (!file_exists($htaccess)) {
        @file_put_contents($htaccess, "Options -Indexes\n<FilesMatch \\\"\\.(php|phtml|phar|cgi|pl|py|sh)$\\\">\n    Require all denied\n</FilesMatch>\n");
    }
    $index = rtrim($destino, '/') . '/index.html';
    if (!file_exists($index)) @file_put_contents($index, '');
}

function emxCarpetaCategoriaProducto($pdo, $categoria_id) {
    if (empty($categoria_id)) return '00 - sin-categoria';
    static $cache = [];
    if (isset($cache[$categoria_id])) return $cache[$categoria_id];
    try {
        $stmt = $pdo->query("SELECT id, nombre, COALESCE(NULLIF(slug,''), nombre) AS slug FROM categorias ORDER BY nombre ASC");
        $i = 1;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $cat) {
            $folder = sprintf('%02d - %s', $i, emxSegmentoCarpeta($cat['slug'] ?: $cat['nombre'], 'categoria'));
            $cache[$cat['id']] = $folder;
            $i++;
        }
    } catch (Throwable $e) {
        emxRegistrarEvento('No se pudo construir carpeta de categoría', ['error' =>$e->getMessage()]);
    }
    return $cache[$categoria_id] ?? '00 - sin-categoria';
}

function emxCarpetaProductoUploads($pdo, $producto_id) {
    try {
        $stmt = $pdo->prepare("SELECT p.id, p.nombre, p.sku, p.categoria_id, COALESCE(NULLIF(c.slug,''), c.nombre) AS categoria_slug FROM productos p LEFT JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ? LIMIT 1");
        $stmt->execute([$producto_id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($p) {
            $categoria = emxCarpetaCategoriaProducto($pdo, $p['categoria_id'] ?? null);
            $baseNombre = trim(($p['sku'] ?? '') . ' ' . ($p['nombre'] ?? ''));
            $producto = emxSegmentoCarpeta($baseNombre ?: ('producto-' . substr((string)$producto_id, 0, 8)), 'producto');
            return emxRutaUploads(['uploads', 'productos', $categoria, $producto, 'imagenes']);
        }
    } catch (Throwable $e) {
        emxRegistrarEvento('No se pudo construir carpeta de producto', ['producto_id' =>$producto_id, 'error' =>$e->getMessage()]);
    }
    return emxRutaUploads(['uploads', 'productos', '00 - sin-categoria', 'producto-' . substr((string)$producto_id, 0, 8), 'imagenes']);
}

function emxCarpetaMarcaUploads($nombreMarca) {
    return emxRutaUploads(['uploads', 'marcas', emxSegmentoCarpeta($nombreMarca, 'marca')]);
}

function emxCarpetaBannerUploads($pdo, $section_id = null) {
    $section = 'sin-seccion';
    if (!empty($section_id)) {
        try {
            $stmt = $pdo->prepare("SELECT nombre, tipo FROM page_sections WHERE id = ? LIMIT 1");
            $stmt->execute([$section_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) $section = ($row['tipo'] ?? 'section') . '-' . ($row['nombre'] ?? 'seccion');
        } catch (Throwable $e) {}
    }
    return emxRutaUploads(['uploads', 'banners', emxSegmentoCarpeta($section, 'seccion'), date('Y')]);
}

function emxCarpetaPerfilUploads($tipo, $usuario_id) {
    $tipo = emxSegmentoCarpeta($tipo, 'usuarios');
    $usuario = 'user-' . preg_replace('/[^a-z0-9]/i', '', substr((string)$usuario_id, 0, 8));
    return emxRutaUploads(['uploads', 'perfiles', $tipo, $usuario]);
}

function emxCarpetaConfirmacionPedidoUploads($pedido_id) {
    $pedido = 'pedido-' . preg_replace('/[^a-z0-9]/i', '', substr((string)$pedido_id, 0, 8));
    return emxRutaUploads(['uploads', 'confirmaciones', $pedido]);
}

function emxCarpetaDevolucionUploads($pedido_id) {
    $pedido = 'pedido-' . preg_replace('/[^a-z0-9]/i', '', substr((string)$pedido_id, 0, 8));
    return emxRutaUploads(['uploads', 'devoluciones', $pedido, date('Y-m')]);
}

function emxErrorSubidaTexto($codigo) {
    $mapa = [
        UPLOAD_ERR_INI_SIZE =>'La imagen supera el límite configurado en PHP.',
        UPLOAD_ERR_FORM_SIZE =>'La imagen supera el límite del formulario.',
        UPLOAD_ERR_PARTIAL =>'La imagen se subió incompleta. Intenta nuevamente.',
        UPLOAD_ERR_NO_FILE =>'No se seleccionó ningún archivo.',
        UPLOAD_ERR_NO_TMP_DIR =>'Falta la carpeta temporal de PHP.',
        UPLOAD_ERR_CANT_WRITE =>'PHP no pudo escribir el archivo en disco.',
        UPLOAD_ERR_EXTENSION =>'Una extensión de PHP bloqueó la subida.'
    ];
    return $mapa[$codigo] ?? 'Error desconocido al subir la imagen.';
}

function emxPrepararDirectorioSubida($destino) {
    $destino = trim((string)$destino);
    if ($destino === '') {
        throw new Exception('Directorio de subida inválido.');
    }
    if (!is_dir($destino)) {
        if (!mkdir($destino, 0755, true) && !is_dir($destino)) {
            throw new Exception('No se pudo crear el directorio de subida: ' . $destino);
        }
    }
    if (!is_writable($destino)) {
        throw new Exception('El directorio de subida no tiene permisos de escritura: ' . $destino);
    }
    $destino = rtrim(str_replace('\\', '/', $destino), '/');
    emxAplicarProteccionUploads($destino);
    return $destino;
}

function emxDetectarExtensionImagenSegura($tmp, $nombreOriginal, array $permitidos) {
    if (!is_uploaded_file($tmp)) {
        throw new Exception('Archivo temporal inválido.');
    }

    $mime = null;
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
    } elseif (function_exists('mime_content_type')) {
        $mime = mime_content_type($tmp);
    }

    if ($mime && isset($permitidos[$mime])) {
        return $permitidos[$mime];
    }

    $info = @getimagesize($tmp);
    if ($info !== false && !empty($info['mime']) && isset($permitidos[$info['mime']])) {
        return $permitidos[$info['mime']];
    }

    // Último respaldo para instalaciones XAMPP donde finfo puede venir deshabilitado.
    // No se acepta solo por extensión: antes debe pasar getimagesize().
    if ($info !== false) {
        $ext = strtolower(pathinfo((string)$nombreOriginal, PATHINFO_EXTENSION));
        $extPermitidas = array_unique(array_values($permitidos));
        if (in_array($ext, $extPermitidas, true)) {
            return $ext === 'jpeg' ? 'jpg' : $ext;
        }
    }

    throw new Exception('Una imagen no tiene un formato válido. Usa JPG, PNG, WEBP o GIF.');
}

function emxNombreArchivoSeguro($prefijo, $extension, $indice = null) {
    $prefijo = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$prefijo);
    if ($prefijo === '') $prefijo = 'file';
    $sufijo = bin2hex(random_bytes(8));
    if ($indice !== null) $sufijo .= '_' . (int)$indice;
    return $prefijo . '_' . date('YmdHis') . '_' . $sufijo . '.' . $extension;
}

function emxSubirArchivoSeguro($campo, $destino = 'uploads', array $opciones = []) {
    if (empty($_FILES[$campo]) || ($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$campo];
    $error = $file['error'] ?? UPLOAD_ERR_OK;
    if ($error !== UPLOAD_ERR_OK) {
        throw new Exception(emxErrorSubidaTexto($error));
    }

    $maxBytes = $opciones['max_bytes'] ?? (12 * 1024 * 1024);
    if (($file['size'] ?? 0) <= 0) {
        throw new Exception('El archivo está vacío.');
    }
    if (($file['size'] ?? 0) >$maxBytes) {
        throw new Exception('La imagen supera el tamaño permitido de ' . round($maxBytes / 1024 / 1024) . 'MB.');
    }

    $permitidos = $opciones['mime'] ?? [
        'image/jpeg' =>'jpg',
        'image/png' =>'png',
        'image/webp' =>'webp',
        'image/gif' =>'gif'
    ];
    $tmp = $file['tmp_name'];
    $ext = emxDetectarExtensionImagenSegura($tmp, $file['name'] ?? '', $permitidos);
    $destino = emxPrepararDirectorioSubida($destino);
    $ruta = $destino . '/' . emxNombreArchivoSeguro($opciones['prefijo'] ?? 'file', $ext);

    if (!move_uploaded_file($tmp, $ruta)) {
        throw new Exception('No se pudo guardar la imagen. Revisa permisos de la carpeta uploads.');
    }
    return $ruta;
}

function emxSubirArchivosMultiplesSeguro($campo, $destino = 'uploads', array $opciones = []) {
    $rutas = [];
    if (empty($_FILES[$campo]) || empty($_FILES[$campo]['name']) || !is_array($_FILES[$campo]['name'])) {
        return $rutas;
    }

    $destino = emxPrepararDirectorioSubida($destino);
    $maxBytes = $opciones['max_bytes'] ?? (12 * 1024 * 1024);
    $permitidos = $opciones['mime'] ?? [
        'image/jpeg' =>'jpg',
        'image/png' =>'png',
        'image/webp' =>'webp',
        'image/gif' =>'gif'
    ];
    $prefijo = $opciones['prefijo'] ?? 'file';
    $errores = [];

    foreach ($_FILES[$campo]['name'] as $i =>$name) {
        if (($name ?? '') === '') continue;
        $error = $_FILES[$campo]['error'][$i] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) continue;
        if ($error !== UPLOAD_ERR_OK) {
            $errores[] = $name . ': ' . emxErrorSubidaTexto($error);
            continue;
        }
        $size = $_FILES[$campo]['size'][$i] ?? 0;
        if ($size <= 0) {
            $errores[] = $name . ': archivo vacío.';
            continue;
        }
        if ($size >$maxBytes) {
            $errores[] = $name . ': supera ' . round($maxBytes / 1024 / 1024) . 'MB.';
            continue;
        }
        $tmp = $_FILES[$campo]['tmp_name'][$i] ?? '';
        try {
            $ext = emxDetectarExtensionImagenSegura($tmp, $name, $permitidos);
            $ruta = $destino . '/' . emxNombreArchivoSeguro($prefijo, $ext, $i);
            if (!move_uploaded_file($tmp, $ruta)) {
                $errores[] = $name . ': no se pudo guardar. Revisa permisos de uploads.';
                continue;
            }
            $rutas[] = $ruta;
        } catch (Exception $e) {
            $errores[] = $name . ': ' . $e->getMessage();
        }
    }

    if (!empty($errores)) {
        // Si al menos una imagen se subió, devolvemos la lista y registramos los errores.
        // Si ninguna se subió, detenemos la operación para que el admin sepa qué pasó.
        emxRegistrarEvento('Errores al subir imágenes múltiples', ['campo' =>$campo, 'errores' =>$errores]);
        if (empty($rutas)) {
            throw new Exception('No se pudo subir ninguna imagen: ' . implode(' | ', $errores));
        }
    }

    return $rutas;
}

?>