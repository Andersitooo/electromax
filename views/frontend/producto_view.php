<?php
/**
 * Vista separada de `producto.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `producto.php`.
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
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= htmlspecialchars($producto['nombre'] ?? 'Producto') ?> | ElectroMax</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"><style>* { font-family: 'Inter', sans-serif; }
        body { background-color: #f8fafc; color: #0f172a; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        .gallery-arrow { 
            position: absolute; top: 50%; transform: translateY(-50%); 
            background: white; border: none; width: 44px; height: 44px; 
            border-radius: 50%; cursor: pointer; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; opacity: 0;
        }
        .gallery-container:hover .gallery-arrow { opacity: 1; }
        .gallery-arrow:hover { background: #f8fafc; transform: translateY(-50%) scale(1.05); }
        .gallery-arrow.prev { left: 16px; }
        .gallery-arrow.next { right: 16px; }
        
        .img-thumb { cursor: pointer; transition: all 0.2s; border: 2px solid transparent; opacity: 0.7; }
        .img-thumb:hover { opacity: 1; border-color: #cbd5e1; }
        .img-thumb.active { opacity: 1; border-color: #0f172a; }
        
        .tab-active { border-bottom: 2px solid #0f172a; color: #0f172a; font-weight: 600; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        .stock-low { color: #ea580c; font-weight: 600; }
        .stock-ok { color: #16a34a; font-weight: 600; }
        
        .btn-primary { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; font-weight: 600; transition: all 0.2s; }
        .btn-primary:hover { background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%); transform: translateY(-1px); box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4); }
        
        .swiper { overflow: visible !important; padding-bottom: 20px; }
        .swiper-button-next, .swiper-button-prev { 
            background: white; border: 1px solid #e2e8f0; 
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .swiper-button-next:after, .swiper-button-prev:after { font-size: 14px; font-weight: 600; color: #0f172a; }
        
        .modal-overlay {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
        }
        .modal-content {
            animation: modalSlide 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes modalSlide {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        
        .spec-card {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .spec-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }
        .spec-icon {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            flex-shrink: 0;
        }

        /* ANIMACIONES CHÉVERES PARA WISHLIST Y CARRITO */
        @keyframes heartPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.4); }
            100% { transform: scale(1); }
        }
        .heart-anim {
            animation: heartPop 0.4s ease-in-out;
        }
        .toast-animation { animation: slideInUp 0.3s ease-out forwards; }
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style></head><body class="flex flex-col min-h-screen"><?php require EMX_VIEWS_PATH . '/components/navbar.php'; ?><?php if (!$producto): ?><div class="flex-grow flex items-center justify-center py-20"><div class="text-center"><div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6"><i class="fas fa-box-open text-4xl text-slate-400"></i></div><h2 class="text-2xl font-bold text-slate-900 mb-2">Producto no encontrado</h2><a href="index.php" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white rounded-xl font-medium hover:bg-slate-800 transition mt-4"><i class="fas fa-arrow-left"></i>Volver al inicio
                </a></div></div><?php else: ?><!-- Breadcrumb --><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"><nav class="flex items-center gap-2 text-sm text-slate-500"><a href="index.php" class="hover:text-blue-600 transition">Inicio</a><i class="fas fa-chevron-right text-xs"></i><a href="index.php?categoria=<?= htmlspecialchars($producto['categoria_slug']) ?>" class="hover:text-blue-600 transition"><?= htmlspecialchars($producto['categoria']) ?></a><i class="fas fa-chevron-right text-xs"></i><span class="text-slate-900 font-medium truncate"><?= htmlspecialchars($producto['nombre']) ?></span></nav></div><!-- Producto Principal --><main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16"><div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 mb-12"><!-- Galería --><div class="space-y-4"><div class="gallery-container relative bg-white rounded-2xl overflow-hidden border border-slate-200 aspect-square flex items-center justify-center p-8 shadow-sm"><?php if (!empty($imagenes)): ?><img src="<?= htmlspecialchars($imagenes[0]) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="w-full h-full object-contain" id="main-image"><?php if (count($imagenes) >1): ?><button type="button" onclick="changeImage(-1)" class="gallery-arrow prev"><i class="fas fa-chevron-left text-slate-700"></i></button><button type="button" onclick="changeImage(1)" class="gallery-arrow next"><i class="fas fa-chevron-right text-slate-700"></i></button><?php endif; ?><?php else: ?><div class="text-slate-300"><i class="fas fa-image text-6xl"></i></div><?php endif; ?><?php if ($tiene_descuento): ?><span class="absolute top-4 left-4 bg-red-500 text-white text-sm font-bold px-3 py-1.5 rounded-full shadow-lg">-<?= $porcentaje_descuento ?>%</span><?php endif; ?></div><?php if (count($imagenes) >1): ?><div class="flex gap-3 overflow-x-auto no-scrollbar"><?php foreach ($imagenes as $index =>$img): ?><button type="button" onclick="setImage(<?= $index ?>)" class="img-thumb flex-shrink-0 w-20 h-20 bg-white rounded-xl overflow-hidden border border-slate-200 p-2 <?= $index === 0 ? 'active' : '' ?>"><img src="<?= htmlspecialchars($img) ?>" class="w-full h-full object-contain"></button><?php endforeach; ?></div><?php endif; ?></div><!-- Info Producto --><div class="flex flex-col"><div class="mb-4"><p class="text-sm text-blue-600 font-semibold mb-2 uppercase tracking-wide"><?= htmlspecialchars($producto['marca'] ?? 'General') ?></p><h1 class="text-2xl lg:text-3xl font-extrabold text-slate-900 mb-3 leading-tight"><?= htmlspecialchars($producto['nombre']) ?></h1><?php if ($avg_rating >0): ?><a href="#tab-reviews" onclick="switchTab('reviews')" class="inline-flex items-center gap-2 mb-4 hover:opacity-80 transition"><div class="flex text-yellow-400 text-sm"><?php 
                                    $estrellas_completas = floor($avg_rating);
                                    $tiene_media = ($avg_rating - $estrellas_completas) >= 0.5;
                                    for($i=1; $i<=5; $i++): 
                                        if ($i <= $estrellas_completas): ?><i class="fas fa-star"></i><?php elseif ($i == $estrellas_completas + 1 && $tiene_media): ?><i class="fas fa-star-half-alt"></i><?php else: ?><i class="far fa-star text-slate-300"></i><?php endif; 
                                    endfor; ?></div><span class="text-sm text-slate-600 font-medium"><?= $avg_rating ?>(<?= $total_reviews ?>reseñas)</span></a><?php endif; ?></div><!-- Precio --><div class="mb-6 p-4 bg-slate-50 rounded-xl border border-slate-100"><?php if ($tiene_descuento_plan): ?><div class="mb-1"><span class="text-3xl font-extrabold text-slate-900">$<?= number_format($precio_miembro, 2) ?></span><span class="text-lg text-slate-400 line-through ml-2">$<?= number_format($precio_final, 2) ?></span></div><p class="text-sm text-amber-600 font-semibold flex items-center gap-1"><i class="fas fa-crown text-xs"></i>Precio especial miembro - Ahorras $<?= number_format($ahorro_miembro, 2) ?></p><?php else: ?><?php if ($tiene_descuento): ?><div class="mb-1"><span class="text-3xl font-extrabold text-slate-900">$<?= number_format($precio_final, 2) ?></span><span class="text-lg text-slate-400 line-through ml-2">$<?= number_format($precio_con_iva, 2) ?></span></div><?php else: ?><span class="text-3xl font-extrabold text-slate-900">$<?= number_format($precio_final, 2) ?></span><?php endif; ?><?php endif; ?></div><!-- Stock --><div class="mb-6"><?php if ($agotado): ?><span class="inline-flex items-center gap-2 text-red-600 font-semibold bg-red-50 px-3 py-1.5 rounded-lg"><i class="fas fa-times-circle"></i>Agotado
                            </span><?php elseif ($stock_bajo): ?><span class="inline-flex items-center gap-2 stock-ok bg-amber-50 text-amber-700 px-3 py-1.5 rounded-lg"><i class="fas fa-exclamation-circle"></i>¡Últimas <?= $producto['stock_actual_global'] ?>unidades!
                            </span><?php else: ?><span class="inline-flex items-center gap-2 stock-ok bg-emerald-50 px-3 py-1.5 rounded-lg"><i class="fas fa-check-circle"></i>Disponible (<?= $producto['stock_actual_global'] ?>unidades)
                            </span><?php endif; ?></div><!-- Descripción corta --><?php if (!empty($producto['descripcion_corta'])): ?><div class="mb-8 text-slate-600 leading-relaxed border-l-4 border-blue-500 pl-4"><?= nl2br(htmlspecialchars($producto['descripcion_corta'])) ?></div><?php endif; ?><!-- Cantidad y Carrito --><div class="mb-6"><?php if ($agotado): ?><div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 font-semibold"><i class="fas fa-truck-ramp-box mr-2"></i>No hay stock inmediato. Puedes pedirlo y el carrito calculará una estimación con proveedores.
                            </div><?php endif; ?><div class="flex gap-3 mb-4"><div class="flex items-center bg-white border border-slate-200 rounded-xl shadow-sm"><button type="button" onclick="updateQty(-1)" class="px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-l-xl transition font-bold text-lg">-</button><input type="number" id="quantity-input" value="1" min="1" class="w-20 text-center border-x border-slate-200 py-3 focus:outline-none font-semibold text-slate-900"><button type="button" onclick="updateQty(1)" class="px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-r-xl transition font-bold text-lg">+</button></div><button type="button" onclick="agregarAlCarrito(event)" class="flex-1 btn-primary py-3.5 px-6 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-blue-600/20"><i class="fas fa-shopping-cart"></i><?= $agotado ? 'Solicitar con proveedor' : 'Agregar al carrito' ?></button></div></div><!-- Acciones (WISHLIST INTEGRADO) --><div class="flex gap-3 pt-6 border-t border-slate-200"><?php if (isset($_SESSION['usuario_id'])): ?><form method="POST" action="producto.php?id=<?= urlencode($producto_id) ?>" id="wishlist-form" class="flex-1"><?= emxCsrfCampo() ?><input type="hidden" name="wishlist_action" value="<?= $en_wishlist ? 'remove' : 'add' ?>"><input type="hidden" name="producto_id" value="<?= $producto_id ?>"><button type="submit" id="wishlist-btn" class="w-full py-3 rounded-xl font-medium transition flex items-center justify-center gap-2 border <?= $en_wishlist ? 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' ?>"><i class="<?= $en_wishlist ? 'fas' : 'far' ?> fa-heart text-lg" id="wishlist-icon"></i><span id="wishlist-text"><?= $en_wishlist ? 'En mi lista de deseos' : 'Agregar a favoritos' ?></span></button></form><?php else: ?><a href="auth.php?action=login&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="flex-1 py-3 bg-slate-50 text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-100 transition flex items-center justify-center gap-2"><i class="far fa-heart"></i>Inicia sesión para guardar
                            </a><?php endif; ?><button type="button" onclick="compartirProducto()" class="flex-1 py-3 border border-slate-200 rounded-xl font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition flex items-center justify-center gap-2"><i class="fas fa-share-alt"></i>Compartir
                        </button></div><div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4"><a href="ficha_tecnica.php?id=<?= urlencode($producto['id']) ?>" target="_blank" class="py-3 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition flex items-center justify-center gap-2"><i class="fas fa-file-lines"></i>Ver ficha técnica
                        </a><a href="ficha_tecnica_pdf.php?id=<?= urlencode($producto['id']) ?>" class="py-3 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition flex items-center justify-center gap-2"><i class="fas fa-file-pdf"></i>Descargar PDF
                        </a></div></div></div><!-- Tabs --><div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-12"><div class="border-b border-slate-200 bg-slate-50/50"><div class="flex overflow-x-auto no-scrollbar"><button type="button" onclick="switchTab('specs')" class="tab-btn tab-active px-6 py-4 text-sm font-semibold text-slate-600 hover:text-slate-900 whitespace-nowrap transition" data-tab="specs"><i class="fas fa-list-ul mr-2"></i>Especificaciones
                        </button><button type="button" onclick="switchTab('desc')" class="tab-btn px-6 py-4 text-sm font-semibold text-slate-600 hover:text-slate-900 whitespace-nowrap transition" data-tab="desc"><i class="fas fa-align-left mr-2"></i>Descripción
                        </button><button type="button" onclick="switchTab('reviews')" class="tab-btn px-6 py-4 text-sm font-semibold text-slate-600 hover:text-slate-900 whitespace-nowrap transition" data-tab="reviews"><i class="fas fa-star mr-2"></i>Reseñas (<?= $total_reviews ?>)
                        </button></div></div><div class="p-6 lg:p-8"><!-- Especificaciones Técnicas Mejoradas --><div id="tab-specs" class="tab-content fade-in"><?php
                        $specs = json_decode($producto['especificaciones_tecnicas'] ?? '{}', true);
                        if (!empty($specs)):
                            $icon_map = [
                                'pantalla' =>['fa-tv', 'bg-blue-100 text-blue-600'],
                                'resolucion' =>['fa-expand', 'bg-blue-100 text-blue-600'],
                                'procesador' =>['fa-microchip', 'bg-purple-100 text-purple-600'],
                                'memoria' =>['fa-memory', 'bg-indigo-100 text-indigo-600'],
                                'ram' =>['fa-memory', 'bg-indigo-100 text-indigo-600'],
                                'almacenamiento' =>['fa-hdd', 'bg-cyan-100 text-cyan-600'],
                                'disco' =>['fa-hdd', 'bg-cyan-100 text-cyan-600'],
                                'bateria' =>['fa-battery-full', 'bg-green-100 text-green-600'],
                                'peso' =>['fa-weight-hanging', 'bg-orange-100 text-orange-600'],
                                'dimensiones' =>['fa-ruler-combined', 'bg-pink-100 text-pink-600'],
                                'medidas' =>['fa-ruler-combined', 'bg-pink-100 text-pink-600'],
                                'alto' =>['fa-ruler-vertical', 'bg-pink-100 text-pink-600'],
                                'ancho' =>['fa-ruler-horizontal', 'bg-pink-100 text-pink-600'],
                                'profundidad' =>['fa-ruler', 'bg-pink-100 text-pink-600'],
                                'color' =>['fa-palette', 'bg-rose-100 text-rose-600'],
                                'garantia' =>['fa-shield-alt', 'bg-emerald-100 text-emerald-600'],
                                'conectividad' =>['fa-wifi', 'bg-violet-100 text-violet-600'],
                                'puertos' =>['fa-plug', 'bg-amber-100 text-amber-600'],
                                'potencia' =>['fa-bolt', 'bg-yellow-100 text-yellow-600'],
                                'voltaje' =>['fa-plug', 'bg-yellow-100 text-yellow-600'],
                                'energia' =>['fa-leaf', 'bg-green-100 text-green-600'],
                                'consumo' =>['fa-leaf', 'bg-green-100 text-green-600'],
                                'capacidad' =>['fa-box-open', 'bg-sky-100 text-sky-600'],
                                'velocidad' =>['fa-tachometer-alt', 'bg-red-100 text-red-600'],
                                'rpm' =>['fa-tachometer-alt', 'bg-red-100 text-red-600'],
                                'ruido' =>['fa-volume-mute', 'bg-slate-100 text-slate-600'],
                                'material' =>['fa-cubes', 'bg-stone-100 text-stone-600'],
                                'acabado' =>['fa-spray-can', 'bg-stone-100 text-stone-600'],
                                'tipo' =>['fa-tag', 'bg-gray-100 text-gray-600'],
                                'modelo' =>['fa-barcode', 'bg-gray-100 text-gray-600'],
                                'fabricante' =>['fa-industry', 'bg-gray-100 text-gray-600'],
                                'smart' =>['fa-mobile-alt', 'bg-indigo-100 text-indigo-600'],
                                'wifi' =>['fa-wifi', 'bg-violet-100 text-violet-600'],
                                'bluetooth' =>['fa-bluetooth-b', 'bg-blue-100 text-blue-600'],
                                'usb' =>['fa-usb', 'bg-blue-100 text-blue-600'],
                                'hdmi' =>['fa-tv', 'bg-blue-100 text-blue-600'],
                                'funciones' =>['fa-cogs', 'bg-purple-100 text-purple-600'],
                                'funcion' =>['fa-cogs', 'bg-purple-100 text-purple-600'],
                                'accesorios' =>['fa-box-open', 'bg-amber-100 text-amber-600'],
                                'accesorio' =>['fa-box-open', 'bg-amber-100 text-amber-600'],
                                'caracteristicas' =>['fa-list', 'bg-blue-100 text-blue-600'],
                                'caracteristica' =>['fa-list', 'bg-blue-100 text-blue-600'],
                                'características' =>['fa-list', 'bg-blue-100 text-blue-600'],
                                'conexiones' =>['fa-plug', 'bg-amber-100 text-amber-600'],
                                'entrada' =>['fa-arrow-right-to-bracket', 'bg-blue-100 text-blue-600'],
                                'salida' =>['fa-arrow-right-from-bracket', 'bg-blue-100 text-blue-600'],
                                'audio' =>['fa-volume-high', 'bg-pink-100 text-pink-600'],
                                'sonido' =>['fa-volume-high', 'bg-pink-100 text-pink-600'],
                                'video' =>['fa-video', 'bg-red-100 text-red-600'],
                                'imagen' =>['fa-image', 'bg-purple-100 text-purple-600'],
                                'tasa' =>['fa-repeat', 'bg-cyan-100 text-cyan-600'],
                                'refresco' =>['fa-repeat', 'bg-cyan-100 text-cyan-600'],
                                'hdr' =>['fa-sun', 'bg-yellow-100 text-yellow-600'],
                                'dolby' =>['fa-film', 'bg-purple-100 text-purple-600'],
                            ];
                        ?><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><?php foreach ($specs as $key =>$value): 
                                    $label = ucfirst(str_replace('_', ' ', $key));
                                    if (is_array($value)) {
                                        $items = $value;
                                    } else {
                                        $items = explode(',', $value);
                                    }
                                    $items = array_map('trim', $items);
                                    $items = array_filter($items, function($i) { return $i !== ''; });
                                    $items = array_values($items);

                                    $icon = 'fa-cog';
                                    $color = 'bg-slate-100 text-slate-600';
                                    foreach ($icon_map as $keyword =>$iconData) {
                                        if (stripos($key, $keyword) !== false) {
                                            $icon = $iconData[0];
                                            $color = $iconData[1];
                                            break;
                                        }
                                    }
                                ?><div class="spec-card rounded-xl p-4 flex items-start gap-4"><div class="spec-icon <?= $color ?>"><i class="fas <?= $icon ?> text-lg"></i></div><div class="flex-1 min-w-0"><h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= htmlspecialchars($label) ?></h4><?php if (count($items) >1): ?><ul class="space-y-1"><?php foreach ($items as $item): ?><li class="text-slate-900 font-semibold leading-relaxed flex items-start gap-2"><span class="text-slate-400 mt-0.5">&bull;</span><span class="break-words"><?= htmlspecialchars($item) ?></span></li><?php endforeach; ?></ul><?php else: ?><p class="text-slate-900 font-semibold leading-relaxed break-words"><?= htmlspecialchars($items[0] ?? '') ?></p><?php endif; ?></div></div><?php endforeach; ?></div><?php else: ?><div class="text-center py-16 bg-slate-50 rounded-xl border border-dashed border-slate-200"><div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-cog text-3xl text-slate-400"></i></div><p class="text-slate-500 font-medium">No hay especificaciones técnicas disponibles</p></div><?php endif; ?></div><!-- Descripción --><div id="tab-desc" class="tab-content hidden fade-in"><?php if (!empty($producto['descripcion_corta'])): ?><div class="prose prose-slate max-w-none text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($producto['descripcion_corta'])) ?></div><?php else: ?><div class="text-center py-16 bg-slate-50 rounded-xl border border-dashed border-slate-200"><div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-info-circle text-3xl text-slate-400"></i></div><p class="text-slate-500 font-medium">No hay descripción disponible</p></div><?php endif; ?></div><!-- Reseñas MEJORADAS --><div id="tab-reviews" class="tab-content hidden fade-in"><?php if (isset($_SESSION['usuario_id'])): ?><div class="bg-slate-50 rounded-2xl p-6 mb-8 border border-slate-200"><h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-pen-to-square text-blue-600"></i>Deja tu reseña
                                </h3><form action="producto.php?id=<?= $producto_id ?>#tab-reviews" method="POST" class="space-y-4"><input type="hidden" name="action" value="submit_review"><input type="hidden" name="producto_id" value="<?= $producto_id ?>"><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700 mb-1.5">Calificación</label><select name="calificacion" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"><option value="5">⭐⭐⭐⭐⭐ - 5 Estrellas (Excelente)</option><option value="4">⭐⭐⭐⭐ - 4 Estrellas (Muy bueno)</option><option value="3">⭐⭐⭐ - 3 Estrellas (Bueno)</option><option value="2">⭐⭐ - 2 Estrellas (Regular)</option><option value="1">⭐ - 1 Estrella (Malo)</option></select></div><div><label class="block text-sm font-medium text-slate-700 mb-1.5">Título de tu reseña</label><input type="text" name="titulo" required placeholder="Ej: Excelente producto" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></div></div><div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tu comentario</label><textarea name="comentario" rows="4" required placeholder="Cuéntanos tu experiencia con este producto..." class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"></textarea></div><button type="submit" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition flex items-center justify-center gap-2 shadow-lg shadow-blue-600/20"><i class="fas fa-paper-plane"></i>Publicar reseña
                                    </button></form></div><?php else: ?><div class="bg-blue-50 rounded-2xl p-6 mb-8 border border-blue-100 text-center"><div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fas fa-user-lock text-blue-600"></i></div><p class="text-blue-900 font-semibold mb-1">¿Quieres dejar una reseña?</p><p class="text-blue-700 text-sm mb-4">Inicia sesión para compartir tu experiencia con este producto.</p><a href="auth.php?action=login&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow-sm"><i class="fas fa-sign-in-alt"></i>Iniciar sesión
                                </a></div><?php endif; ?><div class="mt-8"><h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2"><i class="fas fa-comments text-blue-600"></i>Opiniones de clientes (<?= $total_reviews ?>)
                            </h3><?php if (empty($reviews)): ?><div class="text-center py-16 bg-slate-50 rounded-2xl border border-dashed border-slate-200"><div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-comment-dots text-3xl text-slate-400"></i></div><p class="text-slate-600 font-medium">Aún no hay reseñas para este producto.</p><p class="text-slate-400 text-sm mt-1">¡Sé el primero en compartir tu experiencia!</p></div><?php else: ?><div class="space-y-6"><?php foreach ($reviews as $rev): ?><div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow duration-300"><div class="flex items-start gap-4"><div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow-sm flex-shrink-0 bg-slate-100"><?php if (!empty($rev['foto_perfil_url'])): ?><img src="<?= htmlspecialchars($rev['foto_perfil_url']) ?>" alt="Avatar" class="w-full h-full object-cover"><?php else: ?><div class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-bold text-sm"><?= strtoupper(substr($rev['nombres'], 0, 1)) . strtoupper(substr($rev['apellidos'], 0, 1)) ?></div><?php endif; ?></div><div class="flex-1 min-w-0"><div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2"><div><p class="font-bold text-slate-900"><?= htmlspecialchars($rev['nombres'] . ' ' . $rev['apellidos']) ?></p><div class="flex text-yellow-400 text-sm gap-0.5 mt-1"><?php 
                                                                $estrellas_completas_rev = floor($rev['calificacion']);
                                                                $tiene_media_rev = ($rev['calificacion'] - $estrellas_completas_rev) >= 0.5;
                                                                for ($i = 1; $i <= 5; $i++): 
                                                                    if ($i <= $estrellas_completas_rev): ?><i class="fas fa-star"></i><?php elseif ($i == $estrellas_completas_rev + 1 && $tiene_media_rev): ?><i class="fas fa-star-half-alt"></i><?php else: ?><i class="far fa-star text-slate-300"></i><?php endif; 
                                                                endfor; ?></div></div><span class="text-xs font-medium text-slate-500 bg-slate-100 px-3 py-1 rounded-full self-start sm:self-auto"><i class="far fa-clock mr-1"></i><?= date('d M, Y', strtotime($rev['created_at'])) ?></span></div><?php if (!empty($rev['titulo'])): ?><h4 class="font-bold text-slate-800 mb-2 text-base"><?= htmlspecialchars($rev['titulo']) ?></h4><?php endif; ?><div class="relative"><i class="fas fa-quote-left absolute -top-2 -left-1 text-4xl text-slate-100 -z-10"></i><p class="text-slate-600 text-sm leading-relaxed relative z-10 pl-2"><?= nl2br(htmlspecialchars($rev['comentario'])) ?></p></div></div></div></div><?php endforeach; ?></div><?php endif; ?></div></div></div></div><!-- Productos Recomendados --><?php if (!empty($productos_relacionados)): ?><div class="mb-8"><h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2"><i class="fas fa-sparkles text-blue-600"></i>Productos recomendados
                    </h2><div class="swiper related-swiper"><div class="swiper-wrapper"><?php foreach ($productos_relacionados as $rel): 
                                $rel_iva = $rel['iva_porcentaje'] ?? 15;
                                $rel_precio = $rel['precio_base'] * (1 + ($rel_iva / 100));
                            ?><div class="swiper-slide"><a href="producto.php?id=<?= $rel['id'] ?>" class="block bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300"><div class="aspect-square bg-slate-50 p-6 flex items-center justify-center"><?php if (!empty($rel['imagen_principal'])): ?><img src="<?= htmlspecialchars($rel['imagen_principal']) ?>" class="w-full h-full object-contain"><?php else: ?><i class="fas fa-image text-4xl text-slate-300"></i><?php endif; ?></div><div class="p-4"><p class="text-xs text-slate-500 mb-1 font-medium uppercase"><?= htmlspecialchars($rel['marca'] ?? 'General') ?></p><h3 class="font-semibold text-slate-900 mb-2 line-clamp-2 text-sm leading-snug"><?= htmlspecialchars($rel['nombre']) ?></h3><span class="text-lg font-extrabold text-slate-900">$<?= number_format($rel_precio, 2) ?></span></div></a></div><?php endforeach; ?></div></div><div class="hidden md:flex items-center justify-end gap-2 mt-4"><div class="swiper-button-prev-rel cursor-pointer w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center hover:bg-slate-50 hover:border-slate-300 transition shadow-sm"><i class="fas fa-chevron-left text-slate-700"></i></div><div class="swiper-button-next-rel cursor-pointer w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center hover:bg-slate-50 hover:border-slate-300 transition shadow-sm"><i class="fas fa-chevron-right text-slate-700"></i></div></div></div><?php endif; ?></main><?php endif; ?><!-- Modal de Stock --><div id="stock-modal" class="hidden fixed inset-0 z-[100] modal-overlay flex items-center justify-center p-4"><div class="modal-content bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-100"><div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-exclamation-triangle text-3xl text-amber-600"></i></div><h3 class="text-xl font-bold text-slate-900 mb-2">Stock Insuficiente</h3><p class="text-slate-600 mb-6" id="stock-message">No hay suficientes unidades disponibles</p><button type="button" onclick="closeStockModal()" class="w-full py-3 bg-slate-900 text-white rounded-xl font-semibold hover:bg-slate-800 transition active:scale-95">Entendido
            </button></div></div><?php require EMX_VIEWS_PATH . '/components/footer.php'; ?><script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script><script>const imagenesArr = <?= $imagenes_json ?: '[]' ?>;
        let currentImgIndex = 0;
        const maxStock = <?= (int)$producto['stock_actual_global'] ?>;

        function setImage(index) {
            currentImgIndex = index;
            document.getElementById('main-image').src = imagenesArr[index];
            document.querySelectorAll('.img-thumb').forEach((t, i) =>{
                t.classList.toggle('active', i === index);
            });
        }

        function changeImage(direction) {
            if (imagenesArr.length <= 1) return;
            currentImgIndex += direction;
            if (currentImgIndex < 0) currentImgIndex = imagenesArr.length - 1;
            if (currentImgIndex >= imagenesArr.length) currentImgIndex = 0;
            setImage(currentImgIndex);
        }

        function updateQty(change) {
            const input = document.getElementById('quantity-input');
            let newVal = parseInt(input.value) + change;
            if (newVal < 1) newVal = 1;
            input.value = newVal;
            if (newVal >maxStock) {
                showStockModal();
            }
        }

        function showStockModal() {
            const modal = document.getElementById('stock-modal');
            const message = document.getElementById('stock-message');
            if (maxStock >0) {
                message.textContent = `Hay ${maxStock} unidad${maxStock >1 ? 'es' : ''} inmediata${maxStock >1 ? 's' : ''}. Si pides más, el carrito simulará calendario con proveedores y tú decides si aceptas.`;
            } else {
                message.textContent = 'No hay stock inmediato. El carrito calculará entrega total o parcial con proveedores para que decidas si te conviene.';
            }
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeStockModal() {
            const modal = document.getElementById('stock-modal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        document.getElementById('quantity-input')?.addEventListener('change', function() {
            let val = parseInt(this.value);
            if (isNaN(val) || val < 1) {
                this.value = 1;
            } else if (val >maxStock) {
                showStockModal();
            }
        });

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(c =>c.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(b =>b.classList.remove('tab-active'));
            document.getElementById('tab-' + tabName).classList.remove('hidden');
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('tab-active');
            if (tabName === 'reviews') {
                setTimeout(() =>{
                    document.getElementById('tab-reviews').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }
        }

        function compartirProducto() {
            if (navigator.share) {
                navigator.share({ title: document.title, url: window.location.href }).catch(console.error);
            } else {
                navigator.clipboard.writeText(window.location.href).then(() =>{
                    const btn = event.currentTarget;
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check"></i>Copiado';
                    setTimeout(() =>btn.innerHTML = originalHTML, 2000);
                });
            }
        }

        // ==========================================
        // AGREGAR AL CARRITO CON ANIMACIÓN (Igual que index.php)
        // ==========================================
        function agregarAlCarrito(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            const cantidad = document.getElementById('quantity-input').value;
            const productId = '<?= $producto['id'] ?>';
            const btnElement = event.currentTarget;
            
            const imgEl = document.getElementById('main-image');
            const productName = '<?= addslashes($producto['nombre']) ?>';
            const productImage = imgEl ? imgEl.src : '';

            // 1. Animación de vuelo
            if (imgEl) flyToCart(imgEl);

            // 2. Feedback inmediato en el botón
            const originalHTML = btnElement.innerHTML;
            const originalClassName = btnElement.className;
            btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Agregando...';
            btnElement.disabled = true;

            // 3. Llamada AJAX
            fetch(`add_to_cart.php?id=${productId}&cantidad=${cantidad}`)
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
                            if (data.requiere_backorder && data.backorder_resumen) {
                                setTimeout(function(){ showToast(data.backorder_resumen, 'warning', data.imagen || productImage, data.nombre || productName); }, 900);
                            }
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
        }

        // ==========================================
        // ANIMACIÓN CHÉVERE PARA WISHLIST
        // ==========================================
        const wishlistForm = document.getElementById('wishlist-form');
        if (wishlistForm) {
            wishlistForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevenir envío normal para hacer animación
                
                const icon = document.getElementById('wishlist-icon');
                const text = document.getElementById('wishlist-text');
                const btn = document.getElementById('wishlist-btn');
                const formData = new FormData(this);
                const currentAction = formData.get('wishlist_action');
                
                // 1. Animación de corazón (latido)
                icon.classList.remove('far');
                icon.classList.add('fas', 'heart-anim');
                
                // 2. Cambiar colores temporalmente a "agregado"
                btn.classList.remove('bg-slate-50', 'text-slate-700', 'border-slate-200');
                btn.classList.add('bg-red-50', 'text-red-600', 'border-red-200');
                text.textContent = currentAction === 'add' ? '¡Guardado!' : '¡Eliminado!';
                
                // 3. Enviar datos por Fetch (sin recargar la página bruscamente)
                fetch(this.action, {
                    method: 'POST',
                    body: formData
                }).then(response =>{
                    if (response.redirected) {
                        // Seguir la redirección del servidor para actualizar el estado real
                        window.location.href = response.url;
                    }
                }).catch(err =>{
                    console.error('Error:', err);
                    window.location.reload(); // Fallback por si acaso
                });
            });
        }

        // Mostrar Toast de confirmación si viene de una redirección de wishlist
        <?php if ($wishlist_msg === 'added'): ?>showToast('¡Producto agregado a tu lista de deseos!', 'success');
        <?php elseif ($wishlist_msg === 'removed'): ?>showToast('Producto eliminado de tu lista de deseos', 'info');
        <?php endif; ?>// ==========================================
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
                    badge.className = 'absolute -top-1 -right-1 min-w-[20px] h-[20px] bg-blue-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white transition-transform duration-200';
                    badge.textContent = totalItems;
                    cartIcon.parentElement.style.position = 'relative';
                    cartIcon.parentElement.appendChild(badge);
                }
            }
        }

        // ==========================================
        // TOAST NOTIFICATION
        // ==========================================
        function showToast(message, type = 'success', imagen = '', nombre = '') {
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

        document.addEventListener('DOMContentLoaded', () =>{
            new Swiper('.related-swiper', {
                slidesPerView: 1.5,
                spaceBetween: 16,
                breakpoints: {
                    640: { slidesPerView: 2.5 },
                    1024: { slidesPerView: 3.5 },
                    1280: { slidesPerView: 4.5 }
                },
                navigation: {
                    nextEl: '.swiper-button-next-rel',
                    prevEl: '.swiper-button-prev-rel',
                },
            });
        });

        document.addEventListener('keydown', (e) =>{
            if (imagenesArr.length >1) {
                if (e.key === 'ArrowLeft') changeImage(-1);
                if (e.key === 'ArrowRight') changeImage(1);
            }
            if (e.key === 'Escape') {
                closeStockModal();
            }
        });

        document.getElementById('stock-modal')?.addEventListener('click', function(e) {
            if (e.target === this) closeStockModal();
        });
    </script><script src="assets/emx_modales.js"></script><script>
window.EMX_RANGOS_VOLUMEN_PRODUCTO = <?= json_encode($rangos_volumen_producto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
document.addEventListener('DOMContentLoaded', function(){
    const rangos = window.EMX_RANGOS_VOLUMEN_PRODUCTO || [];
    if (!rangos.length) return;
    const input = document.getElementById('quantity-input');
    if (!input || document.getElementById('emx-rangos-volumen-producto')) return;
    const box = document.createElement('div');
    box.id = 'emx-rangos-volumen-producto';
    box.className = 'mb-4 rounded-2xl border border-purple-200 bg-purple-50 p-3';
    box.innerHTML = '<p class="text-xs font-black text-purple-800 uppercase tracking-wide mb-2"><i class="fas fa-layer-group mr-1"></i>Descuentos por cantidad</p><div class="flex flex-wrap gap-2">' + rangos.map(function(r){
        const min = parseInt(r.cantidad_min || 1, 10);
        const max = r.cantidad_max ? '-' + parseInt(r.cantidad_max, 10) : '+';
        const desc = parseFloat(r.descuento || 0).toFixed(0);
        const etq = r.etiqueta ? ' · ' + String(r.etiqueta) : '';
        return '<span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white border border-purple-100 text-[11px] font-bold text-purple-700">' + min + max + ' unid: -' + desc + '%' + etq + '</span>';
    }).join('') + '</div>';
    const container = input.closest('div');
    if (container && container.parentNode) container.parentNode.insertBefore(box, container);
});
</script></body></html>