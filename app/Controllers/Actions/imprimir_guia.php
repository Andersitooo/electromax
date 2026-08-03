<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
emxRequireLogin();

$codigo = $_GET['codigo'] ?? null;
if (!$codigo) {
    die("Código de guía no válido.");
}

$stmt = $pdo->prepare("
    SELECT d.*, u.nombres, u.apellidos, u.email, u.telefono, 
           p.direccion as direccion_pedido, p.ciudad as ciudad_pedido, p.provincia as provincia_pedido
    FROM devoluciones d
    JOIN usuarios u ON d.usuario_id = u.id
    JOIN pedidos p ON d.pedido_id = p.id
    WHERE d.codigo_guia = ?
");
$stmt->execute([$codigo]);
$dev = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dev) {
    die("Guía no encontrada en el sistema.");
}
if (!emxEsAdmin() && $dev['usuario_id'] !== emxUsuarioId()) {
    http_response_code(403);
    exit('No puedes imprimir esta guía.');
}
?><!DOCTYPE html><html lang="es"><head>
<!-- Favicon ElectroMax global -->
<link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
<link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
<link rel="manifest" href="assets/favicon/site.webmanifest">
<meta name="theme-color" content="#0b4da2">
<meta charset="UTF-8"><title>Guía de Devolución - <?= $codigo ?></title><style>body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #000; background: #f0f0f0; }
        .guia-container { background: white; border: 2px dashed #000; padding: 30px; max-width: 800px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 28px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 14px; color: #555; }
        .row { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .col { width: 48%; }
        .box { border: 1px solid #000; padding: 15px; min-height: 120px; }
        .box h3 { margin: 0 0 10px; font-size: 14px; background: #eee; padding: 5px; text-transform: uppercase; font-weight: bold; }
        .box p { margin: 5px 0; font-size: 14px; line-height: 1.4; }
        .codigo-grande { text-align: center; font-size: 36px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; border: 3px solid #000; padding: 15px; background: #f9f9f9; }
        .footer { text-align: center; font-size: 12px; margin-top: 30px; border-top: 1px solid #000; padding-top: 15px; color: #555; }
        
        .no-print { text-align: center; margin-bottom: 20px; }
        .btn-print { padding: 10px 20px; font-size: 16px; cursor: pointer; background: #06b6d4; color: white; border: none; border-radius: 5px; font-weight: bold; }
        .btn-print:hover { background: #0891b2; }
        
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
            .guia-container { border: 2px solid #000; margin: 0; max-width: 100%; }
        }
    </style></head><body><div class="no-print"><button onclick="window.print()" class="btn-print">Imprimir Guía</button><a href="mi_cuenta.php?seccion=devoluciones" style="margin-left: 15px; color: #333; text-decoration: none;">← Volver a Mis Devoluciones</a></div><div class="guia-container"><div class="header"><h1>ElectroMax - Guía de Devolución</h1><p>Centro de Logística y Devoluciones</p></div><div class="codigo-grande"><?= htmlspecialchars($codigo) ?></div><div class="row"><div class="col"><div class="box"><h3>Destinatario (Almacén ElectroMax)</h3><p><strong>Matriz Babahoyo</strong></p><p>Av. Guayaquil y 10 de Agosto</p><p>Babahoyo, Los Ríos, Ecuador</p><p>Tel: 04-273-XXXX</p><p>Horario: Lun-Vie 08:00 - 18:00</p></div></div><div class="col"><div class="box"><h3>Remitente (Cliente)</h3><p><strong><?= htmlspecialchars($dev['nombres'] . ' ' . $dev['apellidos']) ?></strong></p><p><?= htmlspecialchars($dev['direccion_pedido']) ?></p><p><?= htmlspecialchars($dev['ciudad_pedido']) ?>, <?= htmlspecialchars($dev['provincia_pedido']) ?></p><p>Tel: <?= htmlspecialchars($dev['telefono']) ?></p><p>Email: <?= htmlspecialchars($dev['email']) ?></p></div></div></div><div class="row"><div class="col" style="width: 100%;"><div class="box"><h3>Contenido y Motivo de Devolución</h3><p><strong>Pedido Original:</strong>#<?= strtoupper(substr($dev['pedido_id'], 0, 8)) ?></p><p><strong>Motivo:</strong><?= ucfirst(str_replace('_', ' ', $dev['motivo'])) ?></p><p><strong>Descripción:</strong><?= htmlspecialchars($dev['descripcion']) ?></p><p><strong>Solución Solicitada:</strong><?= ucfirst(str_replace('_', ' ', $dev['solucion_propuesta'])) ?></p></div></div></div><div class="footer"><p>Por favor, imprima esta guía, péguela visiblemente en el exterior del paquete y entréguela a la transportadora autorizada o espere la recogida a domicilio.</p><p>ElectroMax © <?= date('Y') ?>- Todos los derechos reservados.</p></div></div><script src="assets/emx_modales.js"></script></body></html>