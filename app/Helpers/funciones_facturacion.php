<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_facturacion.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

/**
 * Facturación simulada ElectroMax
 * - No reemplaza una integración real con SRI.
 * - Genera factura simulada después de confirmación de pago por admin.
 * - Usa PHPMailer si Composer/vendor está instalado y SMTP configurado.
 * - Si no hay SMTP, guarda el correo en email_outbox para simulación académica.
 */

// Configuración local opcional para SMTP/empresa. Copia config_correo.example.php como config_correo.php.
if (is_file(EMX_CONFIG_PATH . '/mail.php')) { require_once EMX_CONFIG_PATH . '/mail.php'; }

if (!function_exists('emxFactPublicPath')) {
function emxFactPublicPath($relative) {
    $relative = ltrim(str_replace('\\', '/', (string)$relative), '/');
    $candidates = [
        EMX_PUBLIC_PATH . '/' . $relative,
        EMX_ROOT . '/' . $relative,
    ];
    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            return $candidate;
        }
    }
    return EMX_PUBLIC_PATH . '/' . $relative;
}
}


if (!function_exists('emxFactColumnExists')) {
function emxFactColumnExists($pdo, $tabla, $columna) {
    static $cache = [];
    $key = $tabla.'.'.$columna;
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $st = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=? AND column_name=? LIMIT 1");
        $st->execute([$tabla, $columna]);
        $cache[$key] = (bool)$st->fetchColumn();
    } catch (Throwable $e) { $cache[$key] = false; }
    return $cache[$key];
}
}

if (!function_exists('emxFactTableExists')) {
function emxFactTableExists($pdo, $tabla) {
    static $cache = [];
    if (array_key_exists($tabla, $cache)) return $cache[$tabla];
    try {
        $st = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name=? LIMIT 1");
        $st->execute([$tabla]);
        $cache[$tabla] = (bool)$st->fetchColumn();
    } catch (Throwable $e) { $cache[$tabla] = false; }
    return $cache[$tabla];
}
}

if (!function_exists('emxEmpresaConfig')) {
function emxEmpresaConfig($pdo) {
    try {
        $row = $pdo->query("SELECT * FROM empresa_config WHERE id = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row;
    } catch (Throwable $e) {}
    return [
        'razon_social' =>'ELECTROMAX S.A.S.',
        'nombre_comercial' =>'ElectroMax',
        'ruc' =>'0999999999001',
        'direccion_matriz' =>'Babahoyo, Los Ríos, Ecuador',
        'telefono' =>'04-273-0000',
        'email' =>'facturacion@electromax.com',
        'ambiente' =>'PRODUCCION',
        'logo_url' =>'assets/electromax_logo.png',
        'logo_pdf_url' =>'assets/electromax_logo_pdf.jpg',
        'establecimiento' =>'001',
        'punto_emision' =>'001',
        'moneda' =>'USD',
        'website' =>'',
        'regimen' =>'Documento generado electrónicamente por ElectroMax.',
        'obligado_contabilidad' =>false
    ];
}
}

if (!function_exists('emxFacturaNumero')) {
function emxFacturaNumero($pdo) {
    try { $n = (int)$pdo->query("SELECT nextval('emx_factura_seq')")->fetchColumn(); }
    catch (Throwable $e) { $n = time() % 100000000; }
    $empresa = emxEmpresaConfig($pdo);
    $establecimiento = preg_replace('/\D+/', '', (string)($empresa['establecimiento'] ?? '001')) ?: '001';
    $punto = preg_replace('/\D+/', '', (string)($empresa['punto_emision'] ?? '001')) ?: '001';
    $establecimiento = str_pad(substr($establecimiento, 0, 3), 3, '0', STR_PAD_LEFT);
    $punto = str_pad(substr($punto, 0, 3), 3, '0', STR_PAD_LEFT);
    return $establecimiento . '-' . $punto . '-' . str_pad((string)$n, 9, '0', STR_PAD_LEFT);
}
}

if (!function_exists('emxNotaCreditoNumero')) {
function emxNotaCreditoNumero($pdo) {
    try { $n = (int)$pdo->query("SELECT nextval('emx_nota_credito_seq')")->fetchColumn(); }
    catch (Throwable $e) { $n = time() % 100000000; }
    $empresa = emxEmpresaConfig($pdo);
    $establecimiento = preg_replace('/\D+/', '', (string)($empresa['establecimiento'] ?? '001')) ?: '001';
    $punto = preg_replace('/\D+/', '', (string)($empresa['punto_emision'] ?? '001')) ?: '001';
    $establecimiento = str_pad(substr($establecimiento, 0, 3), 3, '0', STR_PAD_LEFT);
    $punto = str_pad(substr($punto, 0, 3), 3, '0', STR_PAD_LEFT);
    return 'NC-' . $establecimiento . '-' . $punto . '-' . str_pad((string)$n, 9, '0', STR_PAD_LEFT);
}
}

if (!function_exists('emxClaveAccesoSimulada')) {
function emxClaveAccesoSimulada($numero, $pedido_id = '') {
    $base = date('dmY') . preg_replace('/\D/', '', $numero) . substr(md5($pedido_id . microtime(true)), 0, 24);
    return strtoupper(substr($base, 0, 49));
}
}

if (!function_exists('emxFacturaDatosClienteDesdePedido')) {
function emxFacturaDatosClienteDesdePedido($pedido) {
    $json = json_decode($pedido['facturacion_datos'] ?? '{}', true);
    if (is_array($json) && !empty($json['identificacion'])) return $json;
    return [
        'tipo_identificacion' =>'cedula',
        'identificacion' =>$pedido['cedula_ruc'] ?? '9999999999999',
        'razon_social' =>$pedido['nombre_cliente'] ?? 'Consumidor Final',
        'email' =>$pedido['email'] ?? '',
        'telefono' =>$pedido['telefono'] ?? '',
        'direccion' =>$pedido['direccion'] ?? '',
        'provincia' =>$pedido['provincia'] ?? '',
        'canton' =>$pedido['ciudad'] ?? ''
    ];
}
}

if (!function_exists('emxPdfEscape')) {
function emxPdfEscape($s) {
    $s = trim((string)$s);
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s);
    $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $s);
    if ($converted !== false) $s = $converted;
    return str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $s);
}
}

if (!function_exists('emxPdfText')) {
function emxPdfText($x, $y, $size, $text, $font = 'F1', $r = 0.08, $g = 0.11, $b = 0.17) {
    return sprintf("%.3f %.3f %.3f rg BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n", $r,$g,$b,$font,$size,$x,$y,emxPdfEscape($text));
}
}

if (!function_exists('emxPdfRect')) {
function emxPdfRect($x,$y,$w,$h,$r,$g,$b) { return sprintf("%.3f %.3f %.3f rg %.2f %.2f %.2f %.2f re f\n",$r,$g,$b,$x,$y,$w,$h); }
}

if (!function_exists('emxPdfLine')) {
function emxPdfLine($x1,$y1,$x2,$y2,$r=0.86,$g=0.89,$b=0.94,$w=0.5) {
    return sprintf("%.3f %.3f %.3f RG %.2f w %.2f %.2f m %.2f %.2f l S\n", $r,$g,$b,$w,$x1,$y1,$x2,$y2);
}
}

if (!function_exists('emxPdfRoundedRect')) {
function emxPdfRoundedRect($x,$y,$w,$h,$radius,$fr,$fg,$fb,$sr=0.74,$sg=0.82,$sb=0.94,$lw=0.7) {
    $r = min($radius, $w/2, $h/2);
    $k = 0.5522847498;
    $c = $r * $k;
    $x0=$x; $y0=$y; $x1=$x+$w; $y1=$y+$h;
    return sprintf(
        "q %.3f %.3f %.3f RG %.3f %.3f %.3f rg %.2f w ".
        "%.2f %.2f m %.2f %.2f l %.2f %.2f %.2f %.2f %.2f %.2f c ".
        "%.2f %.2f l %.2f %.2f %.2f %.2f %.2f %.2f c ".
        "%.2f %.2f l %.2f %.2f %.2f %.2f %.2f %.2f c ".
        "%.2f %.2f l %.2f %.2f %.2f %.2f %.2f %.2f c B Q\n",
        $sr,$sg,$sb,$fr,$fg,$fb,$lw,
        $x0+$r,$y0, $x1-$r,$y0,
        $x1-$r+$c,$y0, $x1,$y0+$r-$c, $x1,$y0+$r,
        $x1,$y1-$r,
        $x1,$y1-$r+$c, $x1-$r+$c,$y1, $x1-$r,$y1,
        $x0+$r,$y1,
        $x0+$r-$c,$y1, $x0,$y1-$r+$c, $x0,$y1-$r,
        $x0,$y0+$r,
        $x0,$y0+$r-$c, $x0+$r-$c,$y0, $x0+$r,$y0
    );
}
}

if (!function_exists('emxPdfCircle')) {
function emxPdfCircle($cx,$cy,$r,$fr,$fg,$fb) {
    $k = 0.5522847498;
    $c = $r * $k;
    return sprintf(
        "q %.3f %.3f %.3f rg ".
        "%.2f %.2f m ".
        "%.2f %.2f %.2f %.2f %.2f %.2f c ".
        "%.2f %.2f %.2f %.2f %.2f %.2f c ".
        "%.2f %.2f %.2f %.2f %.2f %.2f c ".
        "%.2f %.2f %.2f %.2f %.2f %.2f c f Q\n",
        $fr,$fg,$fb,
        $cx+$r,$cy,
        $cx+$r,$cy+$c, $cx+$c,$cy+$r, $cx,$cy+$r,
        $cx-$c,$cy+$r, $cx-$r,$cy+$c, $cx-$r,$cy,
        $cx-$r,$cy-$c, $cx-$c,$cy-$r, $cx,$cy-$r,
        $cx+$c,$cy-$r, $cx+$r,$cy-$c, $cx+$r,$cy
    );
}
}


if (!function_exists('emxPdfTextRight')) {
function emxPdfTextRight($x, $y, $size, $text, $font = 'F1', $r = 0.08, $g = 0.11, $b = 0.17) {
    $approx = strlen((string)$text) * $size * 0.48;
    return emxPdfText($x - $approx, $y, $size, $text, $font, $r, $g, $b);
}
}

if (!function_exists('emxPdfImageDo')) {
function emxPdfImageDo($name, $x, $y, $w, $h) {
    return sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /%s Do Q\n", $w, $h, $x, $y, $name);
}
}


if (!function_exists('emxPdfPrepararLogoJpeg')) {
function emxPdfPrepararLogoJpeg($empresa) {
    $candidatos = [];
    $logoPrincipal = trim((string)($empresa['logo_url'] ?? ''));
    $logoPdf = trim((string)($empresa['logo_pdf_url'] ?? ''));

    // Primero el logo principal, porque el usuario quiere el logo visible como en fichas técnicas.
    if ($logoPrincipal !== '') $candidatos[] = $logoPrincipal;
    if ($logoPdf !== '') $candidatos[] = $logoPdf;
    $candidatos[] = 'assets/electromax_logo.png';
    $candidatos[] = 'assets/electromax_logo_pdf.jpg';

    foreach ($candidatos as $rel) {
        $rel = ltrim(str_replace('\\', '/', $rel), '/');
        $abs = emxFactPublicPath($rel);
        if (!is_file($abs) || !function_exists('getimagesize')) continue;
        $info = @getimagesize($abs);
        if (!$info) continue;

        if (($info[2] ?? null) === IMAGETYPE_JPEG) {
            return [$abs, (int)$info[0], (int)$info[1]];
        }

        // El generador PDF básico solo incrusta JPEG; convertimos PNG a JPEG temporal
        // con fondo blanco para evitar cuadros oscuros o transparencias mal renderizadas.
        if (($info[2] ?? null) === IMAGETYPE_PNG && function_exists('imagecreatefrompng') && function_exists('imagejpeg')) {
            $im = @imagecreatefrompng($abs);
            if ($im) {
                $w = imagesx($im);
                $h = imagesy($im);
                $bg = imagecreatetruecolor($w, $h);
                $white = imagecolorallocate($bg, 255, 255, 255);
                imagefilledrectangle($bg, 0, 0, $w, $h, $white);
                imagealphablending($bg, true);
                imagecopy($bg, $im, 0, 0, 0, 0, $w, $h);
                $tmp = sys_get_temp_dir() . '/emx_logo_pdf_' . md5($abs . @filemtime($abs)) . '.jpg';
                @imagejpeg($bg, $tmp, 94);
                imagedestroy($im);
                imagedestroy($bg);
                if (is_file($tmp)) return [$tmp, $w, $h];
            }
        }
    }
    return [null, 0, 0];
}
}


if (!function_exists('emxCrearPdfBasico')) {
function emxCrearPdfBasico($path, $titulo, $subtitulo, $empresa, $cliente, $numero, $items, $totales, $tipo = 'FACTURA') {
    $clave = '';
    if (preg_match('/Clave(?:\s+de\s+acceso)?\s*:\s*([^|]+)/iu', (string)$subtitulo, $m)) $clave = trim($m[1]);
    $fecha = date('d/m/Y H:i');
    $moneda = $empresa['moneda'] ?? 'USD';

    $razon = $empresa['razon_social'] ?? 'ELECTROMAX S.A.S.';
    $ruc = $empresa['ruc'] ?? '';
    $dir = $empresa['direccion_matriz'] ?? '';
    $mail = $empresa['email'] ?? '';
    $tel = $empresa['telefono'] ?? '';
    $notaLegal = trim((string)($empresa['regimen'] ?? 'Documento generado electrónicamente por ElectroMax.'));
    if ($notaLegal === '') $notaLegal = 'Documento generado electrónicamente por ElectroMax.';

    $pedidoTag = '';
    if (preg_match('/Pedido\s*#?([A-Z0-9-]+)/i', (string)$subtitulo, $mPed)) $pedidoTag = strtoupper(substr($mPed[1],0,12));

    $headerAbs = emxFactPublicPath('assets/factura_header_electromax.jpg');
    $headerData = is_file($headerAbs) ? file_get_contents($headerAbs) : null;
    $headerOk = $headerData !== false && $headerData !== null;

    // Documento apaisado para replicar el diseño aprobado.
    $pageW = 842;
    $pageH = 595;

    $lines = [];
    $lines[] = emxPdfRect(0,0,$pageW,$pageH,0.965,0.980,1.000);

    // Header azul tecnológico con logo ya compuesto como imagen.
    if ($headerOk) {
        $lines[] = emxPdfImageDo('ImHeader', 0, 497, 842, 98);
    } else {
        $lines[] = emxPdfRect(0,497,842,98,0.02,0.07,0.16);
        $lines[] = emxPdfText(38,535,26,'ELECTROMAX','F2',1,1,1);
        $lines[] = emxPdfText(42,518,8,'TECNOLOGÍA QUE MEJORA TU VIDA','F2',0.80,0.88,1);
    }

    // Bloque empresa superior izquierdo
    $lines[] = emxPdfText(38,456,15,$razon,'F2',0.00,0.23,0.58);
    $lines[] = emxPdfText(38,430,10,'RUC: '.$ruc.'     |     '.$tel.'     |     '.$mail,'F1',0.06,0.11,0.22);
    $lines[] = emxPdfText(38,407,10,$dir,'F1',0.06,0.11,0.22);

    // Caja superior derecha - igual al modelo aprobado
    $lines[] = emxPdfRoundedRect(528,390,272,88,7,1,1,1,0.58,0.72,0.95,0.9);
    $lines[] = emxPdfRect(528,451,272,27,0.88,0.94,1.00);
    $lines[] = emxPdfText(568,460,13,'Factura No. '.$numero,'F2',0.02,0.15,0.42);
    $lines[] = emxPdfText(560,431,10,'Fecha de emisión:','F1',0.06,0.11,0.22);
    $lines[] = emxPdfTextRight(785,431,10,$fecha,'F1',0.06,0.11,0.22);
    $lines[] = emxPdfText(560,405,10,'Moneda:','F1',0.06,0.11,0.22);
    $lines[] = emxPdfTextRight(785,405,10,$moneda,'F1',0.06,0.11,0.22);
    $lines[] = emxPdfText(542,459,14,'#','F2',0.02,0.15,0.42);
    $lines[] = emxPdfText(542,430,10,'F','F2',0.02,0.15,0.42);
    $lines[] = emxPdfText(542,405,10,'$','F2',0.02,0.15,0.42);

    // Código / clave de acceso
    $lines[] = emxPdfRoundedRect(38,318,762,52,8,1,1,1,0.52,0.68,0.94,0.85);
    $lines[] = emxPdfCircle(70,344,18,0.00,0.29,0.73);
    $lines[] = emxPdfText(66,340,12,'K','F2',1,1,1);
    $lines[] = emxPdfText(108,348,9,'CÓDIGO / CLAVE DE ACCESO','F2',0.00,0.19,0.56);
    $lines[] = emxPdfText(108,329,12,$clave ?: emxClaveAccesoSimulada($numero),'F2',0.02,0.06,0.16);
    $lines[] = emxPdfTextRight(770,334,10,'Est. '.($empresa['establecimiento'] ?? '001').'   Punto emisión '.($empresa['punto_emision'] ?? '001'),'F1',0.00,0.21,0.57);

    // Tarjetas Emisor / Cliente
    $lines[] = emxPdfRoundedRect(38,214,374,82,7,1,1,1,0.78,0.84,0.93,0.7);
    $lines[] = emxPdfRoundedRect(428,214,372,82,7,1,1,1,0.78,0.84,0.93,0.7);

    $lines[] = emxPdfText(58,277,9,'EMISOR','F2',0.00,0.21,0.57);
    $lines[] = emxPdfText(58,257,12,mb_substr((string)$razon,0,38),'F2',0.02,0.06,0.16);
    $lines[] = emxPdfText(58,239,9,'RUC: '.$ruc,'F1',0.06,0.11,0.22);
    $lines[] = emxPdfText(58,222,9,mb_substr((string)$dir,0,45),'F1',0.06,0.11,0.22);
    $lines[] = emxPdfText(58,204,9,mb_substr($tel.'   |   '.$mail,0,51),'F1',0.06,0.11,0.22);
    $lines[] = emxPdfText(58,192,7,'Documento generado electrónicamente','F1',0.40,0.46,0.58);

    $direccionCliente = trim(($cliente['direccion'] ?? '') . ' ' . ($cliente['canton'] ?? '') . ' ' . ($cliente['provincia'] ?? ''));
    $lines[] = emxPdfText(448,277,9,'CLIENTE / DATOS DE FACTURACIÓN','F2',0.00,0.21,0.57);
    $lines[] = emxPdfText(448,257,12,mb_substr((string)($cliente['razon_social'] ?? 'Consumidor Final'),0,38),'F2',0.02,0.06,0.16);
    $lines[] = emxPdfText(448,239,9,'Identificación: '.($cliente['identificacion'] ?? 'N/D'),'F1',0.06,0.11,0.22);
    $lines[] = emxPdfText(448,222,9,mb_substr((string)($cliente['email'] ?? ''),0,42),'F1',0.06,0.11,0.22);
    $lines[] = emxPdfText(448,204,9,mb_substr($direccionCliente,0,42),'F1',0.06,0.11,0.22);
    $telefonoCli = trim((string)($cliente['telefono'] ?? ''));
    if ($telefonoCli !== '') $lines[] = emxPdfText(448,192,8,'Teléfono: '.mb_substr($telefonoCli,0,26),'F1',0.06,0.11,0.22);

    // Tabla de productos
    $lines[] = emxPdfRoundedRect(38,125,762,67,5,1,1,1,0.78,0.84,0.93,0.55);
    $lines[] = emxPdfRect(38,164,762,28,0.02,0.13,0.34);
    $lines[] = emxPdfText(56,174,9,'#','F2',1,1,1);
    $lines[] = emxPdfText(88,174,9,'PRODUCTO / SKU','F2',1,1,1);
    $lines[] = emxPdfTextRight(515,174,9,'CANT.','F2',1,1,1);
    $lines[] = emxPdfTextRight(610,174,9,'P. UNIT.','F2',1,1,1);
    $lines[] = emxPdfTextRight(700,174,9,'IVA','F2',1,1,1);
    $lines[] = emxPdfTextRight(785,174,9,'TOTAL','F2',1,1,1);

    $rowY = 137;
    $idx = 1;
    foreach ($items as $it) {
        if ($idx >5) break;
        $desc = mb_substr((string)($it['descripcion'] ?? ''),0,58);
        $sku = trim((string)($it['sku'] ?? ''));
        $lines[] = emxPdfText(56,$rowY+10,10,(string)$idx,'F2',0.02,0.06,0.16);
        $lines[] = emxPdfText(88,$rowY+10,10,$desc,'F1',0.02,0.06,0.16);
        if ($sku !== '') $lines[] = emxPdfText(88,$rowY-5,7,'SKU: '.$sku,'F1',0.40,0.46,0.58);
        $lines[] = emxPdfTextRight(515,$rowY+7,9,(string)($it['cantidad'] ?? 0),'F1',0.02,0.06,0.16);
        $lines[] = emxPdfTextRight(610,$rowY+7,9,'$'.number_format((float)($it['precio_unitario'] ?? 0),2),'F1',0.02,0.06,0.16);
        $lines[] = emxPdfTextRight(700,$rowY+7,9,'$'.number_format((float)($it['iva'] ?? 0),2),'F1',0.02,0.06,0.16);
        $lines[] = emxPdfTextRight(785,$rowY+7,9,'$'.number_format((float)($it['total'] ?? 0),2),'F2',0.02,0.06,0.16);
        $rowY -= 35;
        $idx++;
    }

    // Bloque de agradecimiento
    $lines[] = emxPdfLine(38,43,38,89,0.00,0.27,0.72,1.5);
    $lines[] = emxPdfCircle(70,66,21,0.88,0.93,1.00);
    $lines[] = emxPdfText(62,60,14,'OK','F2',0.00,0.21,0.57);
    $lines[] = emxPdfText(110,70,10,'Gracias por su confianza.','F2',0.00,0.21,0.57);
    $lines[] = emxPdfText(110,50,10,'Tecnología que mejora tu vida.','F1',0.06,0.11,0.22);

    // Totales
    $lines[] = emxPdfRoundedRect(465,35,335,114,7,0.965,0.985,1.000,0.70,0.80,0.94,0.75);
    $lines[] = emxPdfText(485,121,10,'Subtotal','F1',0.05,0.12,0.32);
    $lines[] = emxPdfTextRight(780,121,10,'$'.number_format((float)($totales['subtotal'] ?? 0),2),'F2',0.02,0.06,0.16);
    $lines[] = emxPdfLine(485,105,780,105,0.83,0.88,0.95,0.45);
    $lines[] = emxPdfText(485,94,10,'Descuento','F1',0.05,0.12,0.32);
    $lines[] = emxPdfTextRight(780,94,10,'$'.number_format((float)($totales['descuento'] ?? 0),2),'F2',0.02,0.06,0.16);
    $lines[] = emxPdfLine(485,78,780,78,0.83,0.88,0.95,0.45);
    $lines[] = emxPdfText(485,67,10,'IVA (15%)','F1',0.05,0.12,0.32);
    $lines[] = emxPdfTextRight(780,67,10,'$'.number_format((float)($totales['iva'] ?? 0),2),'F2',0.02,0.06,0.16);
    $lines[] = emxPdfRect(465,35,335,36,0.02,0.13,0.34);
    $lines[] = emxPdfText(485,48,15,'TOTAL','F2',1,1,1);
    $lines[] = emxPdfTextRight(780,48,20,'$'.number_format((float)($totales['total'] ?? 0),2),'F2',1,1,1);

    $lines[] = emxPdfText(38,18,7,mb_substr($notaLegal,0,110),'F1',0.45,0.50,0.60);

    $content = implode('', $lines);
    $objects = [];
    $resources = "/Font << /F1 4 0 R /F2 5 0 R >>";
    if ($headerOk) $resources .= " /XObject << /ImHeader 7 0 R >>";

    $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >>endobj\n";
    $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n";
    $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << $resources >>/Contents 6 0 R >>endobj\n";
    $objects[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>endobj\n";
    $objects[] = "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>endobj\n";
    $objects[] = "6 0 obj << /Length ".strlen($content)." >>stream\n$content\nendstream endobj\n";
    if ($headerOk) {
        $objects[] = "7 0 obj << /Type /XObject /Subtype /Image /Width 842 /Height 98 /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($headerData)." >>stream\n".$headerData."\nendstream endobj\n";
    }

    $pdf = "%PDF-1.4\n"; $offsets = [0];
    foreach ($objects as $obj) { $offsets[] = strlen($pdf); $pdf .= $obj; }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 ".(count($objects)+1)."\n0000000000 65535 f \n";
    for ($i=1;$i<=count($objects);$i++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    $pdf .= "trailer << /Size ".(count($objects)+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";

    if (!is_dir(dirname($path))) @mkdir(dirname($path), 0775, true);
    file_put_contents($path, $pdf);
    return $path;
}
}


if (!function_exists('emxEnviarCorreoDocumento')) {
function emxEnviarCorreoDocumento($pdo, $usuario_id, $email, $asunto, $html, $archivo = null, $tipo = 'factura') {
    $autoload = EMX_ROOT . '/vendor/autoload.php';
    $smtpHost = trim(getenv('EMX_SMTP_HOST') ?: '');
    $smtpUser = trim(getenv('EMX_SMTP_USER') ?: '');
    $smtpPass = trim(getenv('EMX_SMTP_PASS') ?: '');
    $fromEmail = trim(getenv('EMX_SMTP_FROM_EMAIL') ?: (getenv('EMX_SMTP_USER') ?: 'no-reply@electromax.local'));
    $fromName = trim(getenv('EMX_SMTP_FROM_NAME') ?: 'ElectroMax');

    $placeholderValues = [
        '',
        'smtp.tudominio.com',
        'facturacion@tudominio.com',
        'TU_PASSWORD_O_APP_PASSWORD',
        'CAMBIA_ESTA_PASSWORD_SMTP',
    ];
    $smtpReal = is_file($autoload)
        && !in_array($smtpHost, $placeholderValues, true)
        && !in_array($smtpUser, $placeholderValues, true)
        && !in_array($smtpPass, $placeholderValues, true)
        && filter_var($fromEmail, FILTER_VALIDATE_EMAIL);

    if ($smtpReal) {
        require_once $autoload;
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $secure = strtolower(getenv('EMX_SMTP_SECURE') ?: 'tls');
            $mail->SMTPSecure = $secure === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)(getenv('EMX_SMTP_PORT') ?: ($secure === 'ssl' ? 465 : 587));
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($fromEmail, $fromName);

            $modoPrueba = (getenv('EMX_MAIL_MODO_PRUEBA') ?: '0') === '1';
            $correoPrueba = trim(getenv('EMX_MAIL_CORREO_PRUEBA') ?: '');
            if ($modoPrueba && filter_var($correoPrueba, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($correoPrueba);
                $html .= '<hr><p style="font-size:12px;color:#64748b">Modo prueba: destinatario original ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>';
            } else {
                $mail->addAddress($email);
            }

            $bccEmpresa = trim(getenv('EMX_SMTP_BCC_EMPRESA') ?: '');
            if (filter_var($bccEmpresa, FILTER_VALIDATE_EMAIL)) $mail->addBCC($bccEmpresa);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $html;
            $mail->AltBody = strip_tags(str_replace(['<br>','<br/>','<br />'], "\n", $html));
            if ($archivo && is_file($archivo)) $mail->addAttachment($archivo);
            $mail->send();
            // Registrar también los correos enviados reales para poder verlos en el panel administrativo.
            try {
                if (emxFactTableExists($pdo, 'email_outbox')) {
                    $estadoRegistro = 'enviado';
                    $stLog = $pdo->prepare("INSERT INTO email_outbox (usuario_id, email_destino, asunto, cuerpo_html, archivo_adjunto, tipo, estado, error_msg, enviado_at) VALUES (?, ?, ?, ?, ?, ?, ?, NULL, NOW())");
                    $stLog->execute([$usuario_id, $email, $asunto, $html, $archivo, $tipo, $estadoRegistro]);
                }
            } catch (Throwable $eLog) {}
            return ['ok'=>true, 'estado'=>'enviado'];
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = 'SMTP/PHPMailer no configurado o todavía tiene datos de ejemplo. Correo guardado en outbox.';
    }

    try {
        if (emxFactTableExists($pdo, 'email_outbox')) {
            $st = $pdo->prepare("INSERT INTO email_outbox (usuario_id, email_destino, asunto, cuerpo_html, archivo_adjunto, tipo, estado, error_msg) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?)");
            $st->execute([$usuario_id, $email, $asunto, $html, $archivo, $tipo, $error ?? null]);
        }
    } catch (Throwable $e) {}
    return ['ok'=>false, 'estado'=>'pendiente', 'error'=>$error ?? 'No enviado'];
}
}

if (!function_exists('emxGenerarFacturaPedido')) {
function emxGenerarFacturaPedido($pdo, $pedido_id, $enviarCorreo = true) {
    if (!emxFactTableExists($pdo, 'facturas')) return null;
    $st = $pdo->prepare("SELECT id FROM facturas WHERE pedido_id = ? LIMIT 1");
    $st->execute([$pedido_id]);
    $existe = $st->fetchColumn();
    if ($existe) return $existe;

    $st = $pdo->prepare("SELECT p.*, u.cedula_ruc FROM pedidos p LEFT JOIN usuarios u ON u.id = p.usuario_id WHERE p.id = ?");
    $st->execute([$pedido_id]);
    $pedido = $st->fetch(PDO::FETCH_ASSOC);
    if (!$pedido) throw new Exception('Pedido no encontrado para facturar.');

    $st = $pdo->prepare("SELECT dp.*, pr.sku FROM detalle_pedidos dp LEFT JOIN productos pr ON pr.id = dp.producto_id WHERE dp.pedido_id = ?");
    $st->execute([$pedido_id]);
    $detalles = $st->fetchAll(PDO::FETCH_ASSOC);
    if (!$detalles) throw new Exception('El pedido no tiene detalle para facturar.');

    $empresa = emxEmpresaConfig($pdo);
    $cliente = emxFacturaDatosClienteDesdePedido($pedido);
    $numero = emxFacturaNumero($pdo);
    $clave = emxClaveAccesoSimulada($numero, $pedido_id);

    $subtotal = 0; $iva = 0; $total = 0; $itemsPdf = [];
    foreach ($detalles as $d) {
        $cantidad = (int)$d['cantidad'];
        $precio = (float)$d['precio_unitario'];
        $ivaPct = (float)($d['iva_porcentaje'] ?? 15);
        $lineSub = round($precio * $cantidad, 2);
        $lineIva = round($lineSub * ($ivaPct / 100), 2);
        $lineTotal = isset($d['total']) ? (float)$d['total'] : ($lineSub + $lineIva);
        $subtotal += $lineSub; $iva += $lineIva; $total += $lineTotal;
        $itemsPdf[] = [
            'producto_id'=>$d['producto_id'], 'sku'=>$d['sku'] ?? '', 'descripcion'=>$d['nombre_producto'], 'cantidad'=>$cantidad,
            'precio_unitario'=>$precio, 'descuento'=>0, 'iva_porcentaje'=>$ivaPct, 'subtotal'=>$lineSub, 'iva'=>$lineIva, 'total'=>$lineTotal
        ];
    }

    $st = $pdo->prepare("INSERT INTO facturas (pedido_id, usuario_id, numero_factura, clave_acceso_simulada, estado, ambiente, subtotal, descuento, iva, total, datos_empresa, datos_cliente) VALUES (?, ?, ?, ?, 'emitida', ?, ?, 0, ?, ?, ?::jsonb, ?::jsonb) RETURNING id");
    $st->execute([$pedido_id, $pedido['usuario_id'], $numero, $clave, $empresa['ambiente'] ?? 'SIMULACION', $subtotal, $iva, $total, json_encode($empresa, JSON_UNESCAPED_UNICODE), json_encode($cliente, JSON_UNESCAPED_UNICODE)]);
    $facturaId = $st->fetchColumn();

    $ins = $pdo->prepare("INSERT INTO factura_detalles (factura_id, producto_id, sku, descripcion, cantidad, precio_unitario, descuento, iva_porcentaje, subtotal, iva, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($itemsPdf as $it) $ins->execute([$facturaId, $it['producto_id'], $it['sku'], $it['descripcion'], $it['cantidad'], $it['precio_unitario'], 0, $it['iva_porcentaje'], $it['subtotal'], $it['iva'], $it['total']]);

    $pdfRel = 'documentos/facturacion/facturas/' . date('Y/m') . '/factura_' . preg_replace('/[^0-9A-Za-z_-]/','_', $numero) . '.pdf';
    $pdfAbs = EMX_ROOT . '/' . $pdfRel;
    emxCrearPdfBasico($pdfAbs, 'Factura de compra', 'Pedido #' . strtoupper(substr($pedido_id,0,8)) . ' | Clave de acceso: ' . $clave, $empresa, $cliente, $numero, $itemsPdf, ['subtotal'=>$subtotal,'descuento'=>0,'iva'=>$iva,'total'=>$total], 'FACTURA');
    $pdo->prepare("UPDATE facturas SET pdf_url = ? WHERE id = ?")->execute([$pdfRel, $facturaId]);

    if ($enviarCorreo && !empty($cliente['email'])) {
        $html = '<div style="font-family:Arial,Helvetica,sans-serif;background:#eef3f9;padding:28px;color:#0f172a"><div style="max-width:680px;margin:auto;background:#ffffff;border-radius:22px;overflow:hidden;border:1px solid #dbe6f3;box-shadow:0 12px 32px rgba(15,23,42,.10)"><div style="background:linear-gradient(135deg,#06162f,#0b4da2);padding:26px 30px;color:white"><h1 style="margin:0;font-size:28px;letter-spacing:.3px">ElectroMax</h1><p style="margin:6px 0 0;color:#dbeafe;font-size:14px">Tecnología que mejora tu vida</p></div><div style="padding:28px 30px"><h2 style="margin:0 0 10px;color:#062b63;font-size:22px">Factura No. '.htmlspecialchars($numero).'</h2><p style="font-size:15px;line-height:1.6;margin:0 0 18px">Hola, adjuntamos la factura de tu pedido <strong>#'.htmlspecialchars(strtoupper(substr($pedido_id,0,8))).'</strong>.</p><div style="background:#f5f9ff;border:1px solid #dbeafe;border-radius:16px;padding:18px;margin:18px 0"><table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px"><tr><td style="color:#64748b;padding:6px 0">Número de factura</td><td align="right" style="font-weight:700;color:#0f172a">'.htmlspecialchars($numero).'</td></tr><tr><td style="color:#64748b;padding:6px 0">Total</td><td align="right" style="font-weight:800;color:#0057b8;font-size:18px">$'.number_format($total,2).'</td></tr></table></div><p style="font-size:14px;color:#475569;line-height:1.6">El PDF se encuentra adjunto a este correo. También puedes revisar tu pedido desde tu cuenta.</p><p style="margin-top:22px;font-size:14px;color:#0f172a"><strong>Gracias por confiar en ElectroMax.</strong></p></div><div style="padding:16px 30px;background:#f8fafc;border-top:1px solid #e2e8f0;color:#64748b;font-size:12px">Correo generado automáticamente por ElectroMax.</div></div></div>';
        $res = emxEnviarCorreoDocumento($pdo, $pedido['usuario_id'], $cliente['email'], 'Factura ElectroMax ' . $numero, $html, $pdfAbs, 'factura');
        if (!empty($res['ok'])) $pdo->prepare("UPDATE facturas SET enviada_email = TRUE, email_enviado_at = NOW() WHERE id = ?")->execute([$facturaId]);
    }
    return $facturaId;
}
}


if (!function_exists('emxEnviarCorreoNotaCredito')) {
function emxEnviarCorreoNotaCredito($pdo, $nota_credito_id) {
    try {
        if (!emxFactTableExists($pdo, 'notas_credito')) {
            return ['ok'=>false, 'estado'=>'sin_tabla', 'error'=>'No existe tabla notas_credito'];
        }

        $st = $pdo->prepare("
            SELECT nc.*, f.numero_factura, f.usuario_id AS factura_usuario_id, p.usuario_id AS pedido_usuario_id, p.email AS pedido_email
            FROM notas_credito nc
            LEFT JOIN facturas f ON f.id = nc.factura_id
            LEFT JOIN pedidos p ON p.id = nc.pedido_id
            WHERE nc.id = ?
            LIMIT 1
        ");
        $st->execute([$nota_credito_id]);
        $nc = $st->fetch(PDO::FETCH_ASSOC);
        if (!$nc) return ['ok'=>false, 'estado'=>'no_encontrada', 'error'=>'Nota de crédito no encontrada'];

        if (emxFactColumnExists($pdo, 'notas_credito', 'enviada_email')
            && !empty($nc['enviada_email'])
            && !empty($nc['email_enviado_at'] ?? null)) {
            return ['ok'=>true, 'estado'=>'ya_enviada'];
        }

        $cliente = json_decode($nc['datos_cliente'] ?? '{}', true);
        if (!is_array($cliente)) $cliente = [];

        $email = trim((string)($cliente['email'] ?? ''));
        $usuarioId = $nc['pedido_usuario_id'] ?: ($nc['factura_usuario_id'] ?? null);

        if (!$email && !empty($nc['pedido_id'])) {
            try {
                $stEmail = $pdo->prepare("
                    SELECT COALESCE(NULLIF(p.email,''), u.email) AS email, COALESCE(p.usuario_id, u.id) AS usuario_id
                    FROM pedidos p
                    LEFT JOIN usuarios u ON u.id = p.usuario_id
                    WHERE p.id = ?
                    LIMIT 1
                ");
                $stEmail->execute([$nc['pedido_id']]);
                $rowEmail = $stEmail->fetch(PDO::FETCH_ASSOC);
                if ($rowEmail) {
                    $email = trim((string)($rowEmail['email'] ?? ''));
                    if (!$usuarioId) $usuarioId = $rowEmail['usuario_id'] ?? null;
                }
            } catch (Throwable $e) {}
        }

        if (!$email && $usuarioId) {
            try {
                $stEmail = $pdo->prepare("SELECT email FROM usuarios WHERE id = ? LIMIT 1");
                $stEmail->execute([$usuarioId]);
                $email = trim((string)$stEmail->fetchColumn());
            } catch (Throwable $e) {}
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok'=>false, 'estado'=>'sin_email', 'error'=>'El cliente no tiene correo válido para enviar nota de crédito'];
        }

        $numero = (string)($nc['numero_nota'] ?? '');
        $facturaRelacionada = (string)($nc['numero_factura'] ?? '');
        $motivo = (string)($nc['motivo'] ?? 'Reembolso/cancelación de pedido');
        $total = (float)($nc['total'] ?? 0);
        $pedidoCodigo = !empty($nc['pedido_id']) ? strtoupper(substr((string)$nc['pedido_id'], 0, 8)) : 'N/D';
        $pdfRel = trim((string)($nc['pdf_url'] ?? ''));
        $pdfAbs = $pdfRel !== '' ? EMX_ROOT . '/' . ltrim(str_replace('\\', '/', $pdfRel), '/') : null;

        $html = '<div style="font-family:Arial,Helvetica,sans-serif;background:#eef3f9;padding:28px;color:#0f172a">' .
            '<div style="max-width:680px;margin:auto;background:#ffffff;border-radius:22px;overflow:hidden;border:1px solid #dbe6f3;box-shadow:0 12px 32px rgba(15,23,42,.10)">' .
            '<div style="background:linear-gradient(135deg,#06162f,#0b4da2);padding:26px 30px;color:white">' .
            '<h1 style="margin:0;font-size:28px;letter-spacing:.3px">ElectroMax</h1>' .
            '<p style="margin:6px 0 0;color:#dbeafe;font-size:14px">Tecnología que mejora tu vida</p>' .
            '</div>' .
            '<div style="padding:28px 30px">' .
            '<h2 style="margin:0 0 10px;color:#062b63;font-size:22px">Nota de crédito No. '.htmlspecialchars($numero, ENT_QUOTES, 'UTF-8').'</h2>' .
            '<p style="font-size:15px;line-height:1.6;margin:0 0 18px">Hola, adjuntamos la nota de crédito generada por el reembolso de tu pedido <strong>#'.htmlspecialchars($pedidoCodigo, ENT_QUOTES, 'UTF-8').'</strong>.</p>' .
            '<div style="background:#f5f9ff;border:1px solid #dbeafe;border-radius:16px;padding:18px;margin:18px 0">' .
            '<table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px">' .
            '<tr><td style="color:#64748b;padding:6px 0">Nota de crédito</td><td align="right" style="font-weight:700;color:#0f172a">'.htmlspecialchars($numero, ENT_QUOTES, 'UTF-8').'</td></tr>' .
            '<tr><td style="color:#64748b;padding:6px 0">Factura relacionada</td><td align="right" style="font-weight:700;color:#0f172a">'.htmlspecialchars($facturaRelacionada ?: 'N/D', ENT_QUOTES, 'UTF-8').'</td></tr>' .
            '<tr><td style="color:#64748b;padding:6px 0">Motivo</td><td align="right" style="font-weight:700;color:#0f172a">'.htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8').'</td></tr>' .
            '<tr><td style="color:#64748b;padding:6px 0">Valor acreditado</td><td align="right" style="font-weight:800;color:#b45309;font-size:18px">$'.number_format($total,2).'</td></tr>' .
            '</table>' .
            '</div>' .
            '<p style="font-size:14px;color:#475569;line-height:1.6">El PDF de la nota de crédito se encuentra adjunto a este correo. También puedes revisar el estado de tu devolución desde tu cuenta.</p>' .
            '<p style="margin-top:22px;font-size:14px;color:#0f172a"><strong>Gracias por confiar en ElectroMax.</strong></p>' .
            '</div>' .
            '<div style="padding:16px 30px;background:#f8fafc;border-top:1px solid #e2e8f0;color:#64748b;font-size:12px">Correo generado automáticamente por ElectroMax.</div>' .
            '</div></div>';

        $res = emxEnviarCorreoDocumento(
            $pdo,
            $usuarioId,
            $email,
            'Nota de crédito ElectroMax ' . $numero,
            $html,
            ($pdfAbs && is_file($pdfAbs)) ? $pdfAbs : null,
            'nota_credito'
        );

        if (!empty($res['ok']) && emxFactColumnExists($pdo, 'notas_credito', 'enviada_email')) {
            $sets = ["enviada_email = TRUE"];
            if (emxFactColumnExists($pdo, 'notas_credito', 'email_enviado_at')) $sets[] = "email_enviado_at = NOW()";
            $pdo->prepare("UPDATE notas_credito SET " . implode(', ', $sets) . " WHERE id = ?")->execute([$nota_credito_id]);
        }

        return $res;
    } catch (Throwable $e) {
        error_log('[nota_credito_email] ' . $e->getMessage());
        return ['ok'=>false, 'estado'=>'error', 'error'=>$e->getMessage()];
    }
}
}

if (!function_exists('emxGenerarNotaCreditoTotal')) {
function emxGenerarNotaCreditoTotal($pdo, $pedido_id, $devolucion_id = null, $motivo = 'Reembolso/cancelación de pedido') {
    if (!emxFactTableExists($pdo, 'notas_credito')) return null;
    $st = $pdo->prepare("SELECT * FROM facturas WHERE pedido_id = ? AND estado = 'emitida' LIMIT 1");
    $st->execute([$pedido_id]);
    $fact = $st->fetch(PDO::FETCH_ASSOC);
    if (!$fact) return null;

    if ($devolucion_id) {
        $stEx = $pdo->prepare("SELECT id FROM notas_credito WHERE devolucion_id = ? LIMIT 1");
        $stEx->execute([$devolucion_id]);
        if ($id = $stEx->fetchColumn()) {
            if (function_exists('emxEnviarCorreoNotaCredito')) emxEnviarCorreoNotaCredito($pdo, $id);
            return $id;
        }
    }

    $numero = emxNotaCreditoNumero($pdo);
    $empresa = json_decode($fact['datos_empresa'] ?: '{}', true) ?: emxEmpresaConfig($pdo);
    $cliente = json_decode($fact['datos_cliente'] ?: '{}', true) ?: [];
    $st = $pdo->prepare("INSERT INTO notas_credito (factura_id, pedido_id, devolucion_id, numero_nota, motivo, tipo, subtotal, iva, total, datos_empresa, datos_cliente) VALUES (?, ?, ?, ?, ?, 'total', ?, ?, ?, ?::jsonb, ?::jsonb) RETURNING id");
    $st->execute([$fact['id'], $pedido_id, $devolucion_id, $numero, $motivo, $fact['subtotal'], $fact['iva'], $fact['total'], json_encode($empresa, JSON_UNESCAPED_UNICODE), json_encode($cliente, JSON_UNESCAPED_UNICODE)]);
    $ncId = $st->fetchColumn();

    $items = [[ 'descripcion'=>'Nota de crédito total sobre factura '.$fact['numero_factura'], 'cantidad'=>1, 'precio_unitario'=>$fact['subtotal'], 'iva'=>$fact['iva'], 'total'=>$fact['total'] ]];
    $pdfRel = 'documentos/facturacion/notas_credito/' . date('Y/m') . '/nota_credito_' . preg_replace('/[^0-9A-Za-z_-]/','_', $numero) . '.pdf';
    $pdfAbs = EMX_ROOT . '/' . $pdfRel;
    emxCrearPdfBasico($pdfAbs, 'Nota de crédito', 'Factura relacionada: '.$fact['numero_factura'].' | Motivo: '.$motivo, $empresa, $cliente, $numero, $items, ['subtotal'=>$fact['subtotal'], 'descuento'=>0, 'iva'=>$fact['iva'], 'total'=>$fact['total']], 'NOTA DE CRÉDITO');
    $pdo->prepare("UPDATE notas_credito SET pdf_url = ? WHERE id = ?")->execute([$pdfRel, $ncId]);
    $pdo->prepare("UPDATE facturas SET estado = 'nota_credito_total' WHERE id = ?")->execute([$fact['id']]);
    if ($devolucion_id && emxFactColumnExists($pdo, 'devoluciones', 'nota_credito_id')) $pdo->prepare("UPDATE devoluciones SET nota_credito_id = ? WHERE id = ?")->execute([$ncId, $devolucion_id]);

    // Enviar automáticamente la nota de crédito al cliente.
    // Si SMTP no está configurado, queda registrada como pendiente en email_outbox.
    if (function_exists('emxEnviarCorreoNotaCredito')) emxEnviarCorreoNotaCredito($pdo, $ncId);

    return $ncId;
}
}

if (!function_exists('emxLiberarInventarioPedidoCancelado')) {
function emxLiberarInventarioPedidoCancelado($pdo, $pedido_id) {
    // Se usa solo para pedidos pendientes de aprobación. Devuelve stock reservado/descontado y cancela backorders.
    $st = $pdo->prepare("SELECT id, producto_id, cantidad, sucursal_origen_id, numero_serie_vendido FROM detalle_pedidos WHERE pedido_id = ?");
    $st->execute([$pedido_id]);
    $items = $st->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $it) {
        $sucursal = $it['sucursal_origen_id'] ?? null;
        $producto = $it['producto_id'] ?? null;
        if (!$sucursal || !$producto) continue;
        $series = json_decode($it['numero_serie_vendido'] ?? '[]', true);
        if (!is_array($series)) $series = [];
        $reservado = 0; $fisico = 0;
        foreach ($series as $serie) {
            $serie = (string)$serie;
            if (preg_match('/RESERVADO_ENTREGA_TOTAL_(\d+)_UNIDADES/', $serie, $m)) $reservado += (int)$m[1];
            elseif (str_starts_with($serie, 'PENDIENTE_BACKORDER_')) { /* no stock físico */ }
            elseif (trim($serie) !== '') $fisico++;
        }
        if ($reservado >0 && emxFactColumnExists($pdo, 'inventario_sucursal', 'stock_reservado')) {
            $up = $pdo->prepare("UPDATE inventario_sucursal SET stock_reservado = GREATEST(COALESCE(stock_reservado,0) - ?, 0) WHERE sucursal_id = ? AND producto_id = ?");
            $up->execute([$reservado, $sucursal, $producto]);
            try { $pdo->prepare("UPDATE productos SET stock_actual_global = COALESCE(stock_actual_global,0) + ? WHERE id = ?")->execute([$reservado, $producto]); } catch (Throwable $e) {}
        }
        if ($fisico >0) {
            $up = $pdo->prepare("UPDATE inventario_sucursal SET stock = COALESCE(stock,0) + ? WHERE sucursal_id = ? AND producto_id = ?");
            $up->execute([$fisico, $sucursal, $producto]);
            try { $pdo->prepare("UPDATE productos SET stock_actual_global = COALESCE(stock_actual_global,0) + ? WHERE id = ?")->execute([$fisico, $producto]); } catch (Throwable $e) {}
        }
    }

    try {
        $stBo = $pdo->prepare("SELECT id FROM pedidos_backorder WHERE pedido_original_id = ?");
        $stBo->execute([$pedido_id]);
        $bos = $stBo->fetchAll(PDO::FETCH_COLUMN);
        if ($bos) {
            $in = implode(',', array_fill(0, count($bos), '?'));
            $pdo->prepare("UPDATE cronogramas_reabastecimiento SET estado = 'cancelado' WHERE backorder_id IN ($in)")->execute($bos);
            $pdo->prepare("UPDATE pedidos_backorder SET estado = 'cancelado', updated_at = NOW() WHERE id IN ($in)")->execute($bos);
            if (emxFactColumnExists($pdo, 'solicitudes_reabastecimiento', 'backorder_id')) {
                $pdo->prepare("UPDATE solicitudes_reabastecimiento SET estado = 'cancelada' WHERE backorder_id IN ($in) AND estado IN ('pendiente','cotizada','en_revision')")->execute($bos);
            }
        }
    } catch (Throwable $e) {}
    return true;
}
}

?>