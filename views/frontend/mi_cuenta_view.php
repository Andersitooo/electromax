<?php
/**
 * Vista separada de `mi_cuenta.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `mi_cuenta.php`.
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
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mi Cuenta - ElectroMax</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"><style>*{font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased}
        body { background-color: #fafafa; }
        .btn-cta { transition: all .25s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-cta:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1); }
        .btn-cta:active { transform: translateY(0); }
        .nav-link { transition: all .2s ease; border-left: 3px solid transparent; }
        .nav-link.active { background-color: #f4f4f5; color: #0f172a; border-left: 3px solid #0f172a; font-weight: 700; }
        .nav-link:not(.active):hover { background-color: #f8f8f8; color: #0f172a; }
        .form-input { transition: all .2s ease; border: 1px solid #e2e8f0; background-color: #f8fafc; }
        .form-input:focus { border-color: #0f172a; background-color: #ffffff; box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.05); outline: none; }
        .card-hover { transition: transform .3s ease, box-shadow .3s ease; }
        .card-hover:hover { transform: translateY(-3px); box-shadow: 0 12px 24px rgba(15,23,42,0.06); }
        .ig-badge-container { position: absolute; bottom: 2px; right: 2px; width: 28px; height: 28px; background-color: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .ig-badge-svg { width: 20px; height: 20px; fill: #0095F6; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style></head><body class="text-slate-800 flex flex-col min-h-screen"><header class="bg-gradient-to-r from-blue-50/95 via-slate-50/90 to-blue-50/95 backdrop-blur-md border-b border-blue-100 sticky top-0 z-50"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex justify-between items-center"><a href="index.php" class="flex items-center" aria-label="ElectroMax"><img src="assets/electromax_logo.png" alt="ElectroMax" class="h-12 w-auto max-w-[190px] object-contain drop-shadow-md"></a><a href="index.php" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition flex items-center gap-2 bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-full"><i class="fas fa-arrow-left text-xs"></i>Volver a la tienda
            </a></div></header><main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full"><h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-8">Mi Cuenta</h1><?php if ($msg): ?><div class="mb-6 px-5 py-3.5 rounded-xl text-sm font-medium flex items-center gap-2 <?= $msg_type === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : ($msg_type === 'warning' ? 'bg-amber-50 border border-amber-200 text-amber-700' : 'bg-emerald-50 border border-emerald-200 text-emerald-700') ?>"><i class="fas fa-<?= $msg_type === 'error' ? 'exclamation-circle' : ($msg_type === 'warning' ? 'triangle-exclamation' : 'check-circle') ?>"></i><span><?= htmlspecialchars($msg) ?></span></div><?php endif; ?><div class="grid grid-cols-1 lg:grid-cols-12 gap-8"><div class="lg:col-span-4 xl:col-span-3"><div class="sticky top-24 space-y-6"><div class="bg-white rounded-2xl border border-slate-200 p-6 text-center shadow-sm"><div class="relative inline-block mb-4"><div class="w-28 h-28 rounded-full overflow-hidden bg-slate-100 ring-4 ring-white shadow-md mx-auto"><?php if (!empty($user['foto_perfil_url'])): ?><img src="<?= htmlspecialchars($user['foto_perfil_url']) ?>" class="w-full h-full object-cover"><?php else: ?><div class="w-full h-full flex items-center justify-center bg-slate-900 text-white text-4xl font-extrabold"><?= strtoupper(substr($user['nombres'], 0, 1)) ?></div><?php endif; ?></div><?php if (!empty($user['tiene_badge_verificado']) && ($user['tiene_badge_verificado'] === 't' || $user['tiene_badge_verificado'] === true)): ?><div class="ig-badge-container"><svg class="ig-badge-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M23 12l-2.44-2.78.34-3.68-3.61-.82-1.89-3.18L12 3 8.6 1.54 6.71 4.72l-3.61.81.34 3.68L1 12l2.44 2.78-.34 3.69 3.61.82 1.89 3.18L12 21l3.4 1.46 1.89-3.18 3.61-.82-.34-3.68L23 12zm-12.91 4.72l-3.8-3.81 1.48-1.48 2.32 2.33 5.85-5.87 1.48 1.48-7.33 7.35z"/></svg></div><?php endif; ?></div><h2 class="text-lg font-bold text-slate-900 flex items-center justify-center gap-2"><?= htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']) ?></h2><p class="text-xs text-slate-500 mt-1 break-all"><?= htmlspecialchars($user['email']) ?></p><?php if ($plan_actual && !$plan_actual['expirado']): ?><div class="mt-4"><?php if (!empty($user['es_prueba']) && ($user['es_prueba'] === 't' || $user['es_prueba'] === true)): ?><?php $dias_restantes = max(0, ceil((strtotime($user['plan_expira_en']) - time()) / 86400)); ?><span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"><i class="fas fa-clock"></i>Prueba (<?= $dias_restantes ?>días)
                                    </span><?php else: ?><span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold <?= $plan_actual['es_prime'] ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200' ?>"><?php if ($plan_actual['es_prime']): ?><i class="fas fa-crown"></i>PRIME<?php else: ?><i class="fas fa-star"></i><?= strtoupper($plan_actual['nombre']) ?><?php endif; ?></span><?php endif; ?></div><?php endif; ?></div><nav class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm flex flex-col p-2 gap-1"><a href="?seccion=pedidos" class="nav-link <?= $seccion_activa === 'pedidos' ? 'active' : '' ?>flex items-center gap-3 px-4 py-3 text-slate-600 rounded-xl text-sm font-medium"><i class="fas fa-shopping-bag w-5 text-center"></i>Mis Pedidos</a><a href="?seccion=devoluciones" class="nav-link <?= $seccion_activa === 'devoluciones' ? 'active' : '' ?>flex items-center gap-3 px-4 py-3 text-slate-600 rounded-xl text-sm font-medium"><i class="fas fa-undo w-5 text-center"></i>Devoluciones</a><a href="?seccion=membresia" class="nav-link <?= $seccion_activa === 'membresia' ? 'active' : '' ?>flex items-center gap-3 px-4 py-3 text-slate-600 rounded-xl text-sm font-medium"><i class="fas fa-crown w-5 text-center"></i>Membresía</a><a href="?seccion=historial" class="nav-link <?= $seccion_activa === 'historial' ? 'active' : '' ?>flex items-center gap-3 px-4 py-3 text-slate-600 rounded-xl text-sm font-medium"><i class="fas fa-history w-5 text-center"></i>Vistos</a><a href="?seccion=direcciones" class="nav-link <?= $seccion_activa === 'direcciones' ? 'active' : '' ?>flex items-center gap-3 px-4 py-3 text-slate-600 rounded-xl text-sm font-medium"><i class="fas fa-map-marker-alt w-5 text-center"></i>Direcciones</a><a href="?seccion=perfil" class="nav-link <?= $seccion_activa === 'perfil' ? 'active' : '' ?>flex items-center gap-3 px-4 py-3 text-slate-600 rounded-xl text-sm font-medium"><i class="fas fa-user-edit w-5 text-center"></i>Mis Datos</a><a href="?seccion=seguridad" class="nav-link <?= $seccion_activa === 'seguridad' ? 'active' : '' ?>flex items-center gap-3 px-4 py-3 text-slate-600 rounded-xl text-sm font-medium"><i class="fas fa-lock w-5 text-center"></i>Seguridad</a><div class="my-1 border-t border-slate-100"></div><a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl text-sm font-semibold transition"><i class="fas fa-sign-out-alt w-5 text-center"></i>Cerrar Sesión</a></nav></div></div><div class="lg:col-span-8 xl:col-span-9 space-y-6"><?php if ($seccion_activa === 'pedidos'): ?><div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm"><h3 class="text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-3"><span class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center text-sm shadow-sm"><i class="fas fa-shopping-bag"></i></span>Historial de Pedidos
                        </h3><?php if (empty($pedidos)): ?><div class="text-center py-20 bg-slate-50 rounded-xl border border-slate-100"><i class="fas fa-box-open text-5xl text-slate-300 mb-4"></i><p class="text-slate-500 font-medium">Aún no has realizado ningún pedido.</p><a href="index.php" class="inline-block mt-6 px-6 py-3 bg-slate-900 text-white rounded-xl font-semibold text-sm btn-cta hover:bg-slate-800">Ir a la tienda</a></div><?php else: ?><div class="space-y-5"><?php foreach ($pedidos as $pedido): 
                                    $historial = json_decode($pedido['historial_estados'] ?: '[]', true);
                                    $puede_cancelar = ($pedido['estado'] === 'Pendiente' && (($pedido['estado_pago'] ?? 'pendiente_aprobacion') === 'pendiente_aprobacion')); 
                                    
                                    $estado_confirmacion = $pedido['confirmacion_cliente_estado'] ?? 'pendiente';
                                    $mostrar_boton_confirmar = ($pedido['estado'] === 'Entregado' && $estado_confirmacion === 'pendiente');
                                    
                                    $fecha_pedido = new DateTime($pedido['created_at']);
                                    $hoy = new DateTime();
                                    $es_elegible_devolucion = ($hoy->diff($fecha_pedido)->days <= 30 && $pedido['estado'] !== 'Cancelado');
                                    
                                    $stmt_det = $pdo->prepare("SELECT dp.*, dp.numero_serie_vendido, p.nombre as nombre_producto, pm.url as imagen_url FROM detalle_pedidos dp JOIN productos p ON dp.producto_id = p.id LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.orden = 1 WHERE dp.pedido_id = ?");
                                    $stmt_det->execute([$pedido['id']]);
                                    $detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
                                ?><div class="border border-slate-200 rounded-2xl overflow-hidden card-hover bg-white"><div class="px-5 py-4 bg-slate-50/70 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 border-b border-slate-200"><div><p class="font-bold text-slate-900 text-sm">Pedido #<?= strtoupper(substr($pedido['id'], 0, 8)) ?></p><p class="text-xs text-slate-500 mt-0.5"><i class="far fa-clock mr-1"></i><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></p></div><div class="flex items-center gap-4"><span class="px-3 py-1 rounded-full text-xs font-bold <?= getEstadoColor($pedido['estado']) ?>"><?= htmlspecialchars($pedido['estado']) ?></span><span class="font-extrabold text-slate-900 text-lg">$<?= number_format($pedido['total'], 2) ?></span></div></div><div class="p-5"><div class="grid grid-cols-1 md:grid-cols-2 gap-8"><div><h4 class="text-[11px] font-bold text-slate-400 mb-3 uppercase tracking-widest">Productos</h4><div class="space-y-4"><?php foreach ($detalles as $det): 
                                                        $series = json_decode($det['numero_serie_vendido'] ?? '[]', true);
                                                    ?><div class="flex justify-between items-start gap-3 border-b border-slate-100 pb-3 last:border-0 last:pb-0"><div class="flex items-center gap-3 flex-1"><?php if (!empty($det['imagen_url'])): ?><img src="<?= htmlspecialchars($det['imagen_url']) ?>" class="w-11 h-11 object-cover rounded-xl border border-slate-100"><?php else: ?><div class="w-11 h-11 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400"><i class="fas fa-image text-xs"></i></div><?php endif; ?><div class="flex-1"><p class="text-xs font-semibold text-slate-900 leading-tight"><?= htmlspecialchars($det['nombre_producto']) ?></p><p class="text-[11px] text-slate-500 mt-1">Cant: <?= $det['cantidad'] ?></p><?php if (!empty($series)): ?><div class="mt-1.5 bg-blue-50 border border-blue-100 rounded-lg p-1.5 inline-block"><p class="text-[9px] font-bold text-blue-700 uppercase tracking-wider mb-0.5 flex items-center gap-1"><i class="fas fa-barcode"></i>Serie<?= count($series) >1 ? 's' : '' ?>de Garantía
                                                                            </p><div class="flex flex-wrap gap-1"><?php foreach ($series as $serie): ?><span class="text-[9px] font-mono text-slate-700 bg-white px-1.5 py-0.5 rounded border border-blue-100"><?= htmlspecialchars($serie) ?></span><?php endforeach; ?></div></div><?php endif; ?></div></div><p class="text-xs font-bold text-slate-900">$<?= number_format($det['total'], 2) ?></p></div><?php endforeach; ?></div></div><div><h4 class="text-[11px] font-bold text-slate-400 mb-3 uppercase tracking-widest">Seguimiento</h4><div class="relative border-l-2 border-slate-100 ml-2 pl-6 space-y-5 mb-5"><?php foreach (array_slice(array_reverse($historial), 0, 2) as $evento): ?><div class="relative"><div class="absolute -left-[33px] bg-white border-2 border-slate-900 w-6 h-6 rounded-full flex items-center justify-center shadow-sm"><i class="fas <?= $evento['icono'] ?>text-[10px] text-slate-900"></i></div><p class="text-sm font-bold text-slate-900"><?= htmlspecialchars($evento['estado']) ?></p><p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($evento['descripcion']) ?></p><p class="text-[10px] text-slate-400 mt-1"><?= date('d/m/Y H:i', strtotime($evento['fecha'])) ?></p></div><?php endforeach; ?></div><div class="flex flex-col gap-2 mt-4"><?php if ($mostrar_boton_confirmar): ?><div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-center mb-2"><p class="text-xs text-amber-800 font-medium mb-2"><i class="fas fa-clipboard-check mr-1"></i>Pendiente de tu confirmación</p><button type="button" onclick="abrirModalConfirmacion('<?= $pedido['id'] ?>')" class="w-full px-4 py-2.5 bg-amber-600 text-white rounded-xl text-xs font-bold hover:bg-amber-700 transition btn-cta shadow-sm shadow-amber-600/30"><i class="fas fa-check-circle mr-1"></i>Confirmar Recepción
                                                            </button></div><?php elseif (in_array($estado_confirmacion, ['no_recibido_reenvio', 'no_recibido_reembolso'])): ?><div class="p-3 bg-red-50 border border-red-200 rounded-xl text-center mb-2"><p class="text-xs text-red-800 font-medium"><i class="fas fa-exclamation-triangle mr-1"></i>Incidencia reportada. En revisión por soporte.</p></div><?php elseif ($estado_confirmacion === 'confirmado_ok'): ?><div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-center mb-2"><p class="text-xs text-emerald-800 font-medium"><i class="fas fa-check-double mr-1"></i>Confirmado el <?= date('d/m/Y', strtotime($pedido['fecha_confirmacion_cliente'])) ?></p></div><?php elseif ($estado_confirmacion === 'llego_danado'): ?><div class="p-3 bg-orange-50 border border-orange-200 rounded-xl text-center mb-2"><p class="text-xs text-orange-800 font-medium"><i class="fas fa-camera mr-1"></i>Daño reportado. Proceso de garantía iniciado.</p></div><?php endif; ?><a href="tracking.php?id=<?= $pedido['id'] ?>" class="w-full text-center px-4 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition btn-cta"><i class="fas fa-route mr-1"></i>Ver seguimiento completo
                                                    </a><?php if (!empty($pedido['factura_id'])): ?><a href="factura_pdf.php?id=<?= urlencode($pedido['factura_id']) ?>" target="_blank" class="w-full text-center px-4 py-2.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-xl text-xs font-semibold hover:bg-blue-100 transition btn-cta"><i class="fas fa-file-invoice-dollar mr-1"></i>Ver factura <?= htmlspecialchars($pedido['numero_factura'] ?? '') ?></a><?php elseif ($pedido['estado'] === 'Pendiente'): ?><div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-center"><p class="text-xs text-blue-800 font-medium"><i class="fas fa-file-invoice mr-1"></i>La factura se emitirá cuando el admin apruebe el pago.</p></div><?php endif; ?><?php if ($puede_cancelar): ?><form method="POST" data-emx-confirm="¿Estás seguro de cancelar este pedido?"><?= emxCsrfCampo() ?><input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>"><button type="submit" name="cancelar_pedido" class="w-full px-4 py-2.5 bg-red-50 text-red-600 border border-red-200 rounded-xl text-xs font-semibold hover:bg-red-100 transition"><i class="fas fa-times mr-1"></i>Cancelar Pedido
                                                            </button></form><?php elseif ($pedido['estado'] === 'Despachado' || $pedido['estado'] === 'En Tránsito'): ?><div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-center"><p class="text-xs text-amber-800 font-medium"><i class="fas fa-truck mr-1"></i>Tu pedido ya está en camino.</p></div><?php endif; ?><?php if ($es_elegible_devolucion): ?><button onclick="abrirModalDevolucion('<?= $pedido['id'] ?>')" class="w-full px-4 py-2.5 bg-white text-slate-600 border border-slate-200 rounded-xl text-xs font-semibold hover:bg-slate-50 transition"><i class="fas fa-undo mr-1"></i>Solicitar Devolución
                                                        </button><?php endif; ?></div></div></div></div></div><?php endforeach; ?></div><?php endif; ?></div><?php endif; ?><?php if ($seccion_activa === 'devoluciones'): ?><div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm"><h3 class="text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-3"><span class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center text-sm"><i class="fas fa-undo"></i></span>Mis Devoluciones
                        </h3><?php if (empty($devoluciones)): ?><div class="text-center py-20 bg-slate-50 rounded-xl border border-slate-100"><i class="fas fa-box-open text-5xl text-slate-300 mb-4"></i><p class="text-slate-500 font-medium">No has solicitado ninguna devolución.</p></div><?php else: ?><div class="space-y-5"><?php foreach ($devoluciones as $dev): 
                                    $estado_normalizado = emxEstadoDevolucionNormalizado($dev['estado']);
                                    $color_estado = match($estado_normalizado) {
                                        'pendiente_revision' =>'bg-amber-50 text-amber-700 border border-amber-200',
                                        'pendiente_revision_fraude' =>'bg-red-50 text-red-700 border border-red-200',
                                        'requiere_mas_evidencia' =>'bg-yellow-50 text-yellow-700 border border-yellow-200',
                                        'autorizada_retorno' =>'bg-sky-50 text-sky-700 border border-sky-200',
                                        'en_camino_retorno' =>'bg-indigo-50 text-indigo-700 border border-indigo-200',
                                        'recibido_almacen' =>'bg-purple-50 text-purple-700 border border-purple-200',
                                        'en_inspeccion' =>'bg-violet-50 text-violet-700 border border-violet-200',
                                        'investigacion_courier' =>'bg-orange-50 text-orange-700 border border-orange-200',
                                        'reclamo_courier' =>'bg-orange-50 text-orange-800 border border-orange-300',
                                        'garantia_proveedor' =>'bg-yellow-50 text-yellow-800 border border-yellow-300',
                                        'esperando_decision_cliente' =>'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        'cliente_eligio_reembolso' =>'bg-cyan-50 text-cyan-700 border border-cyan-200',
                                        'cliente_eligio_cambio' =>'bg-teal-50 text-teal-700 border border-teal-200',
                                        'aprobado_reembolso' =>'bg-cyan-50 text-cyan-700 border border-cyan-200',
                                        'aprobado_cambio' =>'bg-teal-50 text-teal-700 border border-teal-200',
                                        'reembolsado' =>'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        'cambio_despachado' =>'bg-teal-50 text-teal-700 border border-teal-200',
                                        'reemplazo_en_transito' =>'bg-violet-50 text-violet-700 border border-violet-200',
                                        'reemplazo_entregado' =>'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        'rechazada' =>'bg-red-50 text-red-700 border border-red-200',
                                        'cerrada' =>'bg-slate-100 text-slate-700 border border-slate-200',
                                        default =>'bg-slate-100 text-slate-700 border border-slate-200'
                                    };
                                    $texto_estado = emxTextoEstado($estado_normalizado);
                                    $motivo_display = function_exists('emxTextoMotivoDevolucion') ? emxTextoMotivoDevolucion($dev['motivo']) : ucfirst(str_replace('_', ' ', $dev['motivo']));
                                    $texto_solucion = match($dev['solucion_propuesta'] ?? '') {
                                        'opcion_reembolso' =>' Opción disponible: reembolso',
                                        'opcion_cambio' =>' Opción disponible: cambio por otro igual',
                                        'opcion_reembolso_cambio' =>' Puedes elegir reembolso o cambio',
                                        'reembolso_total' =>' Reembolso elegido',
                                        'reembolso_parcial' =>' Reembolso sujeto a revisión logística',
                                        'cambio_producto' =>' Cambio por otro igual elegido',
                                        'credito_tienda' =>' Crédito en tienda',
                                        default =>'Solución por definir'
                                    };
                                ?><div class="border border-slate-200 rounded-2xl p-6 card-hover bg-white"><div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-5 pb-4 border-b border-slate-100"><div><p class="font-bold text-slate-900 text-sm">Devolución del Pedido #<?= strtoupper(substr($dev['pedido_id_str'], 0, 8)) ?></p><p class="text-xs text-slate-500 mt-1">Solicitado el <?= date('d/m/Y H:i', strtotime($dev['created_at'])) ?></p></div><span class="px-3 py-1 rounded-full text-xs font-bold <?= $color_estado ?>"><?= $texto_estado ?></span></div><div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5"><div><p class="text-slate-400 text-[10px] uppercase font-bold tracking-widest mb-1">Motivo</p><p class="text-slate-800 text-sm"><?= htmlspecialchars($motivo_display) ?></p></div><div><p class="text-slate-400 text-[10px] uppercase font-bold tracking-widest mb-1">Solución</p><p class="text-slate-800 text-sm"><?= $texto_solucion ?></p><?php if ($dev['costo_envio_retorno'] >0): ?><p class="text-[11px] text-slate-500 font-medium mt-1"><i class="fas fa-truck mr-1"></i>Retorno con gestión logística</p><?php endif; ?></div></div><?php if (!empty($dev['codigo_guia'])): ?><div class="mb-5 p-5 bg-slate-900 rounded-2xl text-white"><div class="flex items-center gap-4 mb-4"><div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center text-white shadow-sm"><i class="fas fa-barcode text-xl"></i></div><div><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Guía de Envío</p><p class="text-lg font-extrabold text-white font-mono mt-1"><?= htmlspecialchars($dev['codigo_guia']) ?></p></div></div><a href="imprimir_guia.php?codigo=<?= urlencode($dev['codigo_guia']) ?>" target="_blank" class="block w-full text-center px-4 py-3 bg-white text-slate-900 text-xs font-bold rounded-xl hover:bg-slate-100 transition btn-cta"><i class="fas fa-print mr-2"></i>Imprimir Guía
                                            </a></div><?php endif; ?><?php if ($estado_normalizado === 'esperando_decision_cliente' && ($dev['respuesta_usuario'] ?? 'pendiente') === 'pendiente' && !empty($dev['solucion_propuesta'])): ?><div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl"><p class="text-xs font-bold text-emerald-800 mb-2"><i class="fas fa-handshake mr-1"></i>El admin aprobó tu caso. Elige una solución:</p><p class="text-sm text-emerald-900 font-medium mb-3"><?= $texto_solucion ?></p><form method="POST" action="responder_devolucion.php" class="grid grid-cols-1 sm:grid-cols-2 gap-2"><?= emxCsrfCampo() ?><input type="hidden" name="devolucion_id" value="<?= $dev['id'] ?>"><?php if (in_array(($dev['solucion_propuesta'] ?? ''), ['opcion_reembolso', 'opcion_reembolso_cambio'], true)): ?><button type="submit" name="accion" value="elegir_reembolso" class="px-3 py-2.5 bg-cyan-600 text-white text-xs font-bold rounded-xl hover:bg-cyan-700 transition btn-cta"><i class="fas fa-money-bill-wave mr-1"></i>Quiero reembolso</button><?php endif; ?><?php if (in_array(($dev['solucion_propuesta'] ?? ''), ['opcion_cambio', 'opcion_reembolso_cambio'], true)): ?><button type="submit" name="accion" value="elegir_cambio" class="px-3 py-2.5 bg-teal-600 text-white text-xs font-bold rounded-xl hover:bg-teal-700 transition btn-cta"><i class="fas fa-box mr-1"></i>Quiero cambio igual</button><?php endif; ?><button type="submit" name="accion" value="rechazar" class="sm:col-span-2 px-3 py-2.5 bg-white text-red-600 text-xs font-bold rounded-xl border border-red-200 hover:bg-red-50 transition"><i class="fas fa-times mr-1"></i>No acepto estas opciones</button></form></div><?php elseif ($dev['respuesta_usuario'] === 'aceptada'): ?><div class="p-4 bg-sky-50 border border-sky-200 rounded-xl flex items-center gap-3"><i class="fas fa-check-circle text-sky-600 text-lg"></i><div><p class="text-xs font-bold text-sky-800">¡Has aceptado la solución!</p><p class="text-[11px] text-sky-700">Estamos procesando tu solicitud.</p></div></div><?php elseif ($dev['respuesta_usuario'] === 'rechazada'): ?><div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-3"><i class="fas fa-exclamation-circle text-amber-600 text-lg"></i><div><p class="text-xs font-bold text-amber-800">Solución rechazada</p><p class="text-[11px] text-amber-700">Un agente te contactará pronto.</p></div></div><?php endif; ?><?php if ($estado_normalizado === 'en_camino_retorno'): ?><div class="p-4 bg-indigo-50 border border-indigo-200 rounded-xl flex items-center gap-3"><i class="fas fa-truck text-indigo-600 text-lg"></i><div><p class="text-xs font-bold text-indigo-800">En camino al almacén</p><p class="text-[11px] text-indigo-700">Esperando que el producto llegue a nuestra matriz.</p></div></div><?php elseif ($estado_normalizado === 'recibido_almacen'): ?><div class="p-4 bg-purple-50 border border-purple-200 rounded-xl flex items-center gap-3"><i class="fas fa-box-open text-purple-600 text-lg"></i><div><p class="text-xs font-bold text-purple-800">Producto recibido</p><p class="text-[11px] text-purple-700">Inspeccionando el producto.</p></div></div><?php elseif ($dev['estado'] === 'reembolsado' || $dev['estado'] === 'cambio_enviado'): ?><div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3"><i class="fas fa-check-double text-emerald-600 text-lg"></i><div><p class="text-xs font-bold text-emerald-800">¡Proceso Completado!</p><p class="text-[11px] text-emerald-700"><?= $dev['estado'] === 'reembolsado' ? 'Tu reembolso ha sido procesado.' : 'Tu producto de reemplazo ya fue enviado.' ?></p></div></div><?php elseif ($dev['estado'] === 'rechazada'): ?><div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3"><i class="fas fa-times-circle text-red-600 text-lg"></i><div><p class="text-xs font-bold text-red-800">Devolución Rechazada</p><p class="text-[11px] text-red-700">Revisa el mensaje de nuestro equipo.</p></div></div><?php elseif ($dev['estado'] === 'pendiente_revision'): ?><div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-3"><i class="fas fa-clock text-amber-600 text-lg"></i><div><p class="text-xs font-bold text-amber-800">En revisión</p><p class="text-[11px] text-amber-700">Nuestro equipo está evaluando tu solicitud.</p></div></div><?php endif; ?><?php if (!empty($dev['comentario_admin']) && $dev['estado'] !== 'aprobada'): ?><div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl"><p class="text-[10px] font-bold text-slate-500 mb-1 uppercase tracking-widest"><i class="fas fa-comment-dots mr-1"></i>Mensaje de ElectroMax</p><p class="text-sm text-slate-800"><?= htmlspecialchars($dev['comentario_admin']) ?></p></div><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?></div><?php endif; ?><?php if ($seccion_activa === 'historial'): ?><div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm"><h3 class="text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-3"><span class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center text-sm"><i class="fas fa-history"></i></span>Productos que has visto
                        </h3><?php if (empty($productos_vistos)): ?><div class="text-center py-20 bg-slate-50 rounded-xl border border-slate-100"><i class="fas fa-eye-slash text-5xl text-slate-300 mb-4"></i><p class="text-slate-500 font-medium">Aún no has visto ningún producto.</p><a href="index.php" class="inline-block mt-6 px-6 py-3 bg-slate-900 text-white rounded-xl font-semibold text-sm btn-cta hover:bg-slate-800">Explorar productos</a></div><?php else: ?><div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5"><?php foreach ($productos_vistos as $visto): 
                                    $iva = $visto['iva_porcentaje'] ?? 15;
                                    $precio_base_con_iva = $visto['precio_base'] * (1 + ($iva / 100));
                                    $tiene_descuento = false; $precio_final = $precio_base_con_iva; $descuento_pct = 0;
                                    if (!empty($visto['descuento_porcentaje']) && $visto['descuento_porcentaje'] >0) {
                                        $hoy_date = date('Y-m-d'); $desde = $visto['descuento_desde'] ?? null; $hasta = $visto['descuento_hasta'] ?? null;
                                        $descuento_activo = true;
                                        if ($desde && $hoy_date < $desde) $descuento_activo = false;
                                        if ($hasta && $hoy_date >$hasta) $descuento_activo = false;
                                        if ($descuento_activo) { $tiene_descuento = true; $descuento_pct = $visto['descuento_porcentaje']; $precio_final = $precio_base_con_iva * (1 - ($descuento_pct / 100)); }
                                    }
                                ?><a href="producto.php?id=<?= $visto['producto_id'] ?>" class="border border-slate-200 rounded-2xl overflow-hidden card-hover relative group bg-white"><?php if ($tiene_descuento): ?><div class="absolute top-3 left-3 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-md z-10 shadow-sm">-<?= $descuento_pct ?>%</div><?php endif; ?><div class="h-32 bg-slate-50 flex items-center justify-center p-3 overflow-hidden"><?php if ($visto['imagen']): ?><img src="<?= htmlspecialchars($visto['imagen']) ?>" class="max-h-full max-w-full object-contain transition group-hover:scale-105"><?php else: ?><i class="fas fa-image text-3xl text-slate-200"></i><?php endif; ?></div><div class="p-4 border-t border-slate-100"><p class="text-xs font-semibold text-slate-900 line-clamp-2 group-hover:text-slate-700 transition min-h-[2.5rem]"><?= htmlspecialchars($visto['nombre']) ?></p><div class="mt-2"><?php if ($tiene_descuento): ?><span class="text-[10px] text-slate-400 line-through block">$<?= number_format($precio_base_con_iva, 2) ?></span><p class="text-base font-extrabold text-red-500">$<?= number_format($precio_final, 2) ?></p><?php else: ?><p class="text-base font-extrabold text-slate-900">$<?= number_format($precio_final, 2) ?></p><?php endif; ?></div><p class="text-[10px] text-slate-400 mt-2 flex items-center gap-1"><i class="far fa-eye"></i>Visto el <?= date('d/m/Y', strtotime($visto['visto_en'])) ?></p></div></a><?php endforeach; ?></div><?php endif; ?></div><?php endif; ?><?php if ($seccion_activa === 'membresia'): ?><div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm"><h3 class="text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-3"><span class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center text-sm"><i class="fas fa-crown"></i></span>Mi Membresía
                        </h3><?php if (!$plan_actual || $plan_actual['expirado']): ?><div class="text-center py-16 bg-slate-50 rounded-2xl border border-slate-200"><?php if ($plan_actual && $plan_actual['expirado']): ?><i class="fas fa-clock text-5xl text-slate-300 mb-4"></i><h4 class="text-lg font-bold text-slate-900">Tu membresía ha expirado</h4><p class="text-sm text-slate-500 mt-2">Renueva para continuar disfrutando de beneficios exclusivos.</p><?php else: ?><i class="fas fa-crown text-5xl text-slate-300 mb-4"></i><h4 class="text-lg font-bold text-slate-900">No tienes una membresía activa</h4><p class="text-sm text-slate-500 mt-2">Únete y accede a precios especiales, envíos prioritarios y más.</p><?php endif; ?><a href="planes.php" class="inline-block mt-6 px-6 py-3 bg-slate-900 text-white font-bold rounded-xl text-sm btn-cta shadow-lg hover:bg-slate-800"><i class="fas fa-crown mr-2"></i>Ver planes disponibles
                                </a></div><?php else: ?><div class="p-6 rounded-2xl bg-slate-900 text-white border border-slate-800 shadow-xl"><div class="flex items-center gap-4 mb-6"><div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-white/10 border border-white/20 text-white shadow-md"><i class="fas fa-<?= $plan_actual['es_prime'] ? 'crown' : 'star' ?>text-xl"></i></div><div><h4 class="font-extrabold text-white text-xl"><?= htmlspecialchars($plan_actual['nombre']) ?></h4><p class="text-xs text-slate-400 font-medium"><?= $plan_actual['es_prime'] ? 'Miembro Prime VIP' : 'Miembro activo' ?></p></div></div><?php if (!empty($user['es_prueba']) && ($user['es_prueba'] === 't' || $user['es_prueba'] === true)): ?><?php $dias_restantes = max(0, ceil((strtotime($user['plan_expira_en']) - time()) / 86400)); ?><div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 mb-6"><div class="flex items-start gap-3"><i class="fas fa-clock text-amber-400 text-xl mt-1"></i><div><h4 class="font-bold text-amber-400">Período de Prueba Activo</h4><p class="text-sm text-amber-200/80 mt-1">Te quedan <strong><?= $dias_restantes ?>días</strong>de prueba gratis. 
                                                    Al finalizar, se procesará el cobro simulado de <strong>$<?= number_format($plan_actual['precio_mensual'], 2) ?></strong>automáticamente.
                                                </p></div></div></div><?php endif; ?><div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6"><div class="bg-white/5 p-4 rounded-xl border border-white/10"><p class="text-[10px] uppercase font-bold tracking-widest text-slate-400 mb-1">Estado</p><p class="text-sm font-bold text-emerald-400 flex items-center gap-2"><span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>Activa</p></div><div class="bg-white/5 p-4 rounded-xl border border-white/10"><p class="text-[10px] uppercase font-bold tracking-widest text-slate-400 mb-1">Vence el</p><p class="text-sm font-bold text-white"><?= !empty($user['plan_expira_en']) ? date('d/m/Y', strtotime($user['plan_expira_en'])) : 'N/A' ?></p></div></div><div class="flex flex-col sm:flex-row gap-3"><a href="planes.php" class="flex-1 text-center px-4 py-3 bg-white text-slate-900 rounded-xl text-xs font-bold hover:bg-slate-100 transition btn-cta border border-slate-200"><i class="fas fa-sync-alt mr-1"></i>Renovar / Mejorar Plan
                                    </a><form method="POST" data-emx-confirm=" ¿Estás seguro de que deseas cancelar tu membresía?

Perderás todos los beneficios (descuentos, badge, envíos) al finalizar tu período actual." class="flex-1"><?= emxCsrfCampo() ?><button type="submit" name="cancelar_membresia" class="w-full px-4 py-3 bg-red-500/10 text-red-400 border border-red-500/30 rounded-xl text-xs font-bold hover:bg-red-500/20 transition btn-cta"><i class="fas fa-ban mr-1"></i>Cancelar Membresía
                                        </button></form></div></div><?php endif; ?></div><?php endif; ?><?php if ($seccion_activa === 'direcciones'): ?><div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm"><h3 class="text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-3"><span class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center text-sm"><i class="fas fa-map-marker-alt"></i></span>Mis Direcciones
                        </h3><?php if (count($direcciones) < 3): ?><form method="POST" class="mb-8 p-6 bg-slate-50 rounded-2xl border border-slate-200"><?= emxCsrfCampo() ?><h4 class="font-bold text-slate-800 mb-4 text-sm">Agregar nueva dirección</h4><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><input type="text" name="alias" placeholder="Alias (Ej: Casa, Trabajo)" class="form-input rounded-xl px-4 py-2.5 text-sm" required><input type="text" name="direccion" placeholder="Calle y número" class="form-input rounded-xl px-4 py-2.5 text-sm" required><input type="text" name="ciudad" placeholder="Ciudad" class="form-input rounded-xl px-4 py-2.5 text-sm" required><input type="text" name="codigo_postal" placeholder="Código Postal" class="form-input rounded-xl px-4 py-2.5 text-sm"><input type="text" name="telefono" placeholder="Teléfono de contacto" class="form-input rounded-xl px-4 py-2.5 text-sm"><input type="hidden" name="latitud" value=""><input type="hidden" name="longitud" value=""></div><button type="submit" name="guardar_direccion" class="mt-4 px-5 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold btn-cta hover:bg-slate-800"><i class="fas fa-plus mr-1"></i>Guardar Dirección
                            </button></form><?php else: ?><div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-sm font-medium mb-6"><i class="fas fa-exclamation-triangle"></i>Has alcanzado el límite de 3 direcciones. Elimina una para agregar otra.
                            </div><?php endif; ?><div class="space-y-4"><?php foreach ($direcciones as $dir): ?><div class="p-5 border border-slate-200 rounded-2xl flex flex-col md:flex-row justify-between items-start gap-4 <?= $dir['es_principal'] ? 'bg-slate-50 border-slate-300' : 'bg-white' ?>"><div class="flex items-start gap-3"><div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500"><i class="fas fa-map-pin"></i></div><div><p class="font-bold text-slate-900 text-sm flex items-center gap-2"><?= htmlspecialchars($dir['alias']) ?><?php if ($dir['es_principal']): ?><span class="text-[10px] bg-slate-900 text-white px-2 py-0.5 rounded-full font-bold">Principal</span><?php endif; ?></p><p class="text-xs text-slate-600 mt-1"><?= htmlspecialchars($dir['direccion']) . ', ' . htmlspecialchars($dir['ciudad']) ?></p><?php if (!empty($dir['telefono'])): ?><p class="text-xs text-slate-500 mt-1"><i class="fas fa-phone text-[10px]"></i><?= htmlspecialchars($dir['telefono']) ?></p><?php endif; ?></div></div><div class="flex gap-2 self-end"><?php if (!$dir['es_principal']): ?><a href="?set_principal=<?= $dir['id'] ?>" class="text-xs px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 font-medium">Marcar principal</a><?php endif; ?><a href="?eliminar=<?= $dir['id'] ?>" data-emx-confirm="¿Eliminar esta dirección?" class="text-xs px-3 py-1.5 bg-red-50 border border-red-200 rounded-lg text-red-600 hover:bg-red-100 font-medium"><i class="fas fa-trash"></i></a></div></div><?php endforeach; ?></div></div><?php endif; ?><?php if ($seccion_activa === 'perfil'): ?><div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm"><h3 class="text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-3"><span class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center text-sm"><i class="fas fa-user-edit"></i></span>Mis Datos Personales
                        </h3><div class="flex flex-col sm:flex-row items-center gap-6 mb-8 pb-8 border-b border-slate-100"><div class="relative"><div class="w-24 h-24 rounded-full overflow-hidden bg-slate-100 border-4 border-white shadow-md ring-1 ring-slate-200"><?php if (!empty($user['foto_perfil_url'])): ?><img src="<?= htmlspecialchars($user['foto_perfil_url']) ?>" class="w-full h-full object-cover"><?php else: ?><div class="w-full h-full flex items-center justify-center bg-slate-900 text-white text-3xl font-extrabold"><?= strtoupper(substr($user['nombres'], 0, 1)) ?></div><?php endif; ?></div></div><div class="flex flex-col gap-2 w-full"><form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2"><?= emxCsrfCampo() ?><input type="file" name="foto_perfil" accept="image/*" class="form-input text-xs rounded-xl file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 w-full" required><button type="submit" name="upload_foto" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 whitespace-nowrap"><i class="fas fa-upload mr-1"></i>Subir</button></form><?php if (!empty($user['foto_perfil_url'])): ?><a href="?eliminar_foto=1" class="text-xs text-red-500 hover:underline mt-1">Eliminar foto actual</a><?php endif; ?></div></div><form method="POST" class="space-y-5"><?= emxCsrfCampo() ?><div class="grid grid-cols-1 md:grid-cols-2 gap-5"><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Nombres</label><input type="text" value="<?= htmlspecialchars($user['nombres']) ?>" disabled class="form-input rounded-xl px-4 py-2.5 text-sm bg-slate-50 cursor-not-allowed"></div><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Apellidos</label><input type="text" value="<?= htmlspecialchars($user['apellidos']) ?>" disabled class="form-input rounded-xl px-4 py-2.5 text-sm bg-slate-50 cursor-not-allowed"></div><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Email</label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-input rounded-xl px-4 py-2.5 text-sm" required></div><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Teléfono</label><input type="text" name="telefono" value="<?= htmlspecialchars($user['telefono'] ?? '') ?>" class="form-input rounded-xl px-4 py-2.5 text-sm"></div><div class="md:col-span-2"><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Cédula / RUC</label><input type="text" name="cedula_ruc" value="<?= htmlspecialchars($user['cedula_ruc'] ?? '') ?>" class="form-input rounded-xl px-4 py-2.5 text-sm"></div></div><div class="flex justify-end"><button type="submit" name="update_profile" class="px-6 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold btn-cta hover:bg-slate-800"><i class="fas fa-save mr-1"></i>Guardar Cambios
                                </button></div></form></div><?php endif; ?><?php if ($seccion_activa === 'seguridad'): ?><div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm"><h3 class="text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-3"><span class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center text-sm"><i class="fas fa-lock"></i></span>Seguridad de la Cuenta
                        </h3><?php $googleVinculado = !empty($user['google_id'] ?? null); ?><div class="mb-8 max-w-xl rounded-2xl border border-slate-200 bg-slate-50 p-5"><div class="flex items-start justify-between gap-4"><div><h4 class="font-extrabold text-slate-900 flex items-center gap-2"><i class="fab fa-google text-blue-600"></i>Cuenta de Google</h4><p class="text-sm text-slate-600 mt-1"><?php if ($googleVinculado): ?>Tu cuenta está vinculada con Google. Puedes iniciar sesión con el mismo correo.<?php else: ?>Vincula Google para entrar más rápido usando el mismo correo de tu cuenta ElectroMax.<?php endif; ?></p></div><span class="px-3 py-1 rounded-full text-xs font-bold <?= $googleVinculado ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' ?>"><?= $googleVinculado ? 'Vinculada' : 'No vinculada' ?></span></div><?php if (emxGoogleActivo()): ?><?php if (!$googleVinculado): ?><form id="googleLinkForm" method="POST" action="google_auth.php?action=link" class="hidden"><?= emxCsrfCampo() ?><input type="hidden" name="credential" id="googleLinkCredential"></form><div class="mt-4"><div id="g_id_onload" data-client_id="<?= htmlspecialchars(emxGoogleClientId(), ENT_QUOTES, 'UTF-8') ?>" data-callback="emxHandleGoogleLink" data-auto_prompt="false"></div><div class="g_id_signin" data-type="standard" data-shape="pill" data-theme="outline" data-text="continue_with" data-size="large" data-logo_alignment="left" data-width="300"></div></div><?php else: ?><form method="POST" action="google_auth.php?action=unlink" class="mt-4"><?= emxCsrfCampo() ?><button type="submit" class="px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 text-sm font-bold hover:bg-slate-100">Desvincular Google</button></form><?php endif; ?><?php else: ?><div class="mt-4 text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-xl p-3">Google Login está instalado, pero falta configurar EMX_GOOGLE_CLIENT_ID.</div><?php endif; ?></div><form method="POST" class="space-y-5 max-w-xl"><?= emxCsrfCampo() ?><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Contraseña Actual</label><input type="password" name="password_actual" class="form-input rounded-xl px-4 py-2.5 text-sm" required></div><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Nueva Contraseña</label><input type="password" name="password_nueva" class="form-input rounded-xl px-4 py-2.5 text-sm" required><p class="text-[11px] text-slate-400 mt-1">Mínimo 6 caracteres.</p></div><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Repetir Nueva Contraseña</label><input type="password" name="password_confirmar" class="form-input rounded-xl px-4 py-2.5 text-sm" required></div><div class="flex justify-end"><button type="submit" name="change_password" class="px-6 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold btn-cta hover:bg-slate-800"><i class="fas fa-key mr-1"></i>Actualizar Contraseña
                                </button></div></form></div><?php endif; ?></div></div></main><footer class="bg-white border-t border-slate-200 mt-12 py-6"><div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-400">&copy; <?= date('Y') ?>ElectroMax. Todos los derechos reservados.
        </div></footer><!-- ====== MODAL DE CONFIRMACIÓN DE RECEPCIÓN (MEJORADO) ====== --><div id="modalConfirmacion" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4"><div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="cerrarModalConfirmacion()"></div><div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]"><div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-gradient-to-r from-emerald-500 to-teal-600 text-white flex-shrink-0"><h3 class="text-lg font-extrabold flex items-center gap-2"><i class="fas fa-clipboard-check"></i>Confirmación de Recepción
                </h3><button type="button" onclick="cerrarModalConfirmacion()" class="text-white/80 hover:text-white transition"><i class="fas fa-times text-xl"></i></button></div><form id="formConfirmacion" method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto"><?= emxCsrfCampo() ?><input type="hidden" name="pedido_id" id="input_pedido_id_completo"><input type="hidden" name="accion_confirmacion" id="input_accion_principal"><input type="hidden" name="solucion_extravio" id="input_solucion_extravio"><p class="text-slate-600 text-sm">Pedido #<span id="modal_pedido_id_display" class="font-mono font-bold"></span></p><p class="text-slate-600 text-sm font-medium">¿Cómo recibiste tu pedido? Selecciona una opción:</p><!-- Escenario A: Llegó bien (CON FOTOS) --><div class="border-2 border-emerald-200 bg-emerald-50 rounded-xl p-4"><label class="flex items-start gap-3 cursor-pointer mb-3"><input type="radio" name="opcion_principal" value="confirmar_ok" id="esc_a" class="mt-1 w-4 h-4 text-emerald-600" required><div class="flex-1"><span class="font-bold text-emerald-800 block">Sí, llegó en perfectas condiciones</span><span class="text-xs text-emerald-700">Se activará tu garantía de 30 días desde hoy.</span></div></label><div id="campo_fotos_confirmacion" class="hidden ml-7 mt-3 p-3 bg-white rounded-lg border border-emerald-100"><p class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider mb-2"><i class="fas fa-camera mr-1"></i>Evidencia fotográfica</p><label class="block cursor-pointer"><div class="border-2 border-dashed border-emerald-300 rounded-lg p-4 text-center hover:border-emerald-500 hover:bg-emerald-50 transition"><input type="file" name="fotos_confirmacion[]" id="fotos_confirmacion" multiple accept="image/*" class="hidden" onchange="previewFotosConfirmacion()"><i class="fas fa-cloud-upload-alt text-2xl text-emerald-400 mb-1"></i><p class="text-xs text-emerald-700 font-medium">Haz clic para subir fotos</p></div></label><div id="preview_fotos_confirmacion" class="grid grid-cols-3 gap-2 mt-3"></div></div></div><!-- Escenario B: No llegó (CON SUB-OPCIONES) --><div class="border-2 border-red-200 bg-red-50 rounded-xl p-4"><label class="flex items-start gap-3 cursor-pointer mb-3"><input type="radio" name="opcion_principal" value="confirmar_no_recibido" id="esc_b" class="mt-1 w-4 h-4 text-red-600"><div class="flex-1"><span class="font-bold text-red-800 block">El courier dice entregado, pero no lo recibí</span><span class="text-xs text-red-700">Se abrirá investigación con courier. El admin definirá la solución.</span></div></label><div class="ml-7 space-y-2"><label class="flex items-start gap-2 cursor-pointer"><input type="radio" name="solucion_extravio" value="reenvio_express" class="mt-1 w-3 h-3 text-red-600"><div class="flex-1"><span class="text-xs font-bold text-red-800">Referencia: posible reenvío</span><p class="text-[10px] text-red-700">Te enviamos un producto nuevo en 24-48h</p></div></label><label class="flex items-start gap-2 cursor-pointer"><input type="radio" name="solucion_extravio" value="reembolso_total" class="mt-1 w-3 h-3 text-red-600"><div class="flex-1"><span class="text-xs font-bold text-red-800">Referencia: posible reembolso</span><p class="text-[10px] text-red-700">Devolvemos el 100% en 3-5 días</p></div></label></div></div><!-- Escenario C: Llegó dañado --><label class="flex items-start gap-3 cursor-pointer border-2 border-amber-200 bg-amber-50 rounded-xl p-4"><input type="radio" name="opcion_principal" value="confirmar_danado" id="esc_c" class="mt-1 w-4 h-4 text-amber-600"><div class="flex-1"><span class="font-bold text-amber-800 block">Llegó, pero está dañado o incompleto</span><span class="text-xs text-amber-700">Se abrirá un caso de devolución. Admin autoriza retorno, inspección y luego habilita reembolso o cambio.</span></div></label></form><div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3 flex-shrink-0"><button type="button" onclick="cerrarModalConfirmacion()" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-200 rounded-lg transition">Cancelar</button><button type="button" onclick="enviarConfirmacionMiCuenta()" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-lg">Enviar Confirmación</button></div></div></div><!-- ====== MODAL DE DEVOLUCIÓN ====== --><div id="modalDevolucion" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4"><div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="cerrarModalDevolucion()"></div><div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"><div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50"><h3 class="text-lg font-extrabold text-slate-900 flex items-center gap-2"><i class="fas fa-undo text-slate-700"></i>Solicitar Devolución
                </h3><button type="button" onclick="cerrarModalDevolucion()" class="text-slate-400 hover:text-slate-700 transition"><i class="fas fa-times text-xl"></i></button></div><div class="p-6 overflow-y-auto"><div class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3"><i class="fas fa-info-circle text-amber-600 mt-0.5"></i><div class="text-xs text-amber-800"><p class="font-bold mb-1">Política de devoluciones</p><ul class="list-disc list-inside space-y-1"><li>Tienes <strong>30 días</strong>desde la compra para solicitar devoluciones.</li><li>Si el problema es nuestra culpa (daño, error, falta de piezas), el envío es <strong>gratis</strong>y reembolso total.</li><li>Si es por decisión del cliente, el retorno se coordina según la revisión logística del caso.</li></ul></div></div><form id="formDevolucion" action="procesar_devolucion.php" method="POST" enctype="multipart/form-data" class="space-y-5"><?= emxCsrfCampo() ?><input type="hidden" name="pedido_id" id="dev_pedido_id"><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Motivo de la devolución <span class="text-red-500">*</span></label><select name="motivo" id="dev_motivo" required class="form-input rounded-xl px-4 py-2.5 text-sm w-full" onchange="manejarCambioMotivo()"><option value="">-- Selecciona un motivo --</option><optgroup label="Problemas por responsabilidad de ElectroMax (envío gratis)"><option value="defectuoso">Producto defectuoso / no funciona</option><option value="producto_incorrecto">Recibí un producto incorrecto</option><option value="faltan_piezas">Faltan piezas o accesorios</option><option value="caja_abierta">Llegó con caja abierta / sello roto</option><option value="danado_envio">Dañado durante el envío</option></optgroup><optgroup label="Decisión del cliente (retorno sujeto a revisión logística)"><option value="no_me_gusta">No me gusta / Me arrepentí</option><option value="talla_color">Talla, color o variante no esperada</option><option value="mejor_precio">Encontré mejor precio</option><option value="no_necesito">Ya no lo necesito</option></optgroup><option value="otro">Otro motivo</option></select></div><div id="campo_motivo_otro" class="hidden"><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Especifica tu motivo <span class="text-red-500">*</span></label><input type="text" name="motivo_otro" id="dev_motivo_otro" placeholder="Ej: El producto llegó sin el control remoto..." class="form-input rounded-xl px-4 py-2.5 text-sm w-full"><p class="text-[11px] text-slate-400 mt-1">Nuestro sistema analizará tu texto para clasificar la responsabilidad.</p></div><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Describe el problema <span class="text-red-500">*</span></label><textarea name="descripcion" id="dev_descripcion" rows="4" required placeholder="Cuéntanos con detalle qué ocurrió con tu pedido..." class="form-input rounded-xl px-4 py-2.5 text-sm w-full resize-none"></textarea></div><div id="campo_fotos"><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block flex items-center gap-2">Fotos de evidencia 
                            <span id="fotos_requerido_badge" class="hidden text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Obligatorio</span><span id="fotos_opcional_badge" class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">Opcional</span></label><div class="border-2 border-dashed border-slate-300 rounded-xl p-5 text-center hover:border-slate-400 transition cursor-pointer" onclick="document.getElementById('dev_fotos').click()"><input type="file" name="fotos[]" id="dev_fotos" multiple accept="image/*" class="hidden" onchange="mostrarPreviewFotos()"><i class="fas fa-cloud-upload-alt text-3xl text-slate-300 mb-2"></i><p class="text-sm text-slate-600 font-medium">Haz clic para subir fotos</p><p class="text-[11px] text-slate-400 mt-1">Puedes subir varias (JPG, PNG). Máx 5MB c/u</p></div><div id="preview_fotos" class="grid grid-cols-4 gap-2 mt-3"></div></div><div id="resumen_devolucion" class="hidden p-4 bg-slate-50 border border-slate-200 rounded-xl"><p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Resumen</p><div class="space-y-1 text-sm"><p id="resumen_costo" class="text-slate-700"></p><p id="resumen_reembolso" class="text-slate-700"></p></div></div></form></div><div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3"><button type="button" onclick="cerrarModalDevolucion()" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-100 transition">Cancelar</button><button type="button" onclick="enviarFormularioDevolucion()" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-bold hover:bg-slate-800 transition btn-cta"><i class="fas fa-paper-plane mr-1"></i>Enviar Solicitud
                </button></div></div></div><script>// ===== FUNCIONES DEL MODAL DE CONFIRMACIÓN =====
        function abrirModalConfirmacion(pedidoId) {
            document.getElementById('modal_pedido_id_display').textContent = pedidoId.substring(0, 8).toUpperCase();
            document.getElementById('input_pedido_id_completo').value = pedidoId;
            
            // Resetear selección
            document.querySelectorAll('input[name="opcion_principal"]').forEach(r =>r.checked = false);
            document.querySelectorAll('input[name="solucion_extravio"]').forEach(r =>r.checked = false);
            document.getElementById('input_accion_principal').value = '';
            document.getElementById('input_solucion_extravio').value = '';
            
            document.getElementById('campo_fotos_confirmacion').classList.add('hidden');
            document.getElementById('preview_fotos_confirmacion').innerHTML = '';
            document.getElementById('fotos_confirmacion').value = '';
            
            document.getElementById('modalConfirmacion').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalConfirmacion() {
            document.getElementById('modalConfirmacion').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Mostrar/ocultar campo de fotos según la opción
        document.addEventListener('change', function(e) {
            if (e.target.name === 'opcion_principal') {
                const campoFotos = document.getElementById('campo_fotos_confirmacion');
                if (e.target.value === 'confirmar_ok') {
                    campoFotos.classList.remove('hidden');
                } else {
                    campoFotos.classList.add('hidden');
                    document.getElementById('fotos_confirmacion').value = '';
                    document.getElementById('preview_fotos_confirmacion').innerHTML = '';
                }
            }
        });

        function previewFotosConfirmacion() {
            const input = document.getElementById('fotos_confirmacion');
            const preview = document.getElementById('preview_fotos_confirmacion');
            preview.innerHTML = '';
            if (input.files) {
                Array.from(input.files).forEach((file, idx) =>{
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative group';
                        div.innerHTML = `
                            <img src="${e.target.result}" class="w-full h-20 object-cover rounded-lg border border-emerald-200"><button type="button" onclick="eliminarFotoConfirmacion(${idx})" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full text-[10px] hidden group-hover:flex items-center justify-center shadow"><i class="fas fa-times"></i></button>`;
                        preview.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }

        function eliminarFotoConfirmacion(idx) {
            const input = document.getElementById('fotos_confirmacion');
            const dt = new DataTransfer();
            Array.from(input.files).forEach((file, i) =>{
                if (i !== idx) dt.items.add(file);
            });
            input.files = dt.files;
            previewFotosConfirmacion();
        }

        function enviarConfirmacionMiCuenta() {
            const opcionPrincipal = document.querySelector('input[name="opcion_principal"]:checked');
            if (!opcionPrincipal) {
                emxAlert(' Por favor selecciona una de las opciones principales.');
                return;
            }
            
            const accion = opcionPrincipal.value;
            document.getElementById('input_accion_principal').value = accion;
            
            if (accion === 'confirmar_ok') {
                const fotosInput = document.getElementById('fotos_confirmacion');
                if (!fotosInput.files || fotosInput.files.length === 0) {
                    emxAlert(' Debes subir al menos 1 foto de evidencia del producto recibido.');
                    return;
                }
            }
            
            if (accion === 'confirmar_no_recibido') {
                const solucion = document.querySelector('input[name="solucion_extravio"]:checked');
                if (!solucion) {
                    emxAlert(' Por favor selecciona Opción A (Reenvío) o B (Reembolso).');
                    return;
                }
                document.getElementById('input_solucion_extravio').value = solucion.value;
            }
            
            emxConfirm('¿Estás seguro de enviar esta confirmación?', function () {
                document.getElementById('formConfirmacion').submit();
            });
        }

        // ===== FUNCIONES DEL MODAL DE DEVOLUCIÓN =====
        function abrirModalDevolucion(id) {
            document.getElementById('dev_pedido_id').value = id;
            document.getElementById('modalDevolucion').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            document.getElementById('formDevolucion').reset();
            document.getElementById('campo_motivo_otro').classList.add('hidden');
            document.getElementById('preview_fotos').innerHTML = '';
            document.getElementById('resumen_devolucion').classList.add('hidden');
            document.getElementById('fotos_requerido_badge').classList.add('hidden');
            document.getElementById('fotos_opcional_badge').classList.remove('hidden');
        }

        function cerrarModalDevolucion() {
            document.getElementById('modalDevolucion').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function manejarCambioMotivo() {
            const motivo = document.getElementById('dev_motivo').value;
            const campoOtro = document.getElementById('campo_motivo_otro');
            const badgeReq = document.getElementById('fotos_requerido_badge');
            const badgeOpc = document.getElementById('fotos_opcional_badge');
            const resumen = document.getElementById('resumen_devolucion');

            if (motivo === 'otro') {
                campoOtro.classList.remove('hidden');
                document.getElementById('dev_motivo_otro').setAttribute('required', 'required');
                badgeReq.classList.add('hidden');
                badgeOpc.classList.remove('hidden');
                resumen.classList.add('hidden');
            } else {
                campoOtro.classList.add('hidden');
                document.getElementById('dev_motivo_otro').removeAttribute('required');
                const motivosConFotos = ['defectuoso', 'producto_incorrecto', 'faltan_piezas', 'caja_abierta', 'danado_envio'];
                if (motivosConFotos.includes(motivo)) {
                    badgeReq.classList.remove('hidden');
                    badgeOpc.classList.add('hidden');
                    mostrarResumen(0.00, 'reembolso_total');
                } else if (motivo !== '') {
                    badgeReq.classList.add('hidden');
                    badgeOpc.classList.remove('hidden');
                    mostrarResumen(0.00, 'envio_revision');
                } else {
                    resumen.classList.add('hidden');
                }
            }
        }

        function mostrarResumen(costo, tipo) {
            const resumen = document.getElementById('resumen_devolucion');
            const costoEl = document.getElementById('resumen_costo');
            const reembolsoEl = document.getElementById('resumen_reembolso');
            if (tipo === 'envio_revision') {
                costoEl.innerHTML = '<i class="fas fa-truck text-slate-600"></i><strong>Retorno sujeto a revisión logística</strong>';
            } else if (costo === 0) {
                costoEl.innerHTML = '<i class="fas fa-check-circle text-emerald-600"></i><strong>Envío de retorno gratis</strong>';
            } else {
                costoEl.innerHTML = '<i class="fas fa-truck text-slate-600"></i>Envío de retorno: <strong>se coordinará según revisión</strong>';
            }
            if (tipo === 'envio_revision') {
                reembolsoEl.innerHTML = '<i class="fas fa-clipboard-check text-blue-600"></i>Solución: <strong>el admin revisará el caso antes de ofrecer opciones</strong>';
            } else if (tipo === 'reembolso_total') {
                reembolsoEl.innerHTML = '<i class="fas fa-money-bill-wave text-emerald-600"></i>Reembolso: <strong>Total del producto</strong>';
            } else {
                reembolsoEl.innerHTML = '<i class="fas fa-money-bill-wave text-amber-600"></i>Solución: <strong>quedará sujeta a revisión del caso</strong>';
            }
            resumen.classList.remove('hidden');
        }

        function mostrarPreviewFotos() {
            const input = document.getElementById('dev_fotos');
            const preview = document.getElementById('preview_fotos');
            preview.innerHTML = '';
            if (input.files) {
                Array.from(input.files).forEach((file, idx) =>{
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative group';
                        div.innerHTML = `
                            <img src="${e.target.result}" class="w-full h-20 object-cover rounded-lg border border-slate-200"><button type="button" onclick="eliminarFoto(${idx})" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full text-[10px] hidden group-hover:flex items-center justify-center shadow"><i class="fas fa-times"></i></button><p class="text-[9px] text-slate-500 mt-1 truncate">${file.name}</p>`;
                        preview.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }

        function eliminarFoto(idx) {
            const input = document.getElementById('dev_fotos');
            const dt = new DataTransfer();
            Array.from(input.files).forEach((file, i) =>{
                if (i !== idx) dt.items.add(file);
            });
            input.files = dt.files;
            mostrarPreviewFotos();
        }

        function enviarFormularioDevolucion() {
            const form = document.getElementById('formDevolucion');
            const motivo = document.getElementById('dev_motivo').value;
            const descripcion = document.getElementById('dev_descripcion').value.trim();
            const motivoOtro = document.getElementById('dev_motivo_otro').value.trim();
            const fotos = document.getElementById('dev_fotos').files;

            if (!motivo) { emxAlert('Por favor selecciona un motivo.'); return; }
            if (motivo === 'otro' && !motivoOtro) { emxAlert('Por favor especifica tu motivo personalizado.'); return; }
            if (!descripcion) { emxAlert('Por favor describe el problema.'); return; }

            const motivosConFotos = ['defectuoso', 'producto_incorrecto', 'faltan_piezas', 'caja_abierta', 'danado_envio'];
            if (motivosConFotos.includes(motivo) && fotos.length === 0) {
                emxAlert(' Para este motivo debes adjuntar al menos una foto como evidencia.');
                return;
            }

            for (let file of fotos) {
                if (file.size >5 * 1024 * 1024) {
                    emxAlert(`El archivo "${file.name}" excede los 5MB permitidos.`);
                    return;
                }
            }

            emxConfirm('¿Confirmas enviar esta solicitud de devolución?', function () {
                form.submit();
            });
        }

        // Cerrar modales con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalConfirmacion();
                cerrarModalDevolucion();
            }
        });
    </script><script src="assets/emx_modales.js"></script><?php if ($seccion_activa === 'seguridad' && emxGoogleActivo()): ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
function emxHandleGoogleLink(response) {
    const form = document.getElementById('googleLinkForm');
    if (!form || !response || !response.credential) return;
    document.getElementById('googleLinkCredential').value = response.credential;
    form.submit();
}
</script>
<?php endif; ?></body></html>