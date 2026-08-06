<?php
/**
 * Vista separada de `index.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `index.php`.
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
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>ElectroMax | Tecnología para tu Hogar</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"><style>*{font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased}
        .no-scrollbar::-webkit-scrollbar{display:none}.no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
        .card-hover{transition:transform .3s ease,box-shadow .3s ease}
        .card-hover:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(15,23,42,0.12)}
        .img-hover{transition:transform .4s ease}.img-hover:hover{transform:scale(1.05)}
        .btn-cta{transition:background .2s ease,transform .15s ease}.btn-cta:hover{transform:translateY(-1px)}
        .discount-pulse{animation:dp 2.5s ease-in-out infinite}
        @keyframes dp{0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.4)}50%{box-shadow:0 0 0 6px rgba(239,68,68,0)}}
        .cat-icon-wrap{transition:transform .3s cubic-bezier(.175,.885,.32,1.275)}
        
        .filtros-sidebar { width: 280px; flex-shrink: 0; transition: all 0.3s ease; }
        .filtros-sidebar.oculto { width: 0; overflow: hidden; opacity: 0; }
        .filtros-sidebar .contenido { width: 280px; }
        .productos-grid { transition: all 0.3s ease; }
        
        .drawer-overlay { opacity: 0; visibility: hidden; transition: all 0.3s ease; }
        .drawer-overlay.activo { opacity: 1; visibility: visible; }
        .drawer-panel { transform: translateX(-100%); transition: transform 0.3s ease; }
        .drawer-overlay.activo .drawer-panel { transform: translateX(0); }
        
        .filtros-scroll::-webkit-scrollbar { width: 6px; }
        .filtros-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .filtros-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .filtros-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .toast-animation { animation: toastBounce 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); }
        @keyframes toastBounce {
            0% { opacity: 0; transform: translateY(40px) scale(0.8); }
            60% { opacity: 1; transform: translateY(-5px) scale(1.02); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes heartPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.4); }
            100% { transform: scale(1); }
        }
        .heart-anim { animation: heartPop 0.4s ease-in-out; }

        /* Ajustes finales de responsividad para móvil, tablet y escritorio. */
        @media (max-width: 640px) {
            .card-hover:hover { transform: none; box-shadow: 0 8px 18px rgba(15,23,42,0.08); }
            .filtros-sidebar { display: none; }
            .productos-grid { width: 100%; }
            #contenedor-productos, #productos-destacados-grid { gap: .85rem; }
            .cat-icon-wrap { width: 2.55rem !important; height: 2.55rem !important; }
        }
        @media (min-width: 641px) and (max-width: 1023px) {
            .productos-grid { width: 100%; }
        }
    </style></head><body class="flex flex-col min-h-screen bg-gray-50"><?php require EMX_VIEWS_PATH . '/components/navbar.php'; ?><?php if (!$filtro_activo): ?><!-- BENEFICIOS STRIP --><div class="bg-slate-800 py-2.5"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center gap-6 sm:gap-10"><div class="flex items-center gap-2 text-white"><div class="w-6 h-6 bg-emerald-500/20 rounded flex items-center justify-center"><i class="fas fa-truck-fast text-emerald-400 text-xs"></i></div><span class="text-xs font-medium">Envío gratis +$500</span></div><div class="flex items-center gap-2 text-white"><div class="w-6 h-6 bg-blue-500/20 rounded flex items-center justify-center"><i class="fas fa-shield-halved text-blue-400 text-xs"></i></div><span class="text-xs font-medium">Compra segura</span></div><div class="flex items-center gap-2 text-white"><div class="w-6 h-6 bg-amber-500/20 rounded flex items-center justify-center"><i class="fas fa-rotate-left text-amber-400 text-xs"></i></div><span class="text-xs font-medium">30 días devolución</span></div><div class="flex items-center gap-2 text-white"><div class="w-6 h-6 bg-slate-500/20 rounded flex items-center justify-center"><i class="fas fa-headset text-slate-400 text-xs"></i></div><span class="text-xs font-medium">Soporte 24/7</span></div></div></div><!-- HERO PRINCIPAL DINÁMICO --><?= emxRenderHomeSlot($pdo, $secciones_home, 'hero_principal') ?><!-- CATEGORIAS --><?php if (!empty($categorias_display)): ?><section class="bg-white py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="flex items-center justify-between mb-5"><h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Categorías</h2><span class="text-sm text-gray-400"><?= count($categorias_display) ?> disponibles</span></div><div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-4"><?php foreach ($categorias_display as $cat):
                $icon = getCategoryIcon($cat['nombre']);
                $accent = getCategoryAccent($cat['nombre']);
                $total_prod = isset($cat['total_productos']) ? (int)$cat['total_productos'] : 0;
                $am = ['blue'=>['bg'=>'bg-blue-600','light'=>'bg-blue-50','border'=>'border-blue-200'],'sky'=>['bg'=>'bg-sky-600','light'=>'bg-sky-50','border'=>'border-sky-200'],'indigo'=>['bg'=>'bg-indigo-600','light'=>'bg-indigo-50','border'=>'border-indigo-200'],'cyan'=>['bg'=>'bg-cyan-600','light'=>'bg-cyan-50','border'=>'border-cyan-200'],'orange'=>['bg'=>'bg-orange-600','light'=>'bg-orange-50','border'=>'border-orange-200'],'red'=>['bg'=>'bg-red-600','light'=>'bg-red-50','border'=>'border-red-200'],'amber'=>['bg'=>'bg-amber-600','light'=>'bg-amber-50','border'=>'border-amber-200'],'purple'=>['bg'=>'bg-purple-600','light'=>'bg-purple-50','border'=>'border-purple-200'],'slate'=>['bg'=>'bg-slate-600','light'=>'bg-slate-50','border'=>'border-slate-200'],'pink'=>['bg'=>'bg-pink-600','light'=>'bg-pink-50','border'=>'border-pink-200'],'rose'=>['bg'=>'bg-rose-600','light'=>'bg-rose-50','border'=>'border-rose-200'],'emerald'=>['bg'=>'bg-emerald-600','light'=>'bg-emerald-50','border'=>'border-emerald-200'],'violet'=>['bg'=>'bg-violet-600','light'=>'bg-violet-50','border'=>'border-violet-200'],'teal'=>['bg'=>'bg-teal-600','light'=>'bg-teal-50','border'=>'border-teal-200'],'yellow'=>['bg'=>'bg-yellow-600','light'=>'bg-yellow-50','border'=>'border-yellow-200']];
                $c = isset($am[$accent]) ? $am[$accent] : $am['slate'];
            ?><a href="index.php?categoria=<?= $cat['slug'] ?>" class="group block h-[140px] rounded-xl <?= $c['light'] ?> border <?= $c['border'] ?> p-4 card-hover flex flex-col items-center justify-center text-center"><div class="<?= $c['bg'] ?> w-12 h-12 rounded-xl flex items-center justify-center mb-3 cat-icon-wrap group-hover:scale-110 shadow-sm"><i class="fas <?= $icon ?> text-white text-xl"></i></div><h3 class="font-semibold text-slate-900 text-sm line-clamp-2 leading-tight mb-1"><?= htmlspecialchars($cat['nombre']) ?></h3><?php if ($total_prod >0): ?><span class="text-xs text-gray-400"><?= $total_prod ?> productos</span><?php else: ?><span class="text-xs text-gray-300">Explorar</span><?php endif; ?></a><?php endforeach; ?></div></div></section><?php endif; ?><!-- BANNERS DESPUÉS DE CATEGORÍAS --><?= emxRenderHomeSlot($pdo, $secciones_home, 'despues_categorias') ?><!-- PRODUCTOS DESTACADOS --><section class="bg-gray-50 py-10"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="flex items-end justify-between mb-6"><div><h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Productos Destacados</h2><p class="text-gray-500 text-sm mt-1">Lo último en tecnología para tu hogar</p></div><a href="index.php?descuento_min=10" class="hidden sm:flex items-center gap-1.5 text-sm font-semibold text-red-600 hover:text-red-700 transition"><i class="fas fa-fire text-xs"></i>Ver ofertas</a></div><?php if (!empty($productos)): ?><div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5" id="productos-destacados-grid"><?php foreach ($productos as $p):
                $iva = $p['iva_porcentaje'] ?? 15; $precio_con_iva = $p['precio_base'] * (1 + ($iva / 100));
                $tiene_descuento = false; $precio_final = $precio_con_iva; $porcentaje_descuento = 0;
                $raw_discount = $p['descuento_porcentaje'] ?? 0; $discount_val = ($raw_discount >0 && $raw_discount <= 1) ? $raw_discount * 100 : $raw_discount;
                if ($discount_val >0) { $hoy_check = date('Y-m-d'); $desde = $p['descuento_desde'] ?? null; $hasta = $p['descuento_hasta'] ?? null;
                    if ((!$desde || $hoy_check >= $desde) && (!$hasta || $hoy_check <= $hasta)) { $tiene_descuento = true; $porcentaje_descuento = round($discount_val); $precio_final = $precio_con_iva * (1 - ($porcentaje_descuento / 100)); }
                }
                $stock_bajo = $p['stock_actual_global'] <= 5 && $p['stock_actual_global'] >0;
                $promedio = round($p['promedio_calificacion'], 1); $total_reseñas = (int)$p['total_reseñas'];
                $en_wishlist_card = in_array($p['id'], $wishlist_ids);
            ?><div class="group flex flex-col card-hover bg-white rounded-xl border border-gray-200 overflow-hidden cursor-pointer" data-product-id="<?= $p['id'] ?>"><div class="relative aspect-square bg-gray-50 p-5 flex items-center justify-center overflow-hidden"><?php if (!empty($p['imagen_principal'])): ?><img src="<?= htmlspecialchars($p['imagen_principal']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" class="w-full h-full object-contain img-hover"><?php else: ?><div class="text-gray-300 flex flex-col items-center gap-2"><i class="fas fa-image text-4xl"></i><span class="text-xs font-semibold uppercase tracking-wider">Sin imagen</span></div><?php endif; ?><!-- BOTÓN WISHLIST --><button type="button" onclick="toggleWishlist('<?= $p['id'] ?>', this, event)" class="absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm hover:scale-110 transition z-20"><i class="<?= $en_wishlist_card ? 'fas text-red-500' : 'far text-slate-400' ?> fa-heart text-lg wishlist-icon"></i></button><?php if ($tiene_descuento): ?><span class="absolute top-3 left-3 bg-red-600 text-white text-xs font-bold px-2.5 py-1 rounded-full discount-pulse">-<?= $porcentaje_descuento ?>%</span><?php elseif ($stock_bajo): ?><span class="absolute top-3 left-3 bg-amber-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">Últimas unidades</span><?php elseif ($p['stock_actual_global'] == 0): ?><span class="absolute top-3 left-3 bg-gray-400 text-white text-[10px] font-bold px-2 py-1 rounded-full">Agotado</span><?php endif; ?></div><div class="p-4 flex flex-col flex-grow"><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1"><?= htmlspecialchars($p['marca'] ?? 'General') ?></p><h3 class="font-semibold text-slate-900 text-[15px] mb-2 line-clamp-2 leading-snug group-hover:text-blue-600 transition"><?= htmlspecialchars($p['nombre']) ?></h3><div class="flex items-center gap-1.5 mb-3"><div class="flex text-amber-400 text-xs gap-0.5"><?php 
                            $estrellas_completas = floor($promedio);
                            $tiene_media = ($promedio - $estrellas_completas) >= 0.5;
                            for ($i=1; $i<=5; $i++): 
                                if ($i <= $estrellas_completas): ?><i class="fas fa-star"></i><?php elseif ($i == $estrellas_completas + 1 && $tiene_media): ?><i class="fas fa-star-half-alt"></i><?php else: ?><i class="far fa-star text-gray-300"></i><?php endif; 
                            endfor; ?></div><?php if ($total_reseñas >0): ?><span class="text-xs text-gray-500">(<?= $total_reseñas ?>)</span><?php else: ?><span class="text-xs text-gray-400">Nuevo</span><?php endif; ?></div><div class="mt-auto pt-3 border-t border-gray-100"><?php if ($tiene_descuento): ?><div class="flex items-baseline gap-2 mb-1"><span class="text-xl font-extrabold text-emerald-600">$<?= number_format($precio_final, 2) ?></span><span class="text-sm text-gray-400 line-through">$<?= number_format($precio_con_iva, 2) ?></span></div><p class="text-xs text-emerald-500 font-medium mb-3">Ahorrás $<?= number_format($precio_con_iva - $precio_final, 2) ?></p><?php else: ?><span class="text-xl font-extrabold text-slate-900 mb-1 block">$<?= number_format($precio_final, 2) ?></span><p class="text-xs text-gray-400 mb-3">IVA <?= $iva ?>% incluido</p><?php endif; ?><button type="button" onclick="addToCart('<?= $p['id'] ?>', this, event)" class="w-full py-2.5 rounded-lg font-semibold flex items-center justify-center gap-2 btn-cta text-sm <?= $p['stock_actual_global'] >0 ? ($tiene_descuento ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-slate-900 text-white hover:bg-blue-700') : 'bg-gray-200 text-gray-500 cursor-not-allowed' ?>"><i class="fas fa-plus text-xs"></i><?= $p['stock_actual_global'] >0 ? 'Agregar al carrito' : 'Sin stock' ?></button></div></div></div><?php endforeach; ?></div><?php else: ?><div class="text-center py-16"><p class="text-gray-400">No hay productos disponibles ahora.</p></div><?php endif; ?></div></section><!-- BANNERS ENTRE PRODUCTOS --><?= emxRenderHomeSlot($pdo, $secciones_home, 'entre_productos') ?><!-- PRODUCTOS MAS VENDIDOS — CARRUSEL --><?php if (!empty($productos_best)):
    $best_chunks = array_chunk($productos_best, 4);
    $total_pages = count($best_chunks);
?><section class="bg-white py-10"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="flex items-center gap-3 mb-6"><div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center text-white shadow-sm"><i class="fas fa-fire text-sm"></i></div><div><h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Los Más Vendidos</h2><p class="text-gray-500 text-sm mt-0.5">Los favoritos de nuestros clientes</p></div></div><div class="relative"><div class="overflow-hidden"><div class="flex transition-transform duration-500 ease-in-out" id="bestCarousel"><?php foreach ($best_chunks as $chunk_idx =>$chunk): ?><div class="w-full flex-shrink-0"><div class="grid grid-cols-2 sm:grid-cols-4 gap-5"><?php foreach ($chunk as $idx =>$p):
                                $rank = ($chunk_idx * 4) + $idx + 1;
                                $iva = $p['iva_porcentaje'] ?? 15; $precio_con_iva = $p['precio_base'] * (1 + ($iva / 100));
                                $tiene_descuento = false; $precio_final = $precio_con_iva; $porcentaje_descuento = 0;
                                $raw_discount = $p['descuento_porcentaje'] ?? 0; $discount_val = ($raw_discount >0 && $raw_discount <= 1) ? $raw_discount * 100 : $raw_discount;
                                if ($discount_val >0) { $hoy_check = date('Y-m-d'); $desde = $p['descuento_desde'] ?? null; $hasta = $p['descuento_hasta'] ?? null;
                                    if ((!$desde || $hoy_check >= $desde) && (!$hasta || $hoy_check <= $hasta)) { $tiene_descuento = true; $porcentaje_descuento = round($discount_val); $precio_final = $precio_con_iva * (1 - ($porcentaje_descuento / 100)); }
                                }
                                $promedio = round($p['promedio_calificacion'], 1); $total_reseñas = (int)$p['total_reseñas']; $total_ventas = isset($p['total_ventas']) ? (int)$p['total_ventas'] : null;
                                $en_wishlist_best = in_array($p['id'], $wishlist_ids);
                            ?><div class="group flex flex-col card-hover bg-gray-50 rounded-xl border border-amber-200/50 overflow-hidden relative cursor-pointer" data-product-id="<?= $p['id'] ?>"><span class="absolute top-3 left-3 z-10 w-7 h-7 bg-amber-500 text-white text-xs font-extrabold rounded-lg flex items-center justify-center shadow-sm">#<?= $rank ?></span><!-- BOTÓN WISHLIST --><button type="button" onclick="toggleWishlist('<?= $p['id'] ?>', this, event)" class="absolute top-3 right-3 z-20 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm hover:scale-110 transition"><i class="<?= $en_wishlist_best ? 'fas text-red-500' : 'far text-slate-400' ?> fa-heart text-lg wishlist-icon"></i></button><div class="relative aspect-square bg-white p-5 flex items-center justify-center overflow-hidden"><?php if ($tiene_descuento): ?><span class="absolute top-3 right-3 bg-red-600 text-white text-xs font-bold px-2.5 py-1 rounded-full discount-pulse">-<?= $porcentaje_descuento ?>%</span><?php endif; ?><?php if (!empty($p['imagen_principal'])): ?><img src="<?= htmlspecialchars($p['imagen_principal']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" class="w-full h-full object-contain img-hover"><?php else: ?><div class="text-gray-300 flex flex-col items-center gap-2"><i class="fas fa-image text-4xl"></i></div><?php endif; ?></div><div class="p-4 flex flex-col flex-grow"><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1"><?= htmlspecialchars($p['marca'] ?? 'General') ?></p><h3 class="font-semibold text-slate-900 text-[15px] mb-2 line-clamp-2 leading-snug group-hover:text-amber-600 transition"><?= htmlspecialchars($p['nombre']) ?></h3><div class="flex items-center gap-1.5 mb-3"><div class="flex text-amber-400 text-xs gap-0.5"><?php 
                                            $estrellas_completas_best = floor($promedio);
                                            $tiene_media_best = ($promedio - $estrellas_completas_best) >= 0.5;
                                            for ($i=1; $i<=5; $i++): 
                                                if ($i <= $estrellas_completas_best): ?><i class="fas fa-star"></i><?php elseif ($i == $estrellas_completas_best + 1 && $tiene_media_best): ?><i class="fas fa-star-half-alt"></i><?php else: ?><i class="far fa-star text-gray-300"></i><?php endif; 
                                            endfor; ?></div><?php if ($total_reseñas >0): ?><span class="text-xs text-gray-500">(<?= $total_reseñas ?>)</span><?php endif; ?><?php if ($total_ventas !== null && $total_ventas >0): ?><span class="text-xs text-amber-600 font-semibold ml-auto"><?= $total_ventas ?> vendidos</span><?php endif; ?></div><div class="mt-auto pt-3 border-t border-gray-100"><?php if ($tiene_descuento): ?><div class="flex items-baseline gap-2 mb-1"><span class="text-xl font-extrabold text-emerald-600">$<?= number_format($precio_final, 2) ?></span><span class="text-sm text-gray-400 line-through">$<?= number_format($precio_con_iva, 2) ?></span></div><p class="text-xs text-emerald-500 font-medium mb-3">Ahorrás $<?= number_format($precio_con_iva - $precio_final, 2) ?></p><?php else: ?><span class="text-xl font-extrabold text-slate-900 mb-1 block">$<?= number_format($precio_final, 2) ?></span><p class="text-xs text-gray-400 mb-3">IVA <?= $iva ?>% incluido</p><?php endif; ?><button type="button" onclick="addToCart('<?= $p['id'] ?>', this, event)" class="w-full py-2.5 rounded-lg font-semibold flex items-center justify-center gap-2 btn-cta text-sm <?= $tiene_descuento ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-amber-500 text-white hover:bg-amber-600' ?>"><i class="fas fa-plus text-xs"></i>Agregar al carrito
                                        </button></div></div></div><?php endforeach; ?></div></div><?php endforeach; ?></div></div><?php if ($total_pages >1): ?><button onclick="moveBest(-1)" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 w-11 h-11 bg-white hover:bg-gray-50 rounded-full flex items-center justify-center shadow-md border border-gray-200 transition z-10"><i class="fas fa-chevron-left text-slate-700 text-sm"></i></button><button onclick="moveBest(1)" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 w-11 h-11 bg-white hover:bg-gray-50 rounded-full flex items-center justify-center shadow-md border border-gray-200 transition z-10"><i class="fas fa-chevron-right text-slate-700 text-sm"></i></button><div class="flex items-center justify-center gap-2 mt-4" id="bestDots"><?php for ($i=0;$i<$total_pages;$i++): ?><button onclick="goToBestPage(<?= $i ?>)" class="w-2.5 h-2.5 rounded-full transition <?= $i===0 ? 'bg-amber-500 scale-125' : 'bg-gray-300 hover:bg-gray-400' ?>" data-dot="<?= $i ?>"></button><?php endfor; ?></div><?php endif; ?></div></div></section><?php endif; ?><?= emxRenderHomeSlot($pdo, $secciones_home, 'despues_mas_vendidos') ?><?= emxRenderHomeSlot($pdo, $secciones_home, 'antes_footer') ?><?php else: ?><!-- ===== VISTA FILTRADA CON SIDEBAR PROFESIONAL ===== --><main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow"><div class="flex items-center justify-between flex-wrap gap-4 mb-6 pb-6 border-b border-gray-200"><div><h2 class="text-2xl font-extrabold text-slate-900 tracking-tight"><?= htmlspecialchars($titulo_filtro) ?></h2><p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($subtitulo_filtro) ?></p></div><div class="flex items-center gap-3"><?php if ($categoria_actual_id): ?><button onclick="toggleFiltros()" id="btn-filtros" class="flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-slate-200 text-slate-700 rounded-xl font-semibold text-sm hover:border-blue-500 hover:text-blue-600 transition"><i class="fas fa-sliders-h"></i><span>Filtros</span><span id="contador-filtros-activos" class="hidden bg-blue-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">0</span></button><?php endif; ?><?php if ($categoria_actual_id): ?><button type="button" onclick="borrarFiltros(event)" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-slate-700 hover:bg-gray-200 font-semibold text-sm transition"><i class="fas fa-times-circle text-xs"></i>Limpiar</button><?php else: ?><a href="index.php" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-slate-700 hover:bg-gray-200 font-semibold text-sm transition"><i class="fas fa-times-circle text-xs"></i>Limpiar</a><?php endif; ?></div></div><div class="mb-6"><span class="text-sm text-gray-600 font-medium"><span id="contador-productos"><?= count($productos) ?></span> productos encontrados</span></div><div class="flex gap-8"><?php if ($categoria_actual_id): ?><aside id="sidebar-desktop" class="filtros-sidebar oculto lg:block"><div class="contenido sticky top-24"><div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5"><div class="flex items-center justify-between mb-5 pb-4 border-b border-gray-100"><h3 class="text-base font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-filter text-blue-600"></i>Filtros</h3><button onclick="borrarFiltros(event)" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">Borrar todo</button></div><div id="contenedor-filtros" class="space-y-5 max-h-[60vh] overflow-y-auto filtros-scroll pr-2"><div class="text-center text-gray-400 py-6"><i class="fas fa-spinner fa-spin text-xl mb-2"></i><p class="text-xs">Cargando filtros...</p></div></div><div class="border-t border-gray-100 pt-5 mt-5"><h4 class="font-bold text-slate-800 mb-3 text-xs uppercase tracking-wide">Calificación</h4><div class="space-y-1"><?php for ($i=5;$i>=1;$i--): ?><label class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition"><input type="radio" name="calificacion_filtro" value="<?= $i ?>" class="filtro-calificacion w-4 h-4 text-blue-600 border-gray-300" onchange="aplicarFiltros()"><div class="flex text-amber-400 text-sm gap-0.5"><?php for ($j=1;$j<=5;$j++): ?><?php if ($j<=$i): ?><i class="fas fa-star"></i><?php else: ?><i class="far fa-star text-gray-300"></i><?php endif; ?><?php endfor; ?></div><span class="text-xs text-gray-500"><?= $i ?> estrella<?= $i === 1 ? "" : "s" ?></span></label><?php endfor; ?></div></div><div class="border-t border-gray-100 pt-5 mt-5"><h4 class="font-bold text-slate-800 mb-3 text-xs uppercase tracking-wide">Rango de Precio</h4><div class="px-1"><input type="range" id="precio-slider" min="0" max="5000" step="50" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" oninput="actualizarPrecio(this.value)"><div class="flex justify-between mt-2 text-xs font-medium text-gray-600"><span id="precio-min-label">$0</span><span id="precio-max-label" class="text-blue-600 font-bold">$5,000</span></div></div></div></div></div></aside><?php endif; ?><?php if ($categoria_actual_id): ?><div id="drawer-overlay" class="drawer-overlay fixed inset-0 z-50 lg:hidden"><div class="absolute inset-0 bg-black/50" onclick="toggleFiltros()"></div><div class="drawer-panel absolute left-0 top-0 h-full w-80 max-w-[85vw] bg-gray-50 shadow-2xl overflow-y-auto"><div class="bg-white rounded-b-2xl shadow-sm border-b border-gray-200 p-5 sticky top-0 z-10"><div class="flex items-center justify-between"><h3 class="text-base font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-filter text-blue-600"></i>Filtros</h3><button onclick="toggleFiltros()" class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-lg hover:bg-gray-200 transition"><i class="fas fa-times text-gray-600"></i></button></div></div><div class="p-5"><div id="contenedor-filtros-movil" class="space-y-5"></div><div class="border-t border-gray-100 pt-5 mt-5"><h4 class="font-bold text-slate-800 mb-3 text-xs uppercase tracking-wide">Calificación</h4><div class="space-y-1" id="calificacion-movil"><?php for ($i=5;$i>=1;$i--): ?><label class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition"><input type="radio" name="calificacion_filtro_movil" value="<?= $i ?>" class="filtro-calificacion w-4 h-4 text-blue-600 border-gray-300" onchange="aplicarFiltros()"><div class="flex text-amber-400 text-sm gap-0.5"><?php for ($j=1;$j<=5;$j++): ?><?php if ($j<=$i): ?><i class="fas fa-star"></i><?php else: ?><i class="far fa-star text-gray-300"></i><?php endif; ?><?php endfor; ?></div><span class="text-xs text-gray-500"><?= $i ?> estrella<?= $i === 1 ? "" : "s" ?></span></label><?php endfor; ?></div></div><div class="border-t border-gray-100 pt-5 mt-5"><h4 class="font-bold text-slate-800 mb-3 text-xs uppercase tracking-wide">Rango de Precio</h4><div class="px-1"><input type="range" id="precio-slider-movil" min="0" max="5000" step="50" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" oninput="actualizarPrecio(this.value)"><div class="flex justify-between mt-2 text-xs font-medium text-gray-600"><span id="precio-min-label-movil">$0</span><span id="precio-max-label-movil" class="text-blue-600 font-bold">$5,000</span></div></div></div><button onclick="borrarFiltros(event)" class="w-full mt-5 py-3 bg-slate-100 text-slate-700 rounded-xl font-semibold text-sm hover:bg-slate-200 transition">Borrar todos los filtros</button></div></div></div><?php endif; ?><div class="productos-grid flex-1"><div id="contenedor-productos" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5"><?php foreach ($productos as $p):
                    $iva = $p['iva_porcentaje'] ?? 15; $precio_con_iva = $p['precio_base'] * (1 + ($iva / 100));
                    $tiene_descuento = false; $precio_final = $precio_con_iva; $porcentaje_descuento = 0;
                    $raw_discount = $p['descuento_porcentaje'] ?? 0; $discount_val = ($raw_discount >0 && $raw_discount <= 1) ? $raw_discount * 100 : $raw_discount;
                    if ($discount_val >0) { $hoy_check = date('Y-m-d'); $desde = $p['descuento_desde'] ?? null; $hasta = $p['descuento_hasta'] ?? null;
                        if ((!$desde || $hoy_check >= $desde) && (!$hasta || $hoy_check <= $hasta)) { $tiene_descuento = true; $porcentaje_descuento = round($discount_val); $precio_final = $precio_con_iva * (1 - ($porcentaje_descuento / 100)); }
                    }
                    $stock_bajo = $p['stock_actual_global'] <= 5 && $p['stock_actual_global'] >0;
                    $promedio = round($p['promedio_calificacion'], 1); $total_reseñas = (int)$p['total_reseñas'];
                    $en_wishlist_filtro = in_array($p['id'], $wishlist_ids);
                ?><div class="group flex flex-col card-hover bg-white rounded-xl border border-gray-200 overflow-hidden cursor-pointer" data-product-id="<?= $p['id'] ?>"><div class="relative aspect-square bg-gray-50 p-5 flex items-center justify-center overflow-hidden"><?php if (!empty($p['imagen_principal'])): ?><img src="<?= htmlspecialchars($p['imagen_principal']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" class="w-full h-full object-contain img-hover"><?php else: ?><div class="text-gray-300 flex flex-col items-center gap-2"><i class="fas fa-image text-4xl"></i></div><?php endif; ?><!-- BOTÓN WISHLIST --><button type="button" onclick="toggleWishlist('<?= $p['id'] ?>', this, event)" class="absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm hover:scale-110 transition z-20"><i class="<?= $en_wishlist_filtro ? 'fas text-red-500' : 'far text-slate-400' ?> fa-heart text-lg wishlist-icon"></i></button><?php if ($tiene_descuento): ?><span class="absolute top-3 left-3 bg-red-600 text-white text-xs font-bold px-2.5 py-1 rounded-full discount-pulse">-<?= $porcentaje_descuento ?>%</span><?php elseif ($stock_bajo): ?><span class="absolute top-3 left-3 bg-amber-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">Últimas unidades</span><?php elseif ($p['stock_actual_global'] == 0): ?><span class="absolute top-3 left-3 bg-gray-400 text-white text-[10px] font-bold px-2 py-1 rounded-full">Agotado</span><?php endif; ?></div><div class="p-4 flex flex-col flex-grow"><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1"><?= htmlspecialchars($p['marca'] ?? 'General') ?></p><h3 class="font-semibold text-slate-900 text-[15px] mb-2 line-clamp-2 leading-snug group-hover:text-blue-600 transition"><?= htmlspecialchars($p['nombre']) ?></h3><div class="flex items-center gap-1.5 mb-3"><div class="flex text-amber-400 text-xs gap-0.5"><?php 
                                $estrellas_completas_filtro = floor($promedio);
                                $tiene_media_filtro = ($promedio - $estrellas_completas_filtro) >= 0.5;
                                for ($i=1; $i<=5; $i++): 
                                    if ($i <= $estrellas_completas_filtro): ?><i class="fas fa-star"></i><?php elseif ($i == $estrellas_completas_filtro + 1 && $tiene_media_filtro): ?><i class="fas fa-star-half-alt"></i><?php else: ?><i class="far fa-star text-gray-300"></i><?php endif; 
                                endfor; ?></div><?php if ($total_reseñas >0): ?><span class="text-xs text-gray-500">(<?= $total_reseñas ?>)</span><?php else: ?><span class="text-xs text-gray-400">Nuevo</span><?php endif; ?></div><div class="mt-auto pt-3 border-t border-gray-100"><?php if ($tiene_descuento): ?><div class="flex items-baseline gap-2 mb-1"><span class="text-xl font-extrabold text-emerald-600">$<?= number_format($precio_final, 2) ?></span><span class="text-sm text-gray-400 line-through">$<?= number_format($precio_con_iva, 2) ?></span></div><p class="text-xs text-emerald-500 font-medium mb-3">Ahorrás $<?= number_format($precio_con_iva - $precio_final, 2) ?></p><?php else: ?><span class="text-xl font-extrabold text-slate-900 mb-1 block">$<?= number_format($precio_final, 2) ?></span><p class="text-xs text-gray-400 mb-3">IVA <?= $iva ?>% incluido</p><?php endif; ?><button type="button" onclick="addToCart('<?= $p['id'] ?>', this, event)" class="w-full py-2.5 rounded-lg font-semibold flex items-center justify-center gap-2 btn-cta text-sm <?= $p['stock_actual_global'] >0 ? ($tiene_descuento ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-slate-900 text-white hover:bg-blue-700') : 'bg-gray-200 text-gray-500 cursor-not-allowed' ?>"><i class="fas fa-plus text-xs"></i><?= $p['stock_actual_global'] >0 ? 'Agregar al carrito' : 'Sin stock' ?></button></div></div></div><?php endforeach; ?></div></div></div></main><?php endif; ?><?php require EMX_VIEWS_PATH . '/components/footer.php'; ?><!-- JAVASCRIPT COMPLETO Y CORREGIDO --><script>
const csrfToken = <?= json_encode(emxCsrfToken()) ?>;const productosData = <?= $productos_json ?>;
    const productosBestData = <?= $productos_best_json ?>;
    const categoriaIdFiltro = <?= $categoria_id_json ?>;
    let wishlistIds = <?= $wishlist_ids_json ?>;
    const bestTotalPages = <?= $best_chunks_count ?>;

    // ==========================================
    // CARRUSELES
    // ==========================================
    const carouselPositions = {};
    function moveCarousel(id, dir) {
        const c = document.getElementById('carousel-' + id);
        if (!c) return;
        const total = c.children.length;
        if (!carouselPositions[id]) carouselPositions[id] = 0;
        carouselPositions[id] += dir;
        if (carouselPositions[id] < 0) carouselPositions[id] = total - 1;
        if (carouselPositions[id] >= total) carouselPositions[id] = 0;
        c.style.transform = 'translateX(-' + (carouselPositions[id] * 100) + '%)';
    }
    setInterval(function() { 
        document.querySelectorAll('[id^="carousel-"]').forEach(function(c) { 
            moveCarousel(c.id.replace('carousel-',''), 1); 
        }); 
    }, 5000);

    let bestPage = 0;
    function moveBest(dir) {
        bestPage += dir;
        if (bestPage < 0) bestPage = bestTotalPages - 1;
        if (bestPage >= bestTotalPages) bestPage = 0;
        updateBestCarousel();
    }
    function goToBestPage(page) { bestPage = page; updateBestCarousel(); }
    function updateBestCarousel() {
        const inner = document.getElementById('bestCarousel');
        if (!inner) return;
        inner.style.transform = 'translateX(-' + (bestPage * 100) + '%)';
        document.querySelectorAll('#bestDots button').forEach(function(dot, i) {
            dot.className = i === bestPage ? 'w-2.5 h-2.5 rounded-full transition bg-amber-500 scale-125' : 'w-2.5 h-2.5 rounded-full transition bg-gray-300 hover:bg-gray-400';
        });
    }
    setInterval(function() { if (bestTotalPages >1) moveBest(1); }, 6000);

    // ==========================================
    // FILTROS
    // ==========================================
    let filtrosVisibles = false;
    function toggleFiltros() {
        const sidebar = document.getElementById('sidebar-desktop');
        const drawer = document.getElementById('drawer-overlay');
        const grid = document.querySelector('.productos-grid');
        if (window.innerWidth >= 1024) {
            filtrosVisibles = !filtrosVisibles;
            if (filtrosVisibles) {
                sidebar.classList.remove('oculto');
                grid.classList.remove('lg:grid-cols-4');
                grid.classList.add('lg:grid-cols-3');
            } else {
                sidebar.classList.add('oculto');
                grid.classList.remove('lg:grid-cols-3');
                grid.classList.add('lg:grid-cols-4');
            }
        } else {
            drawer.classList.toggle('activo');
            document.body.style.overflow = drawer.classList.contains('activo') ? 'hidden' : '';
        }
    }

    <?php if ($filtro_activo && $categoria_actual_id): ?>let filtrosActivos = {};
    let calificacionMin = 0; // Filtro exacto: 1, 2, 3, 4 o 5 estrellas
    let precioMinGlobal = 0;
    let precioMaxGlobal = 5000;
    let precioMaximoReal = 5000;

    document.addEventListener('DOMContentLoaded', function() { cargarFiltrosDinamicos(categoriaIdFiltro); });

    async function cargarFiltrosDinamicos(catId) {
        const contenedor = document.getElementById('contenedor-filtros');
        const contenedorMovil = document.getElementById('contenedor-filtros-movil');
        try {
            const response = await fetch('api_filtros.php?categoria_id=' + catId);
            const data = await response.json();
            if (data.success && data.filtros) {
                const html = renderizarFiltrosHTML(data.filtros, data.min_price || 0, data.max_price || 5000);
                if (contenedor) contenedor.innerHTML = html;
                if (contenedorMovil) contenedorMovil.innerHTML = html;
                actualizarSliders(data.min_price || 0, data.max_price || 5000);
            }
        } catch (error) { console.error('Error cargando filtros:', error); }
    }

    function renderizarFiltrosHTML(filtros, minP, maxP) {
        let html = '';
        filtros.forEach(function(filtro, index) {
            const campoId = filtro.label.toLowerCase().replace(/\s+/g, '_').replace(/[^\w]/g, '');
            html += '<div class="border-b border-gray-100 pb-4 last:border-0">';
            html += '<h4 class="font-bold text-slate-800 mb-3 text-xs uppercase tracking-wide flex justify-between items-center cursor-pointer" onclick="toggleFiltroGrupo(' + index + ')">';
            html += filtro.label + ' <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform" id="icon-' + index + '"></i>';
            html += '</h4>';
            html += '<div id="filtro-' + index + '" class="space-y-1">';
            filtro.valores.forEach(function(valor) {
                const valorEscapado = valor.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                html += '<label class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition">';
                html += '<input type="checkbox" value="' + valorEscapado + '" class="filtro-especificacion w-4 h-4 text-blue-600 rounded border-gray-300" data-campo="' + campoId + '" onchange="aplicarFiltros()">';
                html += '<span class="text-xs text-gray-700">' + valorEscapado + '</span>';
                html += '</label>';
            });
            html += '</div></div>';
        });
        return html;
    }

    function toggleFiltroGrupo(index) {
        const elemento = document.getElementById('filtro-' + index);
        const icono = document.getElementById('icon-' + index);
        if (elemento.classList.contains('hidden')) { 
            elemento.classList.remove('hidden'); 
            icono.style.transform = 'rotate(0deg)'; 
        } else { 
            elemento.classList.add('hidden'); 
            icono.style.transform = 'rotate(-90deg)'; 
        }
    }

    function actualizarSliders(minP, maxP) {
        precioMinGlobal = Math.floor(minP); 
        precioMaxGlobal = Math.ceil(maxP); 
        precioMaximoReal = Math.ceil(maxP);
        ['precio-slider', 'precio-slider-movil'].forEach(function(id) {
            const slider = document.getElementById(id);
            if (slider) { 
                slider.min = precioMinGlobal; 
                slider.max = precioMaxGlobal; 
                slider.value = precioMaxGlobal; 
                slider.step = Math.max(10, Math.floor((precioMaxGlobal - precioMinGlobal) / 50)); 
            }
        });
        ['precio-min-label', 'precio-min-label-movil'].forEach(function(id) { 
            const el = document.getElementById(id); 
            if (el) el.textContent = '$' + Number(precioMinGlobal).toLocaleString(); 
        });
        ['precio-max-label', 'precio-max-label-movil'].forEach(function(id) { 
            const el = document.getElementById(id); 
            if (el) el.textContent = '$' + Number(precioMaxGlobal).toLocaleString(); 
        });
    }

    async function aplicarFiltros() {
        const checkboxes = document.querySelectorAll('.filtro-especificacion:checked');
        filtrosActivos = {};
        checkboxes.forEach(function(cb) { 
            const campo = cb.dataset.campo; 
            if (!filtrosActivos[campo]) filtrosActivos[campo] = []; 
            filtrosActivos[campo].push(cb.value); 
        });
        const calRadio = document.querySelector('input[name="calificacion_filtro"]:checked') || document.querySelector('input[name="calificacion_filtro_movil"]:checked');
        calificacionMin = calRadio ? parseFloat(calRadio.value) : 0;
        const totalFiltros = Object.keys(filtrosActivos).length + (calificacionMin >0 ? 1 : 0);
        const badge = document.getElementById('contador-filtros-activos');
        if (badge) { 
            if (totalFiltros >0) { 
                badge.textContent = totalFiltros; 
                badge.classList.remove('hidden'); 
            } else { 
                badge.classList.add('hidden'); 
            } 
        }
        await filtrarProductos();
    }

    async function filtrarProductos() {
        const contenedor = document.getElementById('contenedor-productos');
        const contador = document.getElementById('contador-productos');
        try {
            contenedor.innerHTML = '<div class="col-span-full text-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-blue-600"></i><p class="text-gray-500 mt-2 text-sm">Buscando productos...</p></div>';
            const params = new URLSearchParams({ 
                categoria_id: categoriaIdFiltro, 
                filtros: JSON.stringify(filtrosActivos), 
                calificacion_exacta: calificacionMin, 
                precio_min: precioMinGlobal, 
                precio_max: precioMaxGlobal 
            });
            const response = await fetch('api_filtrar_productos.php?' + params);
            const data = await response.json();
            if (data.success) { 
                renderizarProductos(data.productos); 
                if (contador) contador.textContent = data.total; 
            } else { 
                contenedor.innerHTML = '<div class="col-span-full text-center py-12 text-red-500">Error en la respuesta del servidor</div>'; 
            }
        } catch (error) { 
            contenedor.innerHTML = '<div class="col-span-full text-center py-12 text-red-500">Error de conexión</div>'; 
        }
    }

    function renderizarProductos(productos) {
        const contenedor = document.getElementById('contenedor-productos');
        contenedor.innerHTML = '';
        if (!productos || productos.length === 0) {
            contenedor.innerHTML = '<div class="col-span-full text-center py-12 bg-white rounded-xl border border-gray-100"><i class="fas fa-search text-4xl text-gray-300 mb-4"></i><p class="text-gray-500 font-medium">No se encontraron productos</p><button onclick="borrarFiltros(event)" class="mt-4 text-blue-600 font-semibold hover:underline text-sm">Limpiar filtros</button></div>';
            return;
        }
        productos.forEach(function(p) {
            const iva = parseFloat(p.iva_porcentaje) || 15;
            const precioConIva = parseFloat(p.precio_base) * (1 + (iva / 100));
            let tieneDescuento = false, precioFinal = precioConIva, porcentajeDescuento = 0;
            const rawDiscount = parseFloat(p.descuento_porcentaje) || 0;
            const discountVal = (rawDiscount >0 && rawDiscount <= 1) ? rawDiscount * 100 : rawDiscount;
            if (discountVal >0) {
                const hoyCheck = new Date().toISOString().split('T')[0];
                if ((!p.descuento_desde || hoyCheck >= p.descuento_desde) && (!p.descuento_hasta || hoyCheck <= p.descuento_hasta)) {
                    tieneDescuento = true; 
                    porcentajeDescuento = Math.round(discountVal); 
                    precioFinal = precioConIva * (1 - (porcentajeDescuento / 100));
                }
            }
            const stockBajo = (parseInt(p.stock_actual_global) || 0) <= 5 && (parseInt(p.stock_actual_global) || 0) >0;
            const promedio = Math.round((parseFloat(p.promedio_calificacion) || 0) * 10) / 10;
            const totalResenas = parseInt(p.total_reseñas) || 0;
            
            let estrellasHtml = '';
            const estrellasCompletas = Math.floor(promedio);
            const tieneMedia = (promedio - estrellasCompletas) >= 0.5;
            for (let i = 1; i <= 5; i++) { 
                if (i <= estrellasCompletas) estrellasHtml += '<i class="fas fa-star"></i>'; 
                else if (i === estrellasCompletas + 1 && tieneMedia) estrellasHtml += '<i class="fas fa-star-half-alt"></i>'; 
                else estrellasHtml += '<i class="far fa-star text-gray-300"></i>'; 
            }
            
            const nombreEscapado = (p.nombre || '').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const marcaEscapada = (p.marca || 'General').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const imagenSrc = p.imagen_principal || '';
            const stockGlobal = parseInt(p.stock_actual_global) || 0;
            const idEscapado = String(p.id).replace(/'/g, "\\'");
            const enWishlist = wishlistIds.includes(p.id);
            const heartClass = enWishlist ? 'fas text-red-500' : 'far text-slate-400';
            
            const div = document.createElement('div');
            div.className = 'group flex flex-col card-hover bg-white rounded-xl border border-gray-200 overflow-hidden cursor-pointer';
            div.setAttribute('data-product-id', p.id);
            
            let html = '<div class="relative aspect-square bg-gray-50 p-5 flex items-center justify-center overflow-hidden">';
            if (imagenSrc) {
                html += '<img src="' + imagenSrc + '" alt="' + nombreEscapado + '" class="w-full h-full object-contain img-hover">';
            } else {
                html += '<div class="text-gray-300 flex flex-col items-center gap-2"><i class="fas fa-image text-4xl"></i></div>';
            }
            
            // Botón Wishlist en JS
            html += '<button type="button" onclick="toggleWishlist(\'' + idEscapado + '\', this, event)" class="absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm hover:scale-110 transition z-20">';
            html += '<i class="' + heartClass + ' fa-heart text-lg wishlist-icon"></i>';
            html += '</button>';

            if (tieneDescuento) {
                html += '<span class="absolute top-3 left-3 bg-red-600 text-white text-xs font-bold px-2.5 py-1 rounded-full discount-pulse">-' + porcentajeDescuento + '%</span>';
            }
            if (stockBajo) {
                html += '<span class="absolute top-3 left-3 bg-amber-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">Últimas unidades</span>';
            }
            if (stockGlobal === 0) {
                html += '<span class="absolute top-3 left-3 bg-gray-400 text-white text-[10px] font-bold px-2 py-1 rounded-full">Agotado</span>';
            }
            html += '</div>';
            
            html += '<div class="p-4 flex flex-col flex-grow">';
            html += '<p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">' + marcaEscapada + '</p>';
            html += '<h3 class="font-semibold text-slate-900 text-[15px] mb-2 line-clamp-2 leading-snug group-hover:text-blue-600 transition">' + nombreEscapado + '</h3>';
            html += '<div class="flex items-center gap-1.5 mb-3">';
            html += '<div class="flex text-amber-400 text-xs gap-0.5">' + estrellasHtml + '</div>';
            if (totalResenas >0) {
                html += '<span class="text-xs text-gray-500">(' + totalResenas + ')</span>';
            } else {
                html += '<span class="text-xs text-gray-400">Nuevo</span>';
            }
            html += '</div>';
            
            html += '<div class="mt-auto pt-3 border-t border-gray-100">';
            if (tieneDescuento) {
                html += '<div class="flex items-baseline gap-2 mb-1"><span class="text-xl font-extrabold text-emerald-600">$' + precioFinal.toFixed(2) + '</span><span class="text-sm text-gray-400 line-through">$' + precioConIva.toFixed(2) + '</span></div>';
                html += '<p class="text-xs text-emerald-500 font-medium mb-3">Ahorrás $' + (precioConIva - precioFinal).toFixed(2) + '</p>';
            } else {
                html += '<span class="text-xl font-extrabold text-slate-900 mb-1 block">$' + precioFinal.toFixed(2) + '</span>';
                html += '<p class="text-xs text-gray-400 mb-3">IVA ' + iva + '% incluido</p>';
            }
            
            const btnClass = stockGlobal >0 ? (tieneDescuento ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-slate-900 text-white hover:bg-blue-700') : 'bg-gray-200 text-gray-500 cursor-not-allowed';
            const btnText = stockGlobal >0 ? 'Agregar al carrito' : 'Sin stock';
            
            html += '<button type="button" onclick="addToCart(\'' + idEscapado + '\', this, event)" class="w-full py-2.5 rounded-lg font-semibold flex items-center justify-center gap-2 btn-cta text-sm ' + btnClass + '">';
            html += '<i class="fas fa-plus text-xs"></i>' + btnText;
            html += '</button>';
            html += '</div></div>';
            
            div.innerHTML = html;
            contenedor.appendChild(div);
        });
    }

    function borrarFiltros(event) {
        if (event) { event.preventDefault(); event.stopPropagation(); }
        document.querySelectorAll('.filtro-especificacion').forEach(function(cb) { cb.checked = false; });
        document.querySelectorAll('input[name="calificacion_filtro"], input[name="calificacion_filtro_movil"]').forEach(function(rb) { rb.checked = false; });
        ['precio-slider', 'precio-slider-movil'].forEach(function(id) { 
            const slider = document.getElementById(id); 
            if (slider) slider.value = precioMaximoReal; 
        });
        ['precio-max-label', 'precio-max-label-movil'].forEach(function(id) { 
            const el = document.getElementById(id); 
            if (el) el.textContent = '$' + Number(precioMaximoReal).toLocaleString(); 
        });
        filtrosActivos = {}; 
        calificacionMin = 0; 
        precioMaxGlobal = precioMaximoReal;
        const badge = document.getElementById('contador-filtros-activos'); 
        if (badge) badge.classList.add('hidden');
        aplicarFiltros();
    }

    function actualizarPrecio(valor) {
        precioMaxGlobal = parseInt(valor);
        ['precio-max-label', 'precio-max-label-movil'].forEach(function(id) { 
            const el = document.getElementById(id); 
            if (el) el.textContent = '$' + Number(precioMaxGlobal).toLocaleString(); 
        });
        aplicarFiltros();
    }
    <?php endif; ?>// ==========================================
    // NAVEGACIÓN A PRODUCTO.PHP
    // ==========================================
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-cta') || e.target.closest('[onclick*="toggleWishlist"]');
        if (btn) return; 

        const card = e.target.closest('[data-product-id]');
        if (card) {
            window.location.href = 'producto.php?id=' + card.getAttribute('data-product-id');
        }
    });

    // ==========================================
    // AGREGAR AL CARRITO CON ANIMACIÓN
    // ==========================================
    function addToCart(productId, btnElement, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const card = btnElement.closest('[data-product-id]') || btnElement.closest('.group');
        const imgEl = card ? card.querySelector('img') : null;
        const productName = card ? (card.querySelector('h3')?.textContent?.trim() || 'Producto') : 'Producto';
        const productImage = imgEl ? imgEl.src : '';

        if (imgEl) flyToCart(imgEl);

        const originalHTML = btnElement.innerHTML;
        const originalClassName = btnElement.className;
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Agregando...';
        btnElement.disabled = true;

        fetch('add_to_cart.php?id=' + productId)
            .then(function(response) {
                return response.json().catch(function() {
                    return { success: false, message: 'Respuesta inválida del servidor' };
                });
            })
            .then(function(data) {
                if (data.requires_login) {
                    showToast(data.message || 'Debes iniciar sesión para agregar productos al carrito.', 'info');
                    btnElement.innerHTML = originalHTML;
                    btnElement.className = originalClassName;
                    btnElement.disabled = false;
                    setTimeout(function() {
                        window.location.href = data.login_url || 'auth.php?action=login&msg=debes_iniciar_sesion';
                    }, 650);
                    return;
                }

                if (data.success) {
                    updateCartBadge(data.total_items || 1);
                    setTimeout(function() {
                        showToast(data.message || '¡Producto agregado al carrito!', 'success', data.imagen || productImage, data.nombre || productName);
                    }, 550);

                    btnElement.innerHTML = '<i class="fas fa-check"></i>Agregado';
                    btnElement.classList.add('bg-green-600', 'text-white');
                    setTimeout(function() {
                        btnElement.innerHTML = originalHTML;
                        btnElement.className = originalClassName;
                        btnElement.disabled = false;
                    }, 1500);
                } else {
                    showToast(data.message || 'No se pudo agregar el producto.', 'error');
                    btnElement.innerHTML = originalHTML;
                    btnElement.className = originalClassName;
                    btnElement.disabled = false;
                }
            })
            .catch(function(error) {
                console.error('Error addToCart:', error);
                showToast('No se pudo conectar con el carrito. Intenta nuevamente.', 'error');
                btnElement.innerHTML = originalHTML;
                btnElement.className = originalClassName;
                btnElement.disabled = false;
            });
        return false;
    }

    // ==========================================
    // WISHLIST (FAVORITOS) CON ANIMACIÓN
    // ==========================================
    function toggleWishlist(productId, btn, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const icon = btn.querySelector('.wishlist-icon');
        const isFilled = icon.classList.contains('fas');

        // Animación de latido
        icon.style.transform = 'scale(1.4)';
        setTimeout(() =>{ icon.style.transform = 'scale(1)'; }, 200);

        // Optimistic UI update
        if (isFilled) {
            icon.classList.remove('fas', 'text-red-500');
            icon.classList.add('far', 'text-slate-400');
        } else {
            icon.classList.remove('far', 'text-slate-400');
            icon.classList.add('fas', 'text-red-500');
        }

        const formData = new FormData();
        formData.append('producto_id', productId);
        formData.append('csrf_token', csrfToken);

        fetch('api_wishlist.php', {
            method: 'POST',
            body: formData
        })
        .then(res =>res.json())
        .then(data =>{
            if (data.success) {
                showToast(data.message, data.in_wishlist ? 'success' : 'info');
                if (data.in_wishlist && !wishlistIds.includes(productId)) {
                    wishlistIds.push(productId);
                } else if (!data.in_wishlist) {
                    const index = wishlistIds.indexOf(productId);
                    if (index >-1) wishlistIds.splice(index, 1);
                }
            } else {
                // Revertir si falla
                if (isFilled) {
                    icon.classList.remove('far', 'text-slate-400');
                    icon.classList.add('fas', 'text-red-500');
                } else {
                    icon.classList.remove('fas', 'text-red-500');
                    icon.classList.add('far', 'text-slate-400');
                }
                showToast(data.message || 'Error', 'error');
            }
        })
        .catch(err =>{
            console.error(err);
            // Revertir
            if (isFilled) {
                icon.classList.remove('far', 'text-slate-400');
                icon.classList.add('fas', 'text-red-500');
            } else {
                icon.classList.remove('fas', 'text-red-500');
                icon.classList.add('far', 'text-slate-400');
            }
            showToast('Error de conexión', 'error');
        });
    }

    // ==========================================
    // ANIMACIÓN DE VUELO DE IMAGEN
    // ==========================================
    function flyToCart(imgEl) {
        const cartContainer = document.getElementById('cart-container');
        if (!cartContainer) return;

        const imgRect = imgEl.getBoundingClientRect();
        const cartRect = cartContainer.getBoundingClientRect();
        if (imgRect.width === 0 || imgRect.height === 0) return;

        const clone = imgEl.cloneNode(true);
        clone.style.position = 'fixed';
        clone.style.left = imgRect.left + 'px';
        clone.style.top = imgRect.top + 'px';
        clone.style.width = imgRect.width + 'px';
        clone.style.height = imgRect.height + 'px';
        clone.style.objectFit = 'cover';
        clone.style.borderRadius = '8px';
        clone.style.boxShadow = '0 10px 25px rgba(0,0,0,0.3)';
        clone.style.zIndex = '100000';
        clone.style.pointerEvents = 'none';
        clone.style.transition = 'none';
        
        document.body.appendChild(clone);
        void clone.offsetWidth;

        clone.style.transition = 'all 0.55s cubic-bezier(0.34, 1.56, 0.64, 1)';
        clone.style.left = (cartRect.left + cartRect.width / 2 - 13) + 'px';
        clone.style.top = (cartRect.top + cartRect.height / 2 - 13) + 'px';
        clone.style.width = '26px';
        clone.style.height = '26px';
        clone.style.opacity = '0';
        clone.style.transform = 'scale(0.3) rotate(25deg)';
        clone.style.borderRadius = '50%';

        setTimeout(function() {
            if (clone.parentNode) clone.parentNode.removeChild(clone);
            cartContainer.style.transition = 'transform 0.18s cubic-bezier(0.34, 1.56, 0.64, 1)';
            cartContainer.style.transform = 'scale(1.25) rotate(-6deg)';
            setTimeout(function() { cartContainer.style.transform = 'scale(1) rotate(0)'; }, 180);
        }, 550);
    }

    // ==========================================
    // ACTUALIZAR BADGE DEL CARRITO
    // ==========================================
    function updateCartBadge(totalItems) {
        let cartBadge = document.getElementById('cart-badge');
        if (cartBadge) {
            cartBadge.textContent = totalItems;
            cartBadge.style.transform = 'scale(1.4)';
            setTimeout(function() { cartBadge.style.transform = 'scale(1)'; }, 250);
        } else {
            const cartIcon = document.querySelector('.fa-shopping-bag');
            if (cartIcon) {
                const badge = document.createElement('span');
                badge.id = 'cart-badge';
                badge.className = 'absolute -top-1 -right-1 min-w-[20px] h-[20px] bg-blue-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center transition-transform duration-200';
                badge.textContent = totalItems;
                cartIcon.parentElement.style.position = 'relative';
                cartIcon.parentElement.appendChild(badge);
            }
        }
    }

    // ==========================================
    // TOAST NOTIFICATION
    // ==========================================
    function showToast(message, type, imagen, nombre) {
        const existingToast = document.querySelector('.custom-toast');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        if (type === 'success') {
            toast.className = 'custom-toast fixed bottom-5 right-5 bg-white text-slate-800 pl-3 pr-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3 z-[99999] toast-animation border border-gray-100';
            let innerHTML = '';
            if (imagen) {
                innerHTML += '<img src="' + imagen + '" alt="' + (nombre || '') + '" class="w-14 h-14 object-contain rounded-lg bg-gray-50 border border-gray-100 flex-shrink-0">';
            }
            innerHTML += '<div class="flex flex-col">';
            if (nombre) {
                innerHTML += '<p class="text-xs text-gray-500 truncate max-w-[200px] mb-1">' + nombre + '</p>';
            }
            innerHTML += '<p class="font-bold text-sm flex items-center gap-1.5 text-emerald-600"><i class="fas fa-check-circle"></i>' + message + '</p>';
            innerHTML += '</div>';
            toast.innerHTML = innerHTML;
        } else if (type === 'info') {
            toast.className = 'custom-toast fixed bottom-5 right-5 bg-blue-600 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 z-[99999] toast-animation';
            toast.innerHTML = '<i class="fas fa-info-circle text-lg"></i><span class="font-medium">' + message + '</span>';
        } else {
            toast.className = 'custom-toast fixed bottom-5 right-5 bg-red-600 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 z-[99999] toast-animation';
            toast.innerHTML = '<i class="fas fa-exclamation-circle text-lg"></i><span class="font-medium">' + message + '</span>';
        }
        
        document.body.appendChild(toast);
        setTimeout(function() {
            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(function() { if (toast.parentNode) toast.remove(); }, 300);
        }, 3000);
    }
</script><script src="assets/emx_modales.js"></script></body></html>