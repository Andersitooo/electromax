<?php
/**
 * Vista separada de `wishlist.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `wishlist.php`.
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
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mi Lista de Deseos - ElectroMax</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"><style>* { font-family: 'Inter', sans-serif; }
        body { background-color: #f8fafc; }
    </style></head><body class="min-h-screen"><?php require EMX_VIEWS_PATH . '/components/navbar.php'; ?><main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8"><!-- Mensajes --><?php if ($msg === 'producto_agregado'): ?><div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center gap-2"><i class="fas fa-check-circle"></i>Producto agregado a tu lista de deseos
            </div><?php elseif ($msg === 'producto_eliminado'): ?><div class="mb-6 p-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 flex items-center gap-2"><i class="fas fa-info-circle"></i>Producto eliminado de tu lista de deseos
            </div><?php endif; ?><div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6"><div class="flex items-center justify-between mb-6"><h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-heart text-red-500"></i>Mi Lista de Deseos
                </h1><span class="text-sm font-medium text-slate-500 bg-slate-100 px-3 py-1 rounded-full"><?= count($wishlist) ?>productos
                </span></div><?php if (empty($wishlist)): ?><div class="text-center py-16"><div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-heart-broken text-4xl text-slate-400"></i></div><p class="text-slate-500 text-lg mb-2">Tu lista de deseos está vacía</p><p class="text-slate-400 text-sm mb-6">Guarda tus productos favoritos para no perderlos de vista.</p><a href="index.php" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition"><i class="fas fa-store"></i>Explorar productos
                    </a></div><?php else: ?><div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"><?php foreach ($wishlist as $item): 
                        $iva = 15;
                        $precio_con_iva = $item['precio_base'] * (1 + ($iva / 100));
                        $tiene_descuento = $item['descuento_porcentaje'] >0;
                        $precio_final = $tiene_descuento ? $precio_con_iva * (1 - $item['descuento_porcentaje']/100) : $precio_con_iva;
                    ?><div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition group"><div class="relative aspect-square bg-gray-50 p-4 flex items-center justify-center"><?php if ($item['imagen']): ?><img src="<?= htmlspecialchars($item['imagen']) ?>" class="w-full h-full object-contain group-hover:scale-105 transition duration-300"><?php else: ?><i class="fas fa-image text-4xl text-slate-300"></i><?php endif; ?><?php if ($tiene_descuento): ?><span class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">-<?= $item['descuento_porcentaje'] ?>%
                                    </span><?php endif; ?></div><div class="p-4"><h3 class="font-semibold text-slate-900 text-sm mb-2 line-clamp-2 h-10"><?= htmlspecialchars($item['nombre']) ?></h3><div class="flex items-center gap-2 mb-2"><?php if ($tiene_descuento): ?><span class="text-lg font-bold text-emerald-600">$<?= number_format($precio_final, 2) ?></span><span class="text-xs text-slate-400 line-through">$<?= number_format($precio_con_iva, 2) ?></span><?php else: ?><span class="text-lg font-bold text-slate-900">$<?= number_format($precio_final, 2) ?></span><?php endif; ?></div><div class="flex items-center gap-2 mb-4"><?php if ($item['stock_actual_global'] >0): ?><span class="text-xs text-green-600 font-medium flex items-center gap-1"><i class="fas fa-check-circle"></i>Disponible
                                        </span><?php else: ?><span class="text-xs text-red-600 font-medium flex items-center gap-1"><i class="fas fa-times-circle"></i>Agotado
                                        </span><?php endif; ?></div><div class="flex gap-2"><a href="producto.php?id=<?= $item['producto_id'] ?>" class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition text-center flex items-center justify-center gap-2"><i class="fas fa-eye"></i>Ver
                                    </a><form method="POST" action="wishlist.php" class="flex-1"><?= emxCsrfCampo() ?><input type="hidden" name="action" value="eliminar"><input type="hidden" name="producto_id" value="<?= $item['producto_id'] ?>"><button type="submit" class="w-full px-4 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg hover:bg-red-100 transition flex items-center justify-center gap-2"><i class="fas fa-trash"></i>Quitar
                                        </button></form></div></div></div><?php endforeach; ?></div><?php endif; ?></div></main><?php if (is_file(EMX_VIEWS_PATH . '/components/footer.php')) include EMX_VIEWS_PATH . '/components/footer.php'; ?><script src="assets/emx_modales.js"></script></body></html>