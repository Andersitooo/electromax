<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_CONFIG_PATH . '/company.php';

$id = trim($_GET['id'] ?? '');
$origen = $_GET['origen'] ?? '';
if (!emxIsUuid($id)) { http_response_code(400); exit('Producto inválido'); }

$stmt = $pdo->prepare("\n    SELECT p.*, c.nombre AS categoria, m.nombre AS marca, m.pais_origen, pm.url AS imagen\n    FROM productos p\n    LEFT JOIN categorias c ON c.id = p.categoria_id\n    LEFT JOIN marcas m ON m.id = p.marca_id\n    LEFT JOIN producto_multimedia pm ON pm.producto_id = p.id AND pm.orden = 1\n    WHERE p.id = ? AND p.deleted_at IS NULL AND p.is_active = TRUE\n");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$producto) { http_response_code(404); exit('Producto no encontrado'); }

$specs = json_decode($producto['especificaciones_tecnicas'] ?? '{}', true);
$specs = is_array($specs) ? $specs : [];
require_once EMX_HELPERS_PATH . '/funciones_ficha_tecnica.php';

$grupos = emxPrepararGruposFicha($specs);
$volver = $origen === 'admin' ? 'admin.php?module=productos' : 'producto.php?id=' . urlencode($id);
$logo = defined('EMX_EMPRESA_LOGO') ? EMX_EMPRESA_LOGO : 'assets/electromax_logo.png';
$datosProducto = [];
foreach ([
    'SKU' =>$producto['sku'] ?? '',
    'Modelo' =>$producto['modelo'] ?? '',
    'Marca' =>$producto['marca'] ?? '',
    'Categoría' =>$producto['categoria'] ?? '',
] as $label =>$value) {
    if (trim((string)$value) !== '') $datosProducto[] = [$label, $value];
}
$specCount = 0;
foreach ($grupos as $items) $specCount += count($items);
?><!DOCTYPE html><html lang="es"><head>
<!-- Favicon ElectroMax global -->
<link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
<link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
<link rel="manifest" href="assets/favicon/site.webmanifest">
<meta name="theme-color" content="#0b4da2">
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Ficha Técnica - <?= htmlspecialchars($producto['nombre']) ?></title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"><style>*{font-family:Inter,sans-serif}body{background:radial-gradient(circle at top left,#dbeafe 0,#eef6ff 28%,#f8fafc 55%,#eef2ff 100%)}
.card{box-shadow:0 30px 80px -35px rgba(15,23,42,.45)}
.logo-emx{filter:drop-shadow(0 16px 26px rgba(15,23,42,.24));}
.brand-strip{background:linear-gradient(135deg,#cfe3ff 0%,#eaf4ff 45%,#b8d4ff 100%)}
.spec-card{break-inside:avoid;min-height:132px;background:linear-gradient(180deg,#fff 0%,#f8fbff 100%)}
.spec-card:hover{transform:translateY(-1px);box-shadow:0 18px 40px -28px rgba(15,23,42,.45)}
@media(max-width:640px){.brand-logo{width:220px!important}.brand-strip{padding-top:2rem!important}}
@media print{.no-print{display:none!important}body{background:white}.card{box-shadow:none;border:0}.page{max-width:100%;padding:0}.brand-strip{background:#eef6ff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}.spec-card{box-shadow:none!important;transform:none!important}}
</style></head><body class="min-h-screen py-8 px-4"><div class="page max-w-6xl mx-auto"><div class="no-print flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6"><a href="<?= htmlspecialchars($volver) ?>" class="inline-flex items-center gap-2 text-slate-600 hover:text-blue-700 font-bold"><i class="fas fa-arrow-left"></i>Volver</a><div class="flex flex-wrap gap-3"><button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-white/80 border border-slate-200 font-bold hover:bg-white"><i class="fas fa-print mr-2"></i>Imprimir</button><a href="ficha_tecnica_pdf.php?id=<?= urlencode($id) ?>" class="px-5 py-2.5 rounded-xl bg-blue-700 text-white font-bold hover:bg-blue-800"><i class="fas fa-file-pdf mr-2"></i>Descargar PDF</a></div></div><article class="card bg-white rounded-[2rem] overflow-hidden border border-slate-200"><header class="brand-strip relative overflow-hidden px-8 md:px-10 pt-8 pb-9 border-b border-blue-100"><div class="absolute -right-16 -top-16 w-72 h-72 bg-blue-500/10 rounded-full blur-2xl"></div><div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center"><div class="lg:col-span-8"><img src="<?= htmlspecialchars($logo) ?>" alt="ElectroMax" class="brand-logo logo-emx w-[310px] max-w-full h-auto object-contain mb-5"><p class="uppercase tracking-[.28em] text-blue-700 text-xs font-black mb-3">Ficha técnica oficial</p><h1 class="text-3xl md:text-5xl font-black leading-tight text-slate-950 mb-5"><?= htmlspecialchars($producto['nombre']) ?></h1><?php if ($datosProducto): ?><div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm"><?php foreach ($datosProducto as [$label, $value]): ?><div class="bg-white/55 backdrop-blur-sm border border-blue-100 rounded-2xl px-4 py-3 shadow-sm"><p class="text-blue-700 text-[11px] font-black uppercase tracking-wide"><?= htmlspecialchars($label) ?></p><p class="font-black text-slate-900 mt-1 leading-tight"><?= htmlspecialchars($value) ?></p></div><?php endforeach; ?></div><?php endif; ?></div><div class="lg:col-span-4"><div class="bg-white/55 backdrop-blur-sm rounded-3xl p-5 shadow-xl border border-blue-100 h-72 flex items-center justify-center"><?php if (!empty($producto['imagen'])): ?><img src="<?= htmlspecialchars($producto['imagen']) ?>" class="w-full h-full object-contain" alt="Producto"><?php else: ?><div class="w-full h-full bg-blue-50/60 rounded-2xl flex items-center justify-center text-blue-200"><i class="fas fa-box text-6xl"></i></div><?php endif; ?></div></div></div></header><div class="p-8 md:p-10"><?php if (!empty(trim((string)($producto['descripcion_corta'] ?? '')))): ?><section class="mb-8 rounded-3xl bg-slate-50 border border-slate-200 p-7"><div class="flex items-center gap-3 mb-4"><span class="w-11 h-11 rounded-2xl bg-blue-700 text-white flex items-center justify-center"><i class="fas fa-align-left"></i></span><div><p class="text-xs uppercase tracking-[.22em] text-blue-700 font-black">Resumen del producto</p><h2 class="text-2xl font-black text-slate-950">Descripción comercial registrada</h2></div></div><p class="text-slate-700 leading-8 whitespace-pre-line"><?= htmlspecialchars(trim((string)$producto['descripcion_corta'])) ?></p></section><?php endif; ?><section><div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-6"><div><p class="text-xs uppercase tracking-[.25em] text-blue-700 font-black">Detalle técnico</p><h2 class="text-3xl font-black text-slate-950"><i class="fas fa-list-check text-blue-700 mr-2"></i>Especificaciones técnicas</h2><p class="text-sm text-slate-500 mt-2">Información tomada únicamente de las especificaciones registradas del producto.</p></div><span class="rounded-full bg-blue-50 text-blue-700 px-4 py-2 text-sm font-black border border-blue-100"><?= $specCount ?>especificación(es)</span></div><?= emxRenderFichaPremium($specs) ?></section></div><?php if (is_file(EMX_VIEWS_PATH . '/components/footer.php')) include EMX_VIEWS_PATH . '/components/footer.php'; ?></article></div><script src="assets/emx_modales.js"></script></body></html>