<?php
/**
 * Vista separada de `notificaciones.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `notificaciones.php`.
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
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mis Notificaciones - ElectroMax</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"><style>* { font-family: 'Inter', sans-serif; }
        body { background-color: #f8fafc; }
    </style></head><body class="min-h-screen"><?php require EMX_VIEWS_PATH . '/components/navbar.php'; ?><main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8"><div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6"><div class="flex items-center justify-between mb-6"><h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-bell text-amber-500"></i>Mis Notificaciones
                    <?php if ($total_no_leidas >0): ?><span class="px-3 py-1 bg-red-100 text-red-700 text-sm font-bold rounded-full"><?= $total_no_leidas ?> nuevas
                        </span><?php endif; ?></h1><?php if ($total_no_leidas >0): ?><a href="?marcar_todas=1" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">Marcar todas como leídas
                    </a><?php endif; ?></div><?php if (empty($notificaciones)): ?><div class="text-center py-16"><div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-bell-slash text-4xl text-slate-400"></i></div><p class="text-slate-500 text-lg mb-2">No tienes notificaciones</p><p class="text-slate-400 text-sm">Agrega productos a tu lista de deseos para recibir alertas de descuentos y stock.</p></div><?php else: ?><div class="space-y-4"><?php foreach ($notificaciones as $notif): ?><div class="p-4 rounded-xl border-2 transition-all <?= $notif['leida'] ? 'bg-gray-50 border-gray-200' : 'bg-blue-50 border-blue-300 shadow-sm' ?>"><div class="flex items-start gap-4"><?php if (in_array($notif['tipo'], ['descuento_wishlist','precio_bajo_wishlist'], true)): ?><div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0"><i class="fas fa-tag text-red-600 text-xl"></i></div><?php else: ?><div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0"><i class="fas fa-box text-green-600 text-xl"></i></div><?php endif; ?><div class="flex-1 min-w-0"><div class="flex items-start justify-between gap-4"><div class="flex-1"><h3 class="font-bold text-slate-900 mb-1"><?= htmlspecialchars($notif['titulo']) ?></h3><p class="text-sm text-slate-600 mb-2"><?= htmlspecialchars($notif['mensaje']) ?></p><p class="text-xs text-slate-400 flex items-center gap-1"><i class="fas fa-clock"></i><?= date('d/m/Y H:i', strtotime($notif['creado_en'])) ?></p></div><?php if (!$notif['leida']): ?><button onclick="marcarLeida(<?= $notif['id'] ?>)" class="px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-200 transition flex-shrink-0">Marcar leída
                                            </button><?php endif; ?></div><!-- ENLACE DIRECTO AL PRODUCTO --><?php if ($notif['producto_id']): ?><a href="producto.php?id=<?= $notif['producto_id'] ?>" 
                                           onclick="marcarLeida(<?= $notif['id'] ?>)" 
                                           class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">Ver producto <i class="fas fa-arrow-right text-xs"></i></a><?php endif; ?></div></div></div><?php endforeach; ?></div><?php endif; ?></div></main><script>// Función para marcar como leída en segundo plano sin recargar la página
        function marcarLeida(notifId) {
            fetch('notificaciones.php?marcar_leida=' + notifId, { method: 'GET' })
                .then(() =>{
                    // Actualización visual inmediata de la tarjeta
                    const card = document.querySelector(`button[onclick="marcarLeida(${notifId})"]`)?.closest('.p-4');
                    if (card) {
                        card.classList.remove('bg-blue-50', 'border-blue-300', 'shadow-sm');
                        card.classList.add('bg-gray-50', 'border-gray-200');
                        const btn = card.querySelector('button');
                        if (btn) btn.remove();
                    }
                });
        }
    </script><?php if (is_file(EMX_VIEWS_PATH . '/components/footer.php')) include EMX_VIEWS_PATH . '/components/footer.php'; ?><script src="assets/emx_modales.js"></script></body></html>