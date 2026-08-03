<?php
/**
 * Vista separada de `checkout.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `checkout.php`.
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
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Checkout Seguro - ElectroMax</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"><style>* { font-family: 'Inter', sans-serif; }
        body { background-color: #f8fafc; }
        .btn-primary { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); transition: all 0.2s; }
        .btn-primary:hover { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); transform: translateY(-1px); }
        .form-input { transition: all 0.2s; border: 1px solid #cbd5e1; }
        .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); outline: none; }
        .form-input.error { border-color: #ef4444; }
        #processing-overlay { display: none; }
        #processing-overlay.active { display: flex; }
    </style></head><body class="text-slate-800 flex flex-col min-h-screen"><div id="processing-overlay" class="fixed inset-0 z-[100] bg-slate-900/80 backdrop-blur-sm flex-col items-center justify-center text-white"><div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div><h3 class="text-xl font-bold mb-2">Procesando tu pago...</h3><p class="text-slate-300 text-sm" id="processing-text">Conectando con el banco emisor</p></div><?php if (is_file(EMX_VIEWS_PATH . '/components/navbar.php')) include EMX_VIEWS_PATH . '/components/navbar.php'; ?><div class="bg-emerald-50 border-b border-emerald-100 py-2 text-center text-xs font-bold text-emerald-700"><i class="fas fa-lock mr-1"></i>Checkout seguro · Datos protegidos</div><main class="max-w-5xl mx-auto px-4 py-8 flex-grow w-full"><?php if ($pedido_exitoso): ?><?php 
            $stmt_detalle = $pdo->prepare("
                SELECT dp.nombre_producto, dp.cantidad, dp.precio_unitario, dp.total, dp.numero_serie_vendido, pm.url as imagen_url
                FROM detalle_pedidos dp
                LEFT JOIN producto_multimedia pm ON dp.producto_id = pm.producto_id AND pm.orden = 1
                WHERE dp.pedido_id = ?
            ");
            $stmt_detalle->execute([$pedido_exitoso['id']]);
            $detalles_pedido = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);
            ?><div class="bg-white rounded-2xl shadow-sm p-8 text-center border border-slate-200 max-w-2xl mx-auto"><div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6"><i class="fas fa-hourglass-half text-4xl text-amber-600"></i></div><h1 class="text-3xl font-bold text-slate-900 mb-2">¡Pedido recibido!</h1><p class="text-slate-600 mb-6">Tu pago fue registrado como simulación y queda pendiente de aprobación administrativa. Cuando el admin lo apruebe, se emitirá la factura y se enviará al correo.</p><div class="bg-slate-50 rounded-lg p-4 mb-6 text-left border border-slate-200"><div class="flex justify-between items-center mb-4 pb-4 border-b border-slate-200"><div><p class="text-xs text-slate-500 uppercase font-bold">Número de pedido</p><p class="font-mono font-bold text-slate-800 text-lg">#<?= strtoupper(substr($pedido_exitoso['id'], 0, 8)) ?></p></div><div class="text-right"><p class="text-xs text-slate-500 uppercase font-bold">Total pagado</p><p class="font-bold text-emerald-600 text-2xl">$<?= number_format($pedido_exitoso['total'], 2) ?></p></div></div><div class="mb-4"><p class="text-xs text-slate-500 uppercase font-bold mb-3">Productos adquiridos</p><div class="space-y-3"><?php foreach ($detalles_pedido as $det): 
                                $series = json_decode($det['numero_serie_vendido'] ?? '[]', true);
                            ?><div class="bg-white p-3 rounded-xl border border-slate-200"><div class="flex items-center gap-4 mb-2"><div class="w-16 h-16 bg-slate-100 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0"><?php if (!empty($det['imagen_url'])): ?><img src="<?= htmlspecialchars($det['imagen_url']) ?>" class="w-full h-full object-cover"><?php else: ?><i class="fas fa-image text-slate-400"></i><?php endif; ?></div><div class="flex-1"><p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($det['nombre_producto']) ?></p><p class="text-xs text-slate-500">Cantidad: <?= $det['cantidad'] ?>x $<?= number_format($det['precio_unitario'], 2) ?></p></div><p class="font-bold text-slate-900">$<?= number_format($det['total'], 2) ?></p></div><?php if (!empty($series)): ?><div class="mt-2 pt-2 border-t border-slate-100"><p class="text-[10px] font-bold text-blue-700 uppercase tracking-wider mb-1"><i class="fas fa-barcode mr-1"></i>Números de Serie (Garantía)
                                            </p><div class="space-y-1"><?php foreach ($series as $idx =>$serie): ?><p class="text-xs font-mono text-blue-700 bg-blue-50 px-2 py-1 rounded inline-block mr-1 mb-1">Unidad <?= $idx + 1 ?>: <?= htmlspecialchars($serie) ?></p><?php endforeach; ?></div><p class="text-[10px] text-slate-400 mt-1"><i class="fas fa-info-circle"></i>Guarda estos números. Son necesarios para cualquier garantía o devolución.
                                            </p></div><?php endif; ?></div><?php endforeach; ?></div></div><div class="mt-4 pt-4 border-t border-slate-200"><p class="text-sm text-slate-500 mb-1"><i class="fas fa-store mr-1"></i>Sucursal asignada:</p><p class="font-bold text-slate-800"><?= htmlspecialchars($pedido_exitoso['asignacion']['sucursal_nombre']) ?></p><div class="mt-3 p-3 bg-emerald-50 border border-emerald-200 rounded-lg"><p class="text-xs text-emerald-800 font-bold uppercase tracking-wide"><i class="fas fa-calendar-check mr-1"></i>Entrega estimada:
                            </p><p class="text-lg text-emerald-900 mt-1 font-bold"><?= date('d/m/Y \a \l\a\s H:i', strtotime($pedido_exitoso['asignacion']['fecha_estimada'])) ?></p></div></div></div><div class="flex flex-col sm:flex-row gap-3 justify-center"><a href="tracking.php?id=<?= $pedido_exitoso['id'] ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition"><i class="fas fa-truck"></i>Ver seguimiento en vivo
                    </a><a href="mi_cuenta.php?seccion=pedidos" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-slate-200 text-slate-700 rounded-lg font-bold hover:bg-slate-300 transition"><i class="fas fa-box"></i>Ir a Mis Pedidos
                    </a></div></div><?php else: ?><h1 class="text-2xl font-bold text-slate-900 mb-6">Finalizar Compra</h1><?php if ($error_msg): ?><div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error_msg) ?></div><?php endif; ?><form id="checkout-form" method="POST" action="checkout.php" class="grid grid-cols-1 lg:grid-cols-3 gap-8"><?= emxCsrfCampo() ?><input type="hidden" name="action" value="procesar"><div class="lg:col-span-2 space-y-6"><div id="step-1" class="step-content bg-white rounded-xl shadow-sm border border-slate-200 p-6"><h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-truck text-blue-600"></i>Dirección de Envío</h2><p class="text-sm text-slate-500 mb-5">Elige una dirección guardada, captura tu ubicación actual o registra una nueva. Con esa ubicación se asigna la sucursal más cercana y se calcula la entrega.</p><?php
                        $stmt_dirs = $pdo->prepare("SELECT d.*, p.nombre AS provincia_nombre, c.nombre AS canton_nombre FROM direcciones_usuario d LEFT JOIN provincias p ON p.id = d.provincia_id LEFT JOIN cantones c ON c.id = d.canton_id WHERE d.usuario_id = ? ORDER BY d.es_principal DESC, d.created_at DESC");
                        $stmt_dirs->execute([$_SESSION['usuario_id']]);
                        $direcciones_guardadas = $stmt_dirs->fetchAll(PDO::FETCH_ASSOC);
                        ?><div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5"><button type="button" onclick="mostrarModoDireccion('guardada')" class="direccion-mode-card rounded-2xl border border-blue-200 bg-blue-50 p-4 text-left hover:shadow-md transition" data-mode="guardada"><span class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center mb-3"><i class="fas fa-bookmark"></i></span><strong class="block text-sm text-slate-900">Mis direcciones</strong><span class="text-xs text-slate-500">Usa una dirección registrada.</span></button><button type="button" onclick="mostrarModoDireccion('actual')" class="direccion-mode-card rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-left hover:shadow-md transition" data-mode="actual"><span class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center mb-3"><i class="fas fa-location-crosshairs"></i></span><strong class="block text-sm text-slate-900">Ubicación actual</strong><span class="text-xs text-slate-500">Captura GPS del dispositivo.</span></button><button type="button" onclick="mostrarModoDireccion('nueva')" class="direccion-mode-card rounded-2xl border border-amber-200 bg-amber-50 p-4 text-left hover:shadow-md transition" data-mode="nueva"><span class="w-9 h-9 rounded-xl bg-amber-500 text-slate-900 flex items-center justify-center mb-3"><i class="fas fa-plus"></i></span><strong class="block text-sm text-slate-900">Nueva dirección</strong><span class="text-xs text-slate-500">Regístrala para futuras compras.</span></button></div><div id="panel-direcciones-guardadas" class="mb-5 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-slate-50 p-4"><p class="text-xs font-black text-blue-900 uppercase tracking-wide mb-3"><i class="fas fa-map-marker-alt mr-1"></i>Direcciones guardadas</p><?php if (!empty($direcciones_guardadas)): ?><div class="grid grid-cols-1 md:grid-cols-2 gap-3"><?php foreach ($direcciones_guardadas as $dir): 
                                        $payload = [
                                            'id' =>$dir['id'] ?? '',
                                            'alias' =>$dir['alias'] ?? 'Dirección',
                                            'direccion' =>$dir['direccion'] ?? '',
                                            'ciudad' =>$dir['ciudad'] ?? ($dir['canton_nombre'] ?? ''),
                                            'telefono' =>$dir['telefono'] ?? '',
                                            'latitud' =>$dir['latitud'] ?? '',
                                            'longitud' =>$dir['longitud'] ?? '',
                                            'provincia_id' =>$dir['provincia_id'] ?? '',
                                            'canton_id' =>$dir['canton_id'] ?? '',
                                            'provincia_nombre' =>$dir['provincia_nombre'] ?? '',
                                            'canton_nombre' =>$dir['canton_nombre'] ?? '',
                                        ];
                                        $jsonDir = htmlspecialchars(json_encode($payload, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                        $tieneCoords = !empty($dir['latitud']) && !empty($dir['longitud']);
                                    ?><button type="button" data-dir="<?= $jsonDir ?>" onclick="usarDireccionGuardada(this)" class="group rounded-2xl border <?= $tieneCoords ? 'border-blue-200 bg-white hover:border-blue-400' : 'border-amber-200 bg-amber-50' ?>p-4 text-left transition"><div class="flex items-start justify-between gap-3"><div><p class="text-sm font-black text-slate-900"><?= htmlspecialchars($dir['alias'] ?? 'Dirección') ?><?= !empty($dir['es_principal']) ? '<span class="ml-1 text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Principal</span>' : '' ?></p><p class="text-xs text-slate-600 mt-1 line-clamp-2"><?= htmlspecialchars($dir['direccion'] ?? '') ?></p><p class="text-[11px] text-slate-500 mt-1"><?= htmlspecialchars(($dir['ciudad'] ?? '') . (!empty($dir['provincia_nombre']) ? ', ' . $dir['provincia_nombre'] : '')) ?></p></div><i class="fas fa-check-circle text-blue-500 opacity-0 group-hover:opacity-100 transition"></i></div><?php if (!$tieneCoords): ?><p class="mt-2 text-[11px] text-amber-700"><i class="fas fa-triangle-exclamation mr-1"></i>Esta dirección no tiene coordenadas. Captura ubicación para calcular sucursal.</p><?php endif; ?></button><?php endforeach; ?></div><?php else: ?><div class="rounded-xl border border-dashed border-blue-200 bg-white/70 p-4 text-sm text-slate-600">Todavía no tienes direcciones guardadas. Puedes capturar tu ubicación actual o registrar una nueva dirección en este checkout.
                                </div><?php endif; ?></div><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 mb-1">Nombre completo *</label><input type="text" name="nombre" required value="<?= htmlspecialchars($nombre_checkout) ?>" class="form-input w-full rounded-lg px-3 py-2.5 text-sm"></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Correo electrónico *</label><input type="email" name="email" required value="<?= htmlspecialchars($usuario_checkout['email'] ?? ($_SESSION['usuario_email'] ?? '')) ?>" class="form-input w-full rounded-lg px-3 py-2.5 text-sm"></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Teléfono / WhatsApp *</label><input type="tel" name="telefono" required value="<?= htmlspecialchars($usuario_checkout['telefono'] ?? '') ?>" class="form-input w-full rounded-lg px-3 py-2.5 text-sm"></div><div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 mb-1">Dirección exacta *</label><input type="text" name="direccion" id="input-direccion" required class="form-input w-full rounded-lg px-3 py-2.5 text-sm" placeholder="Calle, número, edificio, referencia"></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Provincia *</label><select name="provincia_id" id="select-provincia" required class="form-input w-full rounded-lg px-3 py-2.5 text-sm"><option value="">Selecciona provincia</option><?php foreach ($provincias_checkout as $prov): ?><option value="<?= (int)$prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option><?php endforeach; ?></select><input type="hidden" name="provincia_nombre" id="input-provincia-nombre"></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Cantón / Ciudad *</label><select name="canton_id" id="select-canton" required class="form-input w-full rounded-lg px-3 py-2.5 text-sm"><option value="">Selecciona cantón</option></select><input type="hidden" name="ciudad" id="input-ciudad"></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Código postal</label><input type="text" name="codigo_postal" class="form-input w-full rounded-lg px-3 py-2.5 text-sm"></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Alias para guardar</label><input type="text" name="direccion_alias" id="direccion_alias" class="form-input w-full rounded-lg px-3 py-2.5 text-sm" placeholder="Casa, Trabajo, Universidad"></div></div><input type="hidden" name="direccion_tipo" id="direccion_tipo" value="manual"><input type="hidden" name="latitud" id="input-latitud" value=""><input type="hidden" name="longitud" id="input-longitud" value=""><div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3"><button type="button" onclick="obtenerUbicacionActual()" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 hover:bg-emerald-100 transition"><i class="fas fa-location-crosshairs"></i>Capturar ubicación actual
                            </button><label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700"><input type="checkbox" name="guardar_direccion" value="1" class="rounded border-slate-300 text-blue-600">Guardar esta dirección en mi cuenta
                            </label></div><p id="ubicacion-status" class="text-xs text-slate-500 mt-2">Selecciona una dirección con coordenadas o captura la ubicación actual para calcular la sucursal más cercana.</p><div class="mt-6 flex justify-end"><button type="button" onclick="nextStep(2)" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">Continuar a Facturación <i class="fas fa-arrow-right ml-2"></i></button></div></div><div id="step-2" class="step-content hidden bg-white rounded-xl shadow-sm border border-slate-200 p-6"><h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-file-invoice text-blue-600"></i>Facturación y Pago</h2><div class="mb-6 rounded-xl border border-blue-100 bg-blue-50 p-4"><h3 class="text-sm font-bold text-blue-900 mb-3">Datos de facturación</h3><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div><label class="block text-xs font-medium text-slate-700 mb-1">Tipo de identificación *</label><select name="fact_tipo_identificacion" id="fact_tipo_identificacion" required class="form-input w-full rounded-lg px-3 py-2.5 text-sm"><option value="cedula">Cédula</option><option value="ruc">RUC</option><option value="pasaporte">Pasaporte</option><option value="consumidor_final">Consumidor final</option></select></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Identificación *</label><input type="text" name="fact_identificacion" id="fact_identificacion" required class="form-input w-full rounded-lg px-3 py-2.5 text-sm" placeholder="Cédula o RUC"></div><div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 mb-1">Nombres / Razón social *</label><input type="text" name="fact_razon_social" id="fact_razon_social" required class="form-input w-full rounded-lg px-3 py-2.5 text-sm" value="<?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?>"></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Correo de facturación *</label><input type="email" name="fact_email" id="fact_email" required class="form-input w-full rounded-lg px-3 py-2.5 text-sm" value="<?= htmlspecialchars($_SESSION['usuario_email'] ?? '') ?>"></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Teléfono</label><input type="text" name="fact_telefono" id="fact_telefono" class="form-input w-full rounded-lg px-3 py-2.5 text-sm"></div><div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 mb-1">Dirección fiscal *</label><input type="text" name="fact_direccion" id="fact_direccion" required class="form-input w-full rounded-lg px-3 py-2.5 text-sm"></div></div><button type="button" onclick="copiarDatosEnvioAFacturacion()" class="mt-3 text-xs text-blue-700 font-semibold hover:underline"><i class="fas fa-copy mr-1"></i>Usar datos de envío para facturación</button></div><h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2"><i class="fas fa-credit-card text-blue-600"></i>Tarjeta simulada</h3><div class="space-y-4"><div><label class="block text-xs font-medium text-slate-700 mb-1">Nombre en la Tarjeta *</label><input type="text" id="card_name" name="card_name" required class="form-input w-full rounded-lg px-3 py-2.5 text-sm uppercase" placeholder="JUAN PEREZ"></div><div><label class="block text-xs font-medium text-slate-700 mb-1">Número de Tarjeta *</label><div class="relative"><input type="text" id="card_number" name="card_number" required maxlength="19" class="form-input w-full rounded-lg pl-10 pr-12 py-2.5 text-sm font-mono tracking-wider" placeholder="0000 0000 0000 0000"><i class="fas fa-credit-card absolute left-3 top-3 text-slate-400"></i><i id="card-brand-icon" class="fab fa-cc-visa absolute right-3 top-2.5 text-2xl text-slate-400"></i></div><p id="card-error" class="text-xs text-red-500 mt-1 hidden"><i class="fas fa-exclamation-triangle"></i>Número de tarjeta inválido</p></div><div class="grid grid-cols-2 gap-4"><div><label class="block text-xs font-medium text-slate-700 mb-1">Fecha de Expiración (MM/AA) *</label><input type="text" id="card_expiry" name="card_expiry" required maxlength="5" class="form-input w-full rounded-lg px-3 py-2.5 text-sm font-mono" placeholder="MM/AA"></div><div><label class="block text-xs font-medium text-slate-700 mb-1">CVV *</label><div class="relative"><input type="password" id="card_cvv" name="card_cvv" required maxlength="4" class="form-input w-full rounded-lg px-3 py-2.5 text-sm font-mono" placeholder="123"><i class="fas fa-question-circle absolute right-3 top-3 text-slate-400 cursor-help" title="3 dígitos en el reverso"></i></div></div></div></div><div class="mt-6 flex justify-between"><button type="button" onclick="prevStep(1)" class="px-6 py-2.5 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition"><i class="fas fa-arrow-left mr-2"></i>Volver</button><button type="button" onclick="validateAndGoToStep(3)" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">Revisar Pedido <i class="fas fa-arrow-right ml-2"></i></button></div></div><div id="step-3" class="step-content hidden bg-white rounded-xl shadow-sm border border-slate-200 p-6"><h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-clipboard-check text-blue-600"></i>Revisar y Confirmar</h2><div class="space-y-4 mb-6"><div class="border-b border-slate-100 pb-4"><h4 class="text-xs font-bold text-slate-500 uppercase mb-2">Enviar a:</h4><p class="text-sm font-medium text-slate-800" id="summary-name"></p><p class="text-sm text-slate-600" id="summary-address"></p><p class="text-sm text-slate-600" id="summary-contact"></p></div><div class="border-b border-slate-100 pb-4"><h4 class="text-xs font-bold text-slate-500 uppercase mb-2">Método de Pago:</h4><div class="flex items-center gap-3"><i id="summary-card-icon" class="fab fa-cc-visa text-2xl text-slate-700"></i><p class="text-sm font-medium text-slate-800">Tarjeta terminada en <span id="summary-card-last4" class="font-mono"></span></p></div></div><div class="bg-blue-50 border border-blue-200 rounded-lg p-4"><h4 class="text-xs font-bold text-blue-800 uppercase mb-2"><i class="fas fa-store mr-1"></i>Sucursal que procesará tu pedido:</h4><p class="text-sm text-blue-900" id="summary-sucursal">Calculando...</p><p class="text-xs text-blue-700 mt-1" id="summary-distancia"></p><p class="text-sm font-bold text-blue-800 mt-1" id="summary-tiempo"></p></div></div><div class="mt-6 flex justify-between"><button type="button" onclick="prevStep(2)" class="px-6 py-2.5 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition"><i class="fas fa-arrow-left mr-2"></i>Volver</button><button type="submit" id="btn-submit-order" class="btn-primary px-8 py-3 rounded-lg font-bold text-slate-900 flex items-center gap-2 shadow-lg"><i class="fas fa-lock text-sm"></i>Confirmar y Pagar $<span id="summary-total-btn"></span></button></div></div></div><div class="lg:col-span-1"><div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sticky top-24"><h2 class="text-lg font-bold text-slate-900 mb-4">Resumen del Pedido</h2><div class="space-y-4 max-h-60 overflow-y-auto mb-4 pr-2"><?php foreach ($productos_carrito as $item): ?><div class="flex gap-3 items-start border-b border-slate-100 pb-3"><div class="relative flex-shrink-0"><?php if ($item['imagen']): ?><img src="<?= htmlspecialchars($item['imagen']) ?>" class="w-16 h-16 object-contain border border-slate-200 rounded bg-white p-1"><?php else: ?><div class="w-16 h-16 bg-slate-100 rounded flex items-center justify-center text-slate-400"><i class="fas fa-image"></i></div><?php endif; ?><span class="absolute -top-2 -right-2 w-5 h-5 bg-slate-800 text-white text-[10px] font-bold rounded-full flex items-center justify-center"><?= $item['cantidad'] ?></span></div><div class="flex-1 min-w-0"><p class="text-xs font-medium text-slate-800 truncate"><?= htmlspecialchars($item['nombre']) ?></p><?php if ($item['descuento_aplicado'] >0): ?><div class="mt-1"><p class="text-sm font-bold text-emerald-600">$<?= number_format($item['precio_final'], 2) ?></p><p class="text-xs text-slate-400 line-through">$<?= number_format($item['precio_con_iva'], 2) ?></p><span class="text-[10px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-bold">-<?= number_format($item['descuento_aplicado']) ?>%</span></div><?php else: ?><p class="text-sm font-semibold text-slate-800 mt-1">$<?= number_format($item['precio_final'], 2) ?></p><?php endif; ?><?php $pidResumen = $item['producto_id'] ?? null; $boAceptado = $pidResumen ? ($_SESSION['backorder_planes'][$pidResumen] ?? null) : null; ?><?php if (!empty($boAceptado)): $boPlan = $boAceptado['plan'] ?? []; $boOpcion = $boAceptado['opcion'] ?? 'total'; $boDetalle = $boOpcion === 'parcial' ? ($boPlan['opcion_parcial'] ?? []) : ($boPlan['opcion_total'] ?? []); ?><div class="mt-2 rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-[11px] text-blue-900"><p class="font-black uppercase tracking-wide"><i class="fas fa-calendar-check mr-1"></i>Entrega <?= $boOpcion === 'parcial' ? 'parcial' : 'total' ?>aceptada</p><?php if ($boOpcion === 'parcial'): ?><p>Disponible ahora: <?= (int)($boDetalle['despacho_inmediato'] ?? 0) ?>· Finaliza aprox.: <?= htmlspecialchars($boDetalle['fecha_final'] ?? 'Por confirmar') ?></p><?php else: ?><p>Entrega completa estimada para: <?= htmlspecialchars($boDetalle['fecha'] ?? 'Por confirmar') ?>· Tiempo aprox.: <?= (int)($boDetalle['dias'] ?? 0) ?>días</p><?php endif; ?></div><?php endif; ?></div><div class="text-right flex-shrink-0"><p class="text-sm font-bold text-slate-900">$<?= number_format($item['total'], 2) ?></p></div></div><?php endforeach; ?></div><div class="border-t border-slate-200 pt-4 space-y-2"><div class="flex justify-between text-sm"><span class="text-slate-600">Subtotal:</span><span class="font-semibold">$<?= number_format($subtotal_original, 2) ?></span></div><?php if ($total_descuento >0): ?><div class="flex justify-between text-sm text-emerald-600"><span class="flex items-center gap-1"><i class="fas fa-tag"></i>Descuento:</span><span class="font-semibold">-$<?= number_format($total_descuento, 2) ?></span></div><div class="flex justify-between text-sm font-medium text-slate-800 pt-2 border-t border-slate-100"><span>Subtotal con descuento:</span><span>$<?= number_format($subtotal_original - $total_descuento, 2) ?></span></div><?php endif; ?><div class="flex justify-between text-sm text-cyan-600"><span class="flex items-center gap-1"><i class="fas fa-file-invoice-dollar"></i>IVA:</span><span class="font-semibold">$<?= number_format($total_iva, 2) ?></span></div><div class="flex justify-between text-sm text-emerald-600"><span class="flex items-center gap-1"><i class="fas fa-truck"></i>Envío:</span><span class="font-semibold">GRATIS</span></div><div class="flex justify-between items-center pt-3 border-t border-slate-200"><span class="text-base font-bold text-slate-900">Total:</span><span class="text-xl font-bold text-slate-900">$<?= number_format($total_general, 2) ?></span></div></div></div></div></form><?php endif; ?></main><?php if (is_file(EMX_VIEWS_PATH . '/components/footer.php')) include EMX_VIEWS_PATH . '/components/footer.php'; ?><script>const CANTONES_CHECKOUT = <?= json_encode($cantones_checkout, JSON_UNESCAPED_UNICODE) ?>;
        let currentStep = 1;

        function updateStepUI(step) {
            document.querySelectorAll('.step-content').forEach(el =>el.classList.add('hidden'));
            document.getElementById(`step-${step}`).classList.remove('hidden');
            currentStep = step;
        }

        function nextStep(step) {
            if (step === 2) {
                const form = document.getElementById('step-1');
                const inputs = form.querySelectorAll('input[required], select[required]');
                let valid = true;
                inputs.forEach(input =>{
                    if (!String(input.value).trim()) { input.classList.add('error'); valid = false; }
                    else { input.classList.remove('error'); }
                });
                const provSel = document.getElementById('select-provincia');
                const cantonSel = document.getElementById('select-canton');
                document.getElementById('input-provincia-nombre').value = provSel.options[provSel.selectedIndex]?.text || '';
                document.getElementById('input-ciudad').value = cantonSel.options[cantonSel.selectedIndex]?.text || '';
                if (!valid) return emxAlert('Completa todos los campos obligatorios de envío.');
                const lat = document.getElementById('input-latitud').value;
                const lng = document.getElementById('input-longitud').value;
                if (!lat || !lng) return emxAlert('Selecciona una dirección guardada con coordenadas o captura tu ubicación actual.');
            }
            updateStepUI(step);
        }

        function prevStep(step) { updateStepUI(step); }

        function validateAndGoToStep(step) {
            const cardNum = document.getElementById('card_number').value.replace(/\s/g, '');
            const cardName = document.getElementById('card_name').value;
            const cardExpiry = document.getElementById('card_expiry').value;
            const cardCvv = document.getElementById('card_cvv').value;

            const billingReq = document.querySelectorAll('#step-2 input[required], #step-2 select[required]');
            for (const input of billingReq) {
                if (!String(input.value).trim()) { input.classList.add('error'); emxAlert('Completa todos los datos de facturación y pago.'); return; }
                input.classList.remove('error');
            }
            if (!cardNum || !cardName || !cardExpiry || !cardCvv) {
                emxAlert('Completa todos los datos de la tarjeta.');
                return;
            }

            if (!validarLuhnJS(cardNum)) {
                document.getElementById('card_number').classList.add('error');
                document.getElementById('card-error').classList.remove('hidden');
                return;
            } else {
                document.getElementById('card_number').classList.remove('error');
                document.getElementById('card-error').classList.add('hidden');
            }

            document.getElementById('summary-name').textContent = document.querySelector('input[name="nombre"]').value;
            document.getElementById('summary-address').textContent = document.querySelector('input[name="direccion"]').value + ', ' + document.querySelector('input[name="ciudad"]').value;
            document.getElementById('summary-contact').textContent = document.querySelector('input[name="email"]').value + ' | ' + document.querySelector('input[name="telefono"]').value;
            document.getElementById('summary-card-last4').textContent = cardNum.slice(-4);
            document.getElementById('summary-total-btn').textContent = document.querySelector('.text-xl.font-bold.text-slate-900').textContent.replace('$', '');

            calcularSucursalEnVivo();
            updateStepUI(step);
        }

        function validarLuhnJS(numero) {
            let suma = 0; let paridad = (numero.length % 2);
            for (let i = 0; i < numero.length; i++) {
                let digito = parseInt(numero[i]);
                if (i % 2 === paridad) { digito *= 2; if (digito >9) digito -= 9; }
                suma += digito;
            }
            return (suma % 10 === 0);
        }

        document.getElementById('card_number').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            let formatted = '';
            let icon = document.getElementById('card-brand-icon');
            icon.className = 'fab absolute right-3 top-2.5 text-2xl text-slate-400';
            if (value.startsWith('4')) icon.classList.add('fa-cc-visa', 'text-blue-800');
            else if (value.startsWith('5')) icon.classList.add('fa-cc-mastercard', 'text-red-600');
            else if (value.startsWith('3')) icon.classList.add('fa-cc-amex', 'text-blue-500');
            else icon.classList.add('fa-credit-card');
            for (let i = 0; i < value.length; i++) {
                if (i >0 && i % 4 === 0) formatted += ' ';
                formatted += value[i];
            }
            e.target.value = formatted;
        });

        document.getElementById('card_expiry').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) value = value.substring(0, 2) + '/' + value.substring(2, 4);
            e.target.value = value;
        });

        function mostrarModoDireccion(modo) {
            document.querySelectorAll('.direccion-mode-card').forEach(card =>{
                card.classList.remove('ring-2', 'ring-blue-500', 'shadow-lg');
                if (card.dataset.mode === modo || (modo === 'actual' && card.dataset.mode === 'actual') || (modo === 'nueva' && card.dataset.mode === 'nueva')) {
                    card.classList.add('ring-2', 'ring-blue-500', 'shadow-lg');
                }
            });
            const panel = document.getElementById('panel-direcciones-guardadas');
            if (panel) panel.style.display = modo === 'guardada' ? 'block' : 'none';
            document.getElementById('direccion_tipo').value = modo === 'actual' ? 'ubicacion_actual' : (modo === 'guardada' ? 'guardada' : 'manual');
            if (modo === 'actual') obtenerUbicacionActual();
            if (modo === 'nueva') {
                document.getElementById('input-direccion').focus();
                document.getElementById('ubicacion-status').textContent = 'Completa la dirección y captura tu ubicación para calcular sucursal cercana.';
                document.getElementById('ubicacion-status').className = 'text-xs text-blue-600 mt-2';
            }
        }

        function seleccionarProvinciaYCanton(provinciaId, cantonId) {
            const provSel = document.getElementById('select-provincia');
            const cantonSel = document.getElementById('select-canton');
            if (provinciaId && provSel) {
                provSel.value = String(provinciaId);
                cantonSel.innerHTML = '<option value="">Selecciona cantón</option>';
                CANTONES_CHECKOUT.filter(c =>parseInt(c.provincia_id) === parseInt(provinciaId)).forEach(c =>{
                    const opt = document.createElement('option');
                    opt.value = c.id; opt.textContent = c.nombre; cantonSel.appendChild(opt);
                });
                if (cantonId) cantonSel.value = String(cantonId);
                document.getElementById('input-provincia-nombre').value = provSel.options[provSel.selectedIndex]?.text || '';
                document.getElementById('input-ciudad').value = cantonSel.options[cantonSel.selectedIndex]?.text || '';
            }
        }

        function usarDireccionGuardada(btn) {
            let data = {};
            try { data = JSON.parse(btn.dataset.dir || '{}'); } catch (e) { data = {}; }
            document.getElementById('input-direccion').value = data.direccion || '';
            document.querySelector('input[name="telefono"]').value = data.telefono || document.querySelector('input[name="telefono"]').value;
            document.getElementById('direccion_alias').value = data.alias || '';
            document.getElementById('direccion_tipo').value = 'guardada';
            seleccionarProvinciaYCanton(data.provincia_id || '', data.canton_id || '');
            if (!document.getElementById('input-ciudad').value && data.ciudad) document.getElementById('input-ciudad').value = data.ciudad;
            if (data.latitud && data.longitud) {
                document.getElementById('input-latitud').value = data.latitud;
                document.getElementById('input-longitud').value = data.longitud;
                document.getElementById('ubicacion-status').textContent = ' Dirección guardada seleccionada. Se usará para calcular sucursal y entrega.';
                document.getElementById('ubicacion-status').className = 'text-xs text-emerald-600 mt-2';
            } else {
                document.getElementById('input-latitud').value = '';
                document.getElementById('input-longitud').value = '';
                document.getElementById('ubicacion-status').textContent = ' Esta dirección no tiene coordenadas. Captura tu ubicación actual antes de continuar.';
                document.getElementById('ubicacion-status').className = 'text-xs text-amber-700 mt-2';
            }
            document.querySelectorAll('[data-dir]').forEach(el =>el.classList.remove('ring-2','ring-blue-500'));
            btn.classList.add('ring-2','ring-blue-500');
        }

        function copiarDatosEnvioAFacturacion() {
            const nombre = document.querySelector('input[name="nombre"]').value || '';
            const email = document.querySelector('input[name="email"]').value || '';
            const tel = document.querySelector('input[name="telefono"]').value || '';
            const dir = document.querySelector('input[name="direccion"]').value || '';
            document.getElementById('fact_razon_social').value = nombre;
            document.getElementById('fact_email').value = email;
            document.getElementById('fact_telefono').value = tel;
            document.getElementById('fact_direccion').value = dir;
        }

        document.getElementById('select-provincia')?.addEventListener('change', function(){
            const provinciaId = parseInt(this.value || '0', 10);
            const canton = document.getElementById('select-canton');
            canton.innerHTML = '<option value="">Selecciona cantón</option>';
            CANTONES_CHECKOUT.filter(c =>parseInt(c.provincia_id) === provinciaId).forEach(c =>{
                const opt = document.createElement('option');
                opt.value = c.id; opt.textContent = c.nombre; canton.appendChild(opt);
            });
            document.getElementById('input-provincia-nombre').value = this.options[this.selectedIndex]?.text || '';
        });
        document.getElementById('select-canton')?.addEventListener('change', function(){
            document.getElementById('input-ciudad').value = this.options[this.selectedIndex]?.text || '';
        });
        document.getElementById('fact_tipo_identificacion')?.addEventListener('change', function(){
            if (this.value === 'consumidor_final') {
                document.getElementById('fact_identificacion').value = '9999999999999';
                document.getElementById('fact_razon_social').value = 'Consumidor Final';
            }
        });

        function obtenerUbicacionActual() {
            const status = document.getElementById('ubicacion-status');
            if (!navigator.geolocation) {
                status.textContent = ' Tu navegador no soporta geolocalización';
                return;
            }
            status.textContent = ' Obteniendo ubicación...';
            navigator.geolocation.getCurrentPosition(
                (position) =>{
                    document.getElementById('input-latitud').value = position.coords.latitude;
                    document.getElementById('input-longitud').value = position.coords.longitude;
                    document.getElementById('direccion_tipo').value = 'ubicacion_actual';
                    status.textContent = ' Ubicación obtenida: ' + position.coords.latitude.toFixed(4) + ', ' + position.coords.longitude.toFixed(4);
                    status.className = 'text-xs text-emerald-600 mt-1';
                },
                (error) =>{
                    status.textContent = ' No se pudo obtener la ubicación.';
                    status.className = 'text-xs text-red-600 mt-1';
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        function calcularSucursalEnVivo() {
            const lat = parseFloat(document.getElementById('input-latitud').value);
            const lng = parseFloat(document.getElementById('input-longitud').value);
            document.getElementById('summary-sucursal').textContent = ' Calculando sucursal óptima...';
            document.getElementById('summary-distancia').textContent = '';
            document.getElementById('summary-tiempo').textContent = '';
            
            setTimeout(() =>{
                document.getElementById('summary-sucursal').innerHTML = '<i class="fas fa-check-circle text-emerald-600"></i>El sistema asignará automáticamente la sucursal más cercana con stock.';
                document.getElementById('summary-distancia').textContent = ' Coordenadas: ' + lat.toFixed(4) + ', ' + lng.toFixed(4);
                document.getElementById('summary-tiempo').textContent = '⏱ El tiempo se calculará en base a la distancia real.';
            }, 800);
        }

        mostrarModoDireccion('guardada');

        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const overlay = document.getElementById('processing-overlay');
            const texts = ["Conectando con el banco emisor...", "Verificando fondos disponibles...", "Calculando sucursal óptima...", "Reservando stock en sucursal...", "Pago registrado para aprobación"];
            overlay.classList.add('active');
            let step = 0;
            const interval = setInterval(() =>{
                document.getElementById('processing-text').textContent = texts[step];
                step++;
                if (step >= texts.length) {
                    clearInterval(interval);
                    setTimeout(() =>{ this.submit(); }, 800);
                }
            }, 800);
        });
    </script><script src="assets/emx_modales.js"></script></body></html>