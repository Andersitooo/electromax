<?php
/**
 * Regenera PDFs de facturas que no existen físicamente y actualiza pdf_url.
 * Uso en VPS:
 *   php scripts/reparar_facturas_pdf_faltantes.php
 *   php scripts/reparar_facturas_pdf_faltantes.php --factura=UUID
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo consola.');
}

require_once dirname(__DIR__) . '/bootstrap/app.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_HELPERS_PATH . '/funciones_facturacion.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "[ERROR] No hay conexión PDO. Revisa .env de base de datos.\n");
    exit(1);
}

$facturaFiltro = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--factura=')) {
        $facturaFiltro = trim(substr($arg, 10));
    }
}

$sql = "SELECT f.* FROM facturas f";
$params = [];
if ($facturaFiltro) {
    $sql .= " WHERE f.id = ?";
    $params[] = $facturaFiltro;
}
$sql .= " ORDER BY f.created_at DESC";

$st = $pdo->prepare($sql);
$st->execute($params);
$facturas = $st->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
$regeneradas = 0;
$existentes = 0;
$errores = 0;

foreach ($facturas as $fact) {
    $total++;
    $pdfActual = emxFactResolveAttachmentPath($fact['pdf_url'] ?? '');
    if ($pdfActual) {
        $existentes++;
        echo "[OK] Ya existe PDF: {$fact['numero_factura']} -> $pdfActual\n";
        continue;
    }

    $empresa = json_decode($fact['datos_empresa'] ?? '{}', true);
    if (!is_array($empresa) || !$empresa) $empresa = emxEmpresaConfig($pdo);

    $cliente = json_decode($fact['datos_cliente'] ?? '{}', true);
    if (!is_array($cliente)) $cliente = [];

    $itSt = $pdo->prepare("SELECT producto_id, sku, descripcion, cantidad, precio_unitario, descuento, iva_porcentaje, subtotal, iva, total FROM factura_detalles WHERE factura_id = ? ORDER BY id");
    $itSt->execute([$fact['id']]);
    $items = $itSt->fetchAll(PDO::FETCH_ASSOC);
    if (!$items) {
        $errores++;
        echo "[ERROR] Factura sin detalles: {$fact['numero_factura']} ({$fact['id']})\n";
        continue;
    }

    $pdfRel = emxFactRelativePdfPath('factura', $fact['numero_factura']);
    $pdfAbs = emxFactAbsoluteFromRelative($pdfRel);
    $clave = $fact['clave_acceso_simulada'] ?? emxClaveAccesoSimulada($fact['numero_factura'], $fact['pedido_id'] ?? '');

    $ok = emxCrearPdfBasico(
        $pdfAbs,
        'Factura de compra',
        'Pedido #' . strtoupper(substr((string)($fact['pedido_id'] ?? ''), 0, 8)) . ' | Clave de acceso: ' . $clave,
        $empresa,
        $cliente,
        $fact['numero_factura'],
        $items,
        [
            'subtotal' => (float)($fact['subtotal'] ?? 0),
            'descuento' => (float)($fact['descuento'] ?? 0),
            'iva' => (float)($fact['iva'] ?? 0),
            'total' => (float)($fact['total'] ?? 0),
        ],
        'FACTURA'
    );

    if ($ok && is_file($pdfAbs)) {
        $pdo->prepare("UPDATE facturas SET pdf_url = ? WHERE id = ?")->execute([$pdfRel, $fact['id']]);
        $regeneradas++;
        echo "[OK] PDF regenerado: {$fact['numero_factura']} -> $pdfRel\n";
    } else {
        $errores++;
        echo "[ERROR] No se pudo regenerar PDF: {$fact['numero_factura']} ({$fact['id']})\n";
    }
}

echo "\nResumen:\n";
echo "Total revisadas: $total\n";
echo "Ya existentes: $existentes\n";
echo "Regeneradas: $regeneradas\n";
echo "Errores: $errores\n";
exit($errores > 0 ? 1 : 0);
