<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';

// ==========================================
// banner_redirect.php
// Recibe el ID de un banner, lee su redirect_type
// y redirect_target, y arma la URL de destino
// correcta hacia index.php (o externa si es custom).
// ==========================================

$banner_id = $_GET['id'] ?? null;

if (!$banner_id) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT redirect_type, redirect_target FROM banners_promocionales WHERE id = ? AND is_active = TRUE");
$stmt->execute([$banner_id]);
$banner = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el banner no existe o está inactivo, al inicio
if (!$banner) {
    header('Location: index.php');
    exit();
}

$tipo = $banner['redirect_type'] ?? 'custom';
$target = trim($banner['redirect_target'] ?? '');

$url_destino = 'index.php';

switch ($tipo) {

    case 'category':
        //  CORRECCIÓN: $target puede ser "slug" o "slug|porcentaje"
        if ($target !== '') {
            // Si contiene un '|', nos quedamos solo con la parte del slug (antes del '|')
            if (strpos($target, '|') !== false) {
                $target = explode('|', $target)[0];
            }
            $url_destino = 'index.php?categoria=' . urlencode($target);
        }
        break;

    case 'discount':
        // $target = porcentaje (ej: "20")
        $porcentaje = (float) $target;
        if ($porcentaje >0) {
            $url_destino = 'index.php?descuento_min=' . urlencode($porcentaje);
        }
        break;

    case 'prime_exclusive':
        $url_destino = 'index.php?prime_only=1';
        break;

    case 'tag':
        // $target = nombre del tag/campaña
        if ($target !== '') {
            $url_destino = 'index.php?tag=' . urlencode($target);
        }
        break;

    case 'search':
        // $target = término de búsqueda
        if ($target !== '') {
            $url_destino = 'index.php?q=' . urlencode($target);
        }
        break;

    case 'custom':
    default:
        // $target = URL completa o ruta relativa puesta por el admin
        if ($target !== '') {
            $url_destino = emxSafeRedirect($target, 'index.php');
        }
        break;
}

header('Location: ' . emxSafeRedirect($url_destino, 'index.php'));
exit();