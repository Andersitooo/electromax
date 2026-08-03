<?php
/**
 * Vista separada de `carrito.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `carrito.php`.
 *
 * Las variables usadas aquí vienen del controlador raíz por compatibilidad.
 */
?>
<!DOCTYPE html><html lang="es"><head>
<!-- Favicon ElectroMax global -->
<link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
<link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
<link rel="manifest" href="assets/favicon/site.webmanifest">
<meta name="theme-color" content="#0b4da2">
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Carrito de Compras - ElectroMax</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"><style>* { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        body { background-color: #f8fafc; }
        
        .btn-primary { 
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-primary:hover { 
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%); 
            transform: translateY(-2px); 
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4); 
        }
        
        .qty-btn { 
            transition: all 0.2s; 
        }
        .qty-btn:hover { 
            background: #f1f5f9; 
            color: #0f172a;
        }
        
        .product-card {
            transition: all 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
        }

        /* Modal Animations */
        .modal-backdrop {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .modal-backdrop.active {
            opacity: 1;
            visibility: visible;
        }
        .modal-content {
            transform: scale(0.95) translateY(10px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .modal-backdrop.active .modal-content {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .delivery-option-card { min-height: 100%; }
        .delivery-options-stack { display: flex; flex-direction: column; gap: 1.25rem; }
        .calendar-strip, .delivery-events-list { display: flex; flex-direction: column; gap: .8rem; }
        .calendar-event {
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 1.15rem;
            padding: 1rem;
            text-align: left;
            transition: all .2s ease;
            cursor: pointer;
            width: 100%;
            overflow: hidden;
        }
        .calendar-event:hover, .calendar-event.active { border-color: #2563eb; box-shadow: 0 14px 26px -18px rgba(37,99,235,.65); transform: translateY(-1px); }
        .calendar-event .calendar-detail { display: block; }
        .calendar-event.active .calendar-detail { display: block; }
        .calendar-day { width: 3.75rem; height: 3.75rem; border-radius: 1rem; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1; flex: 0 0 auto; box-shadow: 0 10px 20px -16px rgba(15,23,42,.45); }
        .calendar-day strong { font-size: 1.08rem; }
        .calendar-day span { font-size: .64rem; text-transform: uppercase; font-weight: 900; letter-spacing: .06em; }
        .delivery-line-item { display: grid; grid-template-columns: 4.25rem minmax(0,1fr); gap: 1rem; align-items: stretch; }
        .delivery-line-content { min-width: 0; display: flex; flex-direction: column; gap: .65rem; }
        .delivery-line-head { display: flex; align-items: flex-start; justify-content: space-between; gap: .8rem; }
        .delivery-line-title { font-size: .98rem; line-height: 1.25; font-weight: 900; color: #020617; }
        .delivery-line-meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(145px, 1fr)); gap: .45rem; }
        .delivery-pill { display: inline-flex; align-items: center; justify-content: center; gap: .38rem; padding: .55rem .7rem; border-radius: .9rem; font-size: .76rem; font-weight: 900; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; white-space: nowrap; }
        .delivery-days-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 4.5rem; }
        .delivery-description { font-size: .82rem; line-height: 1.55; color: #64748b; background: #f8fafc; border: 1px solid #eef2f7; border-radius: .9rem; padding: .75rem .85rem; }
        @media (max-width: 640px) {
            .delivery-line-item { grid-template-columns: 3.75rem minmax(0,1fr); gap: .8rem; }
            .calendar-day { width: 3.35rem; height: 3.35rem; border-radius: .9rem; }
            .delivery-line-head { flex-direction: column; align-items: flex-start; }
            .delivery-line-meta { grid-template-columns: 1fr; }
            .delivery-days-badge { min-width: auto; }
        }

    </style></head><body class="text-slate-800 flex flex-col min-h-screen"><?php if (is_file(EMX_VIEWS_PATH . '/components/navbar.php')) include EMX_VIEWS_PATH . '/components/navbar.php'; ?><main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow"><div class="flex items-center gap-3 mb-8"><div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600"><i class="fas fa-shopping-bag text-xl"></i></div><div><h1 class="text-3xl font-bold text-slate-900">Tu Carrito</h1><p class="text-slate-500 text-sm"><?= $total_items ?>producto<?= $total_items !== 1 ? 's' : '' ?>en tu carrito</p></div></div><?php if ($msg): ?><div class="mb-6 p-4 rounded-xl border flex items-center gap-3 fade-in <?= $msg_type === 'error' ? 'bg-red-50 border-red-200 text-red-700' : ($msg_type === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700') ?>"><i class="fas <?= $msg_type === 'error' ? 'fa-circle-exclamation' : ($msg_type === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-check') ?>text-lg"></i><span class="font-medium"><?= htmlspecialchars($msg) ?></span></div><?php endif; ?><?php if (empty($productos_carrito)): ?><div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-16 text-center max-w-2xl mx-auto"><div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6"><i class="fas fa-shopping-cart text-4xl text-slate-400"></i></div><h2 class="text-2xl font-bold text-slate-900 mb-3">Tu carrito está vacío</h2><p class="text-slate-500 mb-8 max-w-md mx-auto">Parece que aún no has agregado ningún producto. ¡Explora nuestras categorías y encuentra lo que necesitas!</p><a href="index.php" class="inline-flex items-center gap-2 px-8 py-4 bg-slate-900 text-white rounded-xl font-semibold hover:bg-slate-800 transition shadow-lg shadow-slate-900/20"><i class="fas fa-store"></i>Ir a la tienda
                </a></div><?php else: ?><div class="grid grid-cols-1 lg:grid-cols-3 gap-8"><!-- Lista de Productos --><div class="lg:col-span-2 space-y-4"><?php foreach ($productos_carrito as $item): ?><div class="product-card bg-white rounded-2xl shadow-sm border border-slate-100 p-5 sm:p-6 flex flex-col sm:flex-row sm:flex-wrap items-start sm:items-center gap-6"><!-- Imagen --><div class="w-full sm:w-28 h-28 bg-slate-50 rounded-xl border border-slate-200 p-3 flex items-center justify-center flex-shrink-0"><?php if (!empty($item['imagen'])): ?><img src="<?= htmlspecialchars($item['imagen']) ?>" class="w-full h-full object-contain"><?php else: ?><i class="fas fa-image text-3xl text-slate-300"></i><?php endif; ?></div><!-- Info --><div class="flex-1 min-w-0"><h3 class="font-bold text-slate-900 text-lg mb-1 truncate"><?= htmlspecialchars($item['nombre']) ?></h3><p class="text-sm text-slate-500 mb-3">IVA incluido: <?= $item['iva'] ?>%</p><?php if ($item['descuento_aplicado'] >0): ?><div class="flex items-center gap-3 flex-wrap"><span class="text-2xl font-extrabold text-emerald-600">$<?= number_format($item['precio_final'], 2) ?></span><span class="text-sm text-slate-400 line-through">$<?= number_format($item['precio_con_iva'], 2) ?></span><span class="px-2.5 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-lg border border-red-200">-<?= number_format($item['descuento_aplicado']) ?>%</span></div><?php if ($item['ahorro'] >0): ?><p class="text-xs text-emerald-600 font-medium mt-1"><i class="fas fa-piggy-bank mr-1"></i>Ahorras $<?= number_format($item['ahorro'], 2) ?>en este ítem</p><?php endif; ?><?php if (!empty($item['descuento_volumen'])): ?><p class="text-xs text-purple-700 font-bold mt-1"><i class="fas fa-layer-group mr-1"></i>Descuento por volumen aplicado: <?= number_format((float)$item['descuento_volumen'], 0) ?>%<?= !empty($item['rango_volumen_label']) ? ' · ' . htmlspecialchars($item['rango_volumen_label']) : '' ?></p><?php endif; ?><?php else: ?><p class="text-2xl font-extrabold text-slate-900">$<?= number_format($item['precio_final'], 2) ?></p><?php endif; ?></div><!-- Controles --><div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end"><div class="flex items-center bg-slate-50 rounded-xl border border-slate-200 p-1"><a href="?action=update&id=<?= $item['producto_id'] ?>&cantidad=<?= max(1, $item['cantidad'] - 1) ?>" class="qty-btn w-10 h-10 flex items-center justify-center rounded-lg text-slate-600 font-bold text-lg">-</a><form method="GET" action="carrito.php" class="flex items-center"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= htmlspecialchars($item['producto_id']) ?>"><input type="number" name="cantidad" min="1" value="<?= (int)$item['cantidad'] ?>" class="w-20 text-center font-bold text-slate-900 bg-transparent outline-none" onchange="this.form.submit()"></form><a href="?action=update&id=<?= $item['producto_id'] ?>&cantidad=<?= $item['cantidad'] + 1 ?>" class="qty-btn w-10 h-10 flex items-center justify-center rounded-lg text-slate-600 font-bold text-lg">+</a></div><div class="text-right min-w-[90px]"><p class="text-xl font-extrabold text-slate-900">$<?= number_format($item['total'], 2) ?></p></div><button onclick="showConfirmModal('Eliminar producto', '¿Estás seguro de que deseas eliminar este producto del carrito?', '?action=remove&id=<?= $item['producto_id'] ?>')" class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="Eliminar"><i class="fas fa-trash-can"></i></button></div><?php if (!empty($item['requiere_backorder'])): 
                                $plan = $item['plan_backorder_preview'] ?? []; 
                                $aceptadoData = $_SESSION['backorder_planes'][$item['producto_id']] ?? null; 
                                $aceptado = !empty($aceptadoData); 
                                $opcionAceptada = $aceptadoData['opcion'] ?? null;
                                $parcial = $plan['opcion_parcial'] ?? null;
                                $totalBo = $plan['opcion_total'] ?? null;
                                $stockInmediato = (int)($plan['stock_actual'] ?? 0);
                                $cantidadSolicitada = (int)($plan['cantidad_solicitada'] ?? $item['cantidad']);
                                $faltante = max(0, (int)($plan['faltante'] ?? 0));
                            ?><div class="basis-full w-full mt-2 rounded-3xl border <?= $aceptado ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' ?>overflow-hidden shadow-sm"><div class="px-5 md:px-6 py-5 border-b <?= $aceptado ? 'border-emerald-200 bg-emerald-100/60' : 'border-amber-200 bg-amber-100/60' ?>"><div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4"><div class="max-w-3xl"><p class="text-xs uppercase tracking-[.18em] font-black <?= $aceptado ? 'text-emerald-700' : 'text-amber-700' ?>"><i class="fas fa-calendar-check mr-2"></i><?= $aceptado ? 'Calendario confirmado' : 'Stock inmediato insuficiente' ?></p><h4 class="text-xl font-black text-slate-950 mt-1"><?= $aceptado ? ('Entrega ' . ($opcionAceptada === 'parcial' ? 'parcial' : 'total') . ' seleccionada') : 'Elige cómo deseas recibir tu compra' ?></h4><p class="text-sm text-slate-700 mt-2 leading-relaxed">Solicitaste <strong><?= $cantidadSolicitada ?></strong>unidad(es). Hay <strong><?= $stockInmediato ?></strong>disponible(s) para despacho inmediato y se deben coordinar <strong><?= $faltante ?></strong>unidad(es) adicionales.
                                                </p></div><?php if (!$aceptado): ?><a href="?action=rechazar_backorder&id=<?= urlencode($item['producto_id']) ?>" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-50 shrink-0"><i class="fas fa-xmark mr-2"></i>Rechazar sobrestock
                                                </a><?php endif; ?></div><div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-5"><div class="bg-white/85 rounded-2xl border border-white px-4 py-3"><p class="text-[11px] uppercase tracking-wide text-slate-500 font-black">Cantidad solicitada</p><p class="text-2xl font-black text-slate-950"><?= $cantidadSolicitada ?></p></div><div class="bg-white/85 rounded-2xl border border-white px-4 py-3"><p class="text-[11px] uppercase tracking-wide text-slate-500 font-black">Entrega inmediata</p><p class="text-2xl font-black text-blue-700"><?= min($stockInmediato, $cantidadSolicitada) ?></p></div><div class="bg-white/85 rounded-2xl border border-white px-4 py-3"><p class="text-[11px] uppercase tracking-wide text-slate-500 font-black">Pendiente por coordinar</p><p class="text-2xl font-black text-amber-700"><?= $faltante ?></p></div></div></div><?php if (($plan['recomendacion'] ?? '') === 'sin_proveedores'): ?><div class="p-5 text-sm text-amber-800 font-semibold"><i class="fas fa-triangle-exclamation mr-2"></i>Este producto no tiene proveedores asociados para coordinar sobrestock. Rechaza el sobrestock o solicita ayuda a soporte.
                                        </div><?php else: ?><?php
                                            $parcialEventos = $parcial['eventos_calendario'] ?? [];
                                            $totalEventos = $totalBo['eventos_calendario'] ?? [];
                                        ?><div class="p-5 md:p-6 delivery-options-stack"><?php if (!empty($parcial)): ?><div class="delivery-option-card rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden h-full flex flex-col <?= $opcionAceptada === 'parcial' ? 'ring-2 ring-blue-500' : '' ?>"><div class="px-5 py-5 border-b border-slate-100 bg-gradient-to-br from-blue-50 to-white"><div class="flex items-start justify-between gap-3"><div><p class="text-xs uppercase tracking-[.16em] text-blue-700 font-black">Opción A</p><h5 class="text-xl font-black text-slate-950 mt-1"><i class="fas fa-box-open text-blue-600 mr-2"></i>Entrega parcial</h5><p class="text-sm text-slate-600 mt-2 leading-relaxed">Recibes primero lo disponible y luego los lotes programados en fechas claras. Ideal si necesitas parte del pedido antes.</p></div><?php if (($plan['recomendacion'] ?? '') === 'parcial'): ?><span class="text-[11px] px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-black whitespace-nowrap">Más rápida</span><?php endif; ?></div></div><div class="p-5 space-y-5 flex-1 flex flex-col"><div class="grid grid-cols-3 gap-3 text-center"><div class="rounded-2xl bg-slate-50 p-3 border border-slate-100"><p class="text-[10px] font-black text-slate-500 uppercase">Ahora</p><p class="font-black text-slate-900 text-xl"><?= (int)($parcial['despacho_inmediato'] ?? 0) ?></p></div><div class="rounded-2xl bg-slate-50 p-3 border border-slate-100"><p class="text-[10px] font-black text-slate-500 uppercase">Lotes</p><p class="font-black text-slate-900 text-xl"><?= count($parcial['lotes'] ?? []) ?></p></div><div class="rounded-2xl bg-slate-50 p-3 border border-slate-100"><p class="text-[10px] font-black text-slate-500 uppercase">Finaliza</p><p class="font-black text-slate-900 text-xs leading-tight"><?= htmlspecialchars($parcial['fecha_final_legible'] ?? ($parcial['fecha_final'] ?? 'Por confirmar')) ?></p></div></div><div class="rounded-3xl border border-blue-100 bg-blue-50/40 p-4"><div class="flex items-center justify-between gap-3 mb-4"><div><p class="text-xs uppercase tracking-wide font-black text-blue-700">Calendario de entregas parciales</p><p class="text-xs text-slate-500">Cada fila muestra fecha, unidades y tiempo estimado de entrega.</p></div><i class="fas fa-calendar-days text-blue-500 text-xl"></i></div><div class="delivery-events-list"><?php foreach ($parcialEventos as $i =>$ev):
                                                                    $fechaEv = $ev['fecha'] ?? null;
                                                                    $diaEv = $fechaEv ? date('d', strtotime($fechaEv)) : '--';
                                                                    $meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
                                                                    $mesEv = $fechaEv ? $meses[(int)date('n', strtotime($fechaEv))-1] : '---';
                                                                    $tipoEv = $ev['tipo'] ?? 'parcial';
                                                                    $badgeClass = $tipoEv === 'inmediato' ? 'bg-blue-600 text-white' : 'bg-white text-blue-700 border border-blue-100';
                                                                    $diasEv = (int)($ev['dias'] ?? 0);
                                                                ?><button type="button" class="calendar-event <?= $i === 0 ? 'active' : '' ?>" data-calendar-event><div class="delivery-line-item"><div class="calendar-day <?= $badgeClass ?>"><strong><?= $diaEv ?></strong><span><?= $mesEv ?></span></div><div class="delivery-line-content"><div class="delivery-line-head"><p class="delivery-line-title"><?= htmlspecialchars($ev['titulo'] ?? ('Despacho ' . ($i + 1))) ?></p><span class="delivery-days-badge text-[11px] px-3 py-1 rounded-full <?= $diasEv === 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700' ?>font-black whitespace-nowrap"><?= $diasEv === 0 ? 'Ahora' : '+' . $diasEv . ' días' ?></span></div><div class="delivery-line-meta"><span class="delivery-pill"><i class="fas fa-box"></i><?= (int)($ev['cantidad'] ?? 0) ?>unidad(es)</span><span class="delivery-pill"><i class="fas fa-clock"></i><?= $diasEv === 0 ? 'Disponible ahora' : 'En ' . $diasEv . ' día(s)' ?></span><span class="delivery-pill"><i class="fas fa-calendar-day"></i><?= htmlspecialchars($ev['fecha_legible'] ?? ($fechaEv ?: 'Por confirmar')) ?></span></div><div class="calendar-detail delivery-description"><?= htmlspecialchars($ev['descripcion'] ?? 'Despacho programado según disponibilidad y producción.') ?></div></div></div></button><?php endforeach; ?></div></div><?php if (!$aceptado): ?><a href="?action=aceptar_backorder&id=<?= urlencode($item['producto_id']) ?>&opcion=parcial" class="mt-auto w-full inline-flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-xl text-sm font-black hover:bg-blue-700"><i class="fas fa-check mr-2"></i>Aceptar entrega parcial</a><?php endif; ?></div></div><?php endif; ?><?php if (!empty($totalBo)): ?><div class="delivery-option-card rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden h-full flex flex-col <?= $opcionAceptada === 'total' ? 'ring-2 ring-emerald-500' : '' ?>"><div class="px-5 py-5 border-b border-slate-100 bg-gradient-to-br from-emerald-50 to-white"><div class="flex items-start justify-between gap-3"><div><p class="text-xs uppercase tracking-[.16em] text-emerald-700 font-black">Opción B</p><h5 class="text-xl font-black text-slate-950 mt-1"><i class="fas fa-truck-ramp-box text-emerald-600 mr-2"></i>Entrega total</h5><p class="text-sm text-slate-600 mt-2 leading-relaxed">Esperas a que se complete todo el pedido y recibes una sola entrega consolidada. Es la opción más simple si prefieres no recibir por partes.</p></div><?php if (($plan['recomendacion'] ?? '') === 'total'): ?><span class="text-[11px] px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-black whitespace-nowrap">Recomendada</span><?php endif; ?></div></div><div class="p-5 space-y-5 flex-1 flex flex-col"><div class="grid grid-cols-3 gap-3 text-center"><div class="rounded-2xl bg-slate-50 p-3 border border-slate-100"><p class="text-[10px] font-black text-slate-500 uppercase">Total</p><p class="font-black text-slate-900 text-xl"><?= $cantidadSolicitada ?></p></div><div class="rounded-2xl bg-slate-50 p-3 border border-slate-100"><p class="text-[10px] font-black text-slate-500 uppercase">Espera</p><p class="font-black text-slate-900 text-xl"><?= (int)($totalBo['dias'] ?? 0) ?>días</p></div><div class="rounded-2xl bg-slate-50 p-3 border border-slate-100"><p class="text-[10px] font-black text-slate-500 uppercase">Entrega</p><p class="font-black text-slate-900 text-xs leading-tight"><?= htmlspecialchars($totalBo['fecha_legible'] ?? ($totalBo['fecha'] ?? 'Por confirmar')) ?></p></div></div><div class="rounded-3xl border border-emerald-100 bg-emerald-50/40 p-4"><div class="flex items-center justify-between gap-3 mb-4"><div><p class="text-xs uppercase tracking-wide font-black text-emerald-700">Calendario visual</p><p class="text-xs text-slate-500">Una sola fecha para todo el pedido.</p></div><i class="fas fa-calendar-check text-emerald-500 text-xl"></i></div><div class="calendar-strip"><?php foreach ($totalEventos as $i =>$ev):
                                                                    $fechaEv = $ev['fecha'] ?? null;
                                                                    $diaEv = $fechaEv ? date('d', strtotime($fechaEv)) : '--';
                                                                    $meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
                                                                    $mesEv = $fechaEv ? $meses[(int)date('n', strtotime($fechaEv))-1] : '---';
                                                                ?><button type="button" class="calendar-event active" data-calendar-event><div class="delivery-line-item"><div class="calendar-day bg-emerald-600 text-white"><strong><?= $diaEv ?></strong><span><?= $mesEv ?></span></div><div class="delivery-line-content"><div class="delivery-line-head"><p class="delivery-line-title"><?= htmlspecialchars($ev['titulo'] ?? 'Entrega total') ?></p><span class="delivery-days-badge text-[11px] px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-black whitespace-nowrap">+<?= (int)($ev['dias'] ?? 0) ?>días</span></div><div class="delivery-line-meta"><span class="delivery-pill"><i class="fas fa-boxes-stacked"></i><?= (int)($ev['cantidad'] ?? $cantidadSolicitada) ?>unidad(es)</span><span class="delivery-pill"><i class="fas fa-calendar-check"></i><?= htmlspecialchars($ev['fecha_legible'] ?? ($fechaEv ?: 'Por confirmar')) ?></span></div><div class="calendar-detail delivery-description"><?= htmlspecialchars($ev['descripcion'] ?? 'Entrega consolidada.') ?></div></div></div></button><?php endforeach; ?></div></div><?php if (!$aceptado): ?><a href="?action=aceptar_backorder&id=<?= urlencode($item['producto_id']) ?>&opcion=total" class="mt-auto w-full inline-flex items-center justify-center px-4 py-3 bg-emerald-600 text-white rounded-xl text-sm font-black hover:bg-emerald-700"><i class="fas fa-check mr-2"></i>Aceptar entrega total</a><?php endif; ?></div></div><?php endif; ?></div><?php endif; ?></div><?php endif; ?></div><?php endforeach; ?><div class="flex justify-end pt-4"><button onclick="showConfirmModal('Vaciar carrito', '¿Estás seguro de que deseas eliminar todos los productos? Esta acción no se puede deshacer.', '?action=clear')" class="text-red-600 hover:text-red-700 font-semibold flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-red-50 transition"><i class="fas fa-trash-alt"></i>Vaciar carrito completo
                        </button></div></div><!-- Resumen del Pedido --><div class="lg:col-span-1"><div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6 sticky top-24"><h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2"><i class="fas fa-receipt text-blue-600"></i>Resumen del pedido
                        </h2><div class="space-y-4 pb-6 border-b border-slate-100"><div class="flex justify-between text-sm"><span class="text-slate-500">Subtotal (sin descuentos):</span><span class="font-semibold text-slate-900">$<?= number_format($subtotal_sin_desc, 2) ?></span></div><?php if ($total_descuento >0): ?><div class="flex justify-between text-sm text-emerald-600 bg-emerald-50 p-3 rounded-xl border border-emerald-100"><span class="flex items-center gap-2 font-medium"><i class="fas fa-tag"></i>Descuento aplicado:</span><span class="font-bold">-$<?= number_format($total_descuento, 2) ?></span></div><?php endif; ?><div class="flex justify-between text-sm"><span class="text-slate-500 flex items-center gap-2"><i class="fas fa-file-invoice text-slate-400"></i>IVA:</span><span class="font-semibold text-slate-900">$<?= number_format($total_iva, 2) ?></span></div><div class="flex justify-between text-sm text-emerald-600"><span class="flex items-center gap-2 font-medium"><i class="fas fa-truck-fast"></i>Envío:</span><span class="font-bold">GRATIS</span></div></div><div class="flex justify-between items-center py-6"><span class="text-lg font-bold text-slate-900">Total a pagar:</span><span class="text-3xl font-extrabold text-slate-900">$<?= number_format($total_general, 2) ?></span></div><?php if (emxCarritoTieneBackorderPendiente()): ?><div class="w-full bg-amber-50 border border-amber-200 text-amber-800 font-bold py-4 rounded-xl text-center px-4"><i class="fas fa-triangle-exclamation mr-2"></i>Acepta o ajusta el calendario de sobrestock antes de pagar.
                            </div><?php else: ?><a href="checkout.php" class="block w-full btn-primary text-white font-bold py-4 rounded-xl text-center shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2"><i class="fas fa-lock text-sm"></i>Proceder al pago seguro
                            </a><?php endif; ?><div class="mt-6 flex items-center justify-center gap-4 text-slate-400 text-2xl"><i class="fab fa-cc-visa hover:text-blue-600 transition"></i><i class="fab fa-cc-mastercard hover:text-red-600 transition"></i><i class="fab fa-cc-amex hover:text-blue-400 transition"></i><i class="fab fa-cc-paypal hover:text-blue-800 transition"></i></div><p class="text-xs text-slate-400 text-center mt-3 flex items-center justify-center gap-1"><i class="fas fa-shield-halved text-emerald-500"></i>Transacción 100% segura y encriptada
                        </p></div></div></div><?php endif; ?></main><!-- ========================================== --><!-- MODAL ELEGANTE DE CONFIRMACIÓN (Reemplaza alert) --><!-- ========================================== --><div id="confirmModal" class="modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"><div class="modal-content bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-100"><div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5"><i class="fas fa-exclamation-triangle text-2xl text-red-600"></i></div><h3 class="text-xl font-bold text-slate-900 mb-2" id="modalTitle">¿Estás seguro?</h3><p class="text-slate-500 mb-8 leading-relaxed" id="modalMessage">Esta acción no se puede deshacer.</p><div class="flex gap-3"><button onclick="closeModal()" class="flex-1 py-3 border border-slate-200 text-slate-700 rounded-xl font-semibold hover:bg-slate-50 transition active:scale-95">Cancelar
                </button><a id="modalConfirmBtn" href="#" class="flex-1 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition shadow-lg shadow-red-600/30 active:scale-95 flex items-center justify-center gap-2"><i class="fas fa-check"></i>Confirmar
                </a></div></div></div><script>// Lógica del Modal Elegante
        function showConfirmModal(title, message, url) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').textContent = message;
            document.getElementById('modalConfirmBtn').href = url;
            
            const modal = document.getElementById('confirmModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevenir scroll
        }

        function closeModal() {
            const modal = document.getElementById('confirmModal');
            modal.classList.remove('active');
            document.body.style.overflow = ''; // Restaurar scroll
        }



        document.querySelectorAll('[data-calendar-event]').forEach(function(card) {
            card.addEventListener('click', function() {
                const parent = card.closest('.calendar-strip');
                if (parent) {
                    parent.querySelectorAll('[data-calendar-event]').forEach(function(other) {
                        if (other !== card) other.classList.remove('active');
                    });
                }
                card.classList.toggle('active');
            });
        });

        // Cerrar modal al hacer clic fuera del contenido
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Cerrar modal con la tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script><?php if (is_file(EMX_VIEWS_PATH . '/components/footer.php')) include EMX_VIEWS_PATH . '/components/footer.php'; ?><script src="assets/emx_modales.js"></script></body></html>