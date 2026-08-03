<?php
/**
 * Vista separada de `planes.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `planes.php`.
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
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Planes de Membresía - ElectroMax</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"><style>* { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); }
        .plan-card { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e2e8f0; background: rgba(255, 255, 255, 0.9); }
        .plan-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1); border-color: #cbd5e1; }
        .plan-card.featured { border: 2px solid #f59e0b; transform: scale(1.03); box-shadow: 0 25px 50px -12px rgba(245, 158, 11, 0.25); background: #fff; }
        .plan-card.featured:hover { transform: scale(1.03) translateY(-8px); }
        .btn-subscribe { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); transition: all 0.3s ease; }
        .btn-subscribe:hover { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4); }
        .modal-backdrop { backdrop-filter: blur(8px); background: rgba(15, 23, 42, 0.7); }
        .form-input { transition: all 0.2s; border: 1px solid #cbd5e1; }
        .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); outline: none; }
        #processing-overlay { display: none; }
        #processing-overlay.active { display: flex; }
    </style></head><body class="text-slate-800 flex flex-col min-h-screen"><!-- Overlay de Procesamiento --><div id="processing-overlay" class="fixed inset-0 z-[100] bg-slate-900/90 backdrop-blur-sm flex-col items-center justify-center text-white"><div class="w-16 h-16 border-4 border-amber-500 border-t-transparent rounded-full animate-spin mb-6"></div><h3 class="text-2xl font-bold mb-2">Procesando tu suscripción...</h3><p class="text-slate-300 text-sm animate-pulse" id="processing-text">Conectando con el banco</p></div><?php if (is_file(EMX_VIEWS_PATH . '/components/navbar.php')) include EMX_VIEWS_PATH . '/components/navbar.php'; ?><main class="flex-grow"><!-- Hero Section --><section class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 text-white py-20 relative overflow-hidden"><div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] opacity-30"></div><div class="max-w-7xl mx-auto px-4 text-center relative z-10"><span class="inline-block px-4 py-1.5 bg-blue-500/20 border border-blue-400/30 rounded-full text-sm font-semibold text-blue-200 mb-6 backdrop-blur-sm"><i class="fas fa-crown mr-2"></i>Membresías Exclusivas
                </span><h1 class="text-4xl md:text-6xl font-extrabold mb-6 tracking-tight">Elige el plan perfecto para ti</h1><p class="text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed">Desbloquea beneficios exclusivos, descuentos increíbles y una experiencia de compra premium diseñada para ti.</p></div></section><!-- Notificaciones --><?php if (isset($_GET['msg'])): ?><div class="max-w-7xl mx-auto px-4 mt-8"><div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm"><i class="fas fa-check-circle text-xl"></i><span class="font-medium"><?= htmlspecialchars($_GET['msg']) ?></span></div></div><?php endif; ?><?php if ($error_msg): ?><div class="max-w-7xl mx-auto px-4 mt-8"><div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3 shadow-sm"><i class="fas fa-exclamation-circle text-xl"></i><span class="font-medium"><?= htmlspecialchars($error_msg) ?></span></div></div><?php endif; ?><!-- Plan Actual del Usuario --><?php if ($plan_actual_usuario && $plan_actual_usuario['plan_activo']): ?><div class="max-w-7xl mx-auto px-4 mt-12"><div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-6 md:p-8 flex flex-col md:flex-row items-center justify-between shadow-sm"><div class="mb-4 md:mb-0"><p class="text-sm text-amber-800 font-semibold uppercase tracking-wide mb-1">Tu plan actual</p><h3 class="text-2xl md:text-3xl font-bold text-amber-900 flex items-center gap-2"><?= htmlspecialchars($plan_actual_usuario['plan_nombre']) ?><?php if ($plan_actual_usuario['es_prime']): ?><i class="fas fa-crown text-amber-500 text-xl"></i><?php endif; ?></h3><p class="text-sm text-amber-700 mt-2 flex items-center gap-2"><i class="fas fa-calendar-alt"></i>Renueva el: <?= date('d/m/Y', strtotime($plan_actual_usuario['plan_expira_en'])) ?></p></div><div class="text-center md:text-right"><p class="text-4xl font-extrabold text-amber-900">$<?= number_format($plan_actual_usuario['precio_mensual'], 2) ?></p><p class="text-sm text-amber-700 font-medium">por mes</p></div></div></div><?php endif; ?><!-- Planes --><section class="max-w-7xl mx-auto px-4 py-16"><div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start"><?php foreach ($planes as $plan): 
                    $beneficios = json_decode($plan['beneficios'], true) ?? [];
                    $es_mas_popular = ($plan['id'] == $plan_mas_popular_id);
                ?><div class="plan-card rounded-3xl shadow-lg overflow-hidden relative <?= $es_mas_popular ? 'featured' : '' ?>"><?php if ($es_mas_popular): ?><div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-amber-400 to-orange-500 text-white text-center py-2 text-xs font-bold uppercase tracking-wider shadow-md z-10"><i class="fas fa-fire mr-1"></i>El más elegido por nuestros usuarios
                            </div><?php endif; ?><div class="p-8 <?= $es_mas_popular ? 'pt-12' : '' ?>"><div class="flex items-center justify-between mb-4"><h3 class="text-2xl font-bold text-slate-900"><?= htmlspecialchars($plan['nombre']) ?></h3><?php if ($plan['es_prime']): ?><div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center"><i class="fas fa-crown text-amber-600"></i></div><?php endif; ?></div><p class="text-slate-500 mb-6 text-sm leading-relaxed"><?= htmlspecialchars($plan['descripcion']) ?></p><div class="mb-8 pb-8 border-b border-slate-100"><span class="text-5xl font-extrabold text-slate-900">$<?= number_format($plan['precio_mensual'], 2) ?></span><span class="text-slate-500 font-medium">/mes</span></div><ul class="space-y-4 mb-8"><?php foreach ($beneficios as $beneficio): ?><li class="flex items-start gap-3"><div class="mt-0.5 w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-emerald-600 text-xs"></i></div><span class="text-slate-700 text-sm font-medium"><?= htmlspecialchars($beneficio['beneficio'] ?? $beneficio['descripcion'] ?? 'Beneficio') ?></span></li><?php endforeach; ?></ul><button onclick="abrirModalPago('<?= $plan['id'] ?>', '<?= htmlspecialchars($plan['nombre']) ?>', <?= $plan['precio_mensual'] ?>)" 
                                    class="btn-subscribe w-full py-3.5 rounded-xl font-bold text-slate-900 flex items-center justify-center gap-2 shadow-lg text-sm uppercase tracking-wide"><?= ($plan_actual_usuario && $plan_actual_usuario['plan_id'] == $plan['id']) ? '<i class="fas fa-check"></i>Plan Actual' : '<i class="fas fa-bolt"></i>Suscribirse Ahora' ?></button><?php if ($es_mas_popular && $max_suscriptores >0): ?><p class="text-center text-xs text-slate-400 mt-4"><i class="fas fa-users mr-1"></i>+<?= $max_suscriptores ?>usuarios activos
                                </p><?php endif; ?></div></div><?php endforeach; ?></div></section></main><!-- Modal de Pago --><div id="modal-pago" class="fixed inset-0 z-50 modal-backdrop hidden flex items-center justify-center p-4"><div class="bg-white rounded-3xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto transform transition-all scale-100"><div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-3xl"><h3 class="text-xl font-bold text-slate-900">Finalizar Suscripción</h3><button onclick="cerrarModalPago()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition"><i class="fas fa-times text-lg"></i></button></div><form id="form-pago" method="POST" action="planes.php" class="p-6 space-y-5"><?= emxCsrfCampo() ?><input type="hidden" name="suscribir" value="1"><input type="hidden" name="plan_id" id="input-plan-id"><div class="bg-blue-50 border border-blue-100 rounded-xl p-4"><p class="text-xs text-blue-600 font-semibold uppercase tracking-wide mb-1">Plan seleccionado</p><p class="text-lg font-bold text-blue-900" id="modal-plan-nombre"></p><p class="text-2xl font-extrabold text-blue-900 mt-1">$<span id="modal-plan-precio"></span><span class="text-sm font-normal text-blue-700">/mes</span></p></div><!-- Checkbox de Prueba Gratuita --><div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4"><input type="checkbox" name="es_prueba" id="es_prueba" value="1" class="mt-1 w-4 h-4 text-amber-600 rounded border-amber-300 focus:ring-amber-500" checked><label for="es_prueba" class="text-xs text-amber-900 leading-tight cursor-pointer"><strong>¡Sí, quiero mis 7 días de prueba gratis!</strong><br>No se realizará ningún cargo hoy. Al finalizar el período de prueba, se procesará el cobro simulado de <span id="modal-precio-cobro"></span>automáticamente. Podrás cancelar en cualquier momento desde tu cuenta.
                    </label></div><div><label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Nombre en la Tarjeta</label><input type="text" name="card_name" required class="form-input w-full rounded-xl px-4 py-3 text-sm uppercase font-medium" placeholder="JUAN PEREZ"></div><div><label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Número de Tarjeta</label><div class="relative"><input type="text" id="card_number" name="card_number" required maxlength="19" class="form-input w-full rounded-xl pl-12 pr-12 py-3 text-sm font-mono tracking-widest font-semibold" placeholder="0000 0000 0000 0000"><i class="fas fa-credit-card absolute left-4 top-3.5 text-slate-400 text-lg"></i><i id="card-brand-icon" class="fab absolute right-4 top-3 text-2xl text-slate-300"></i></div><p id="card-error" class="text-xs text-red-500 mt-1.5 hidden font-medium"><i class="fas fa-exclamation-triangle mr-1"></i>Número de tarjeta inválido</p></div><div class="grid grid-cols-2 gap-4"><div><label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Expiración</label><input type="text" id="card_expiry" name="card_expiry" required maxlength="5" class="form-input w-full rounded-xl px-4 py-3 text-sm font-mono font-semibold text-center" placeholder="MM/AA"></div><div><label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">CVV</label><input type="password" id="card_cvv" name="card_cvv" required maxlength="4" class="form-input w-full rounded-xl px-4 py-3 text-sm font-mono font-semibold text-center" placeholder="123"></div></div><div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center gap-3"><i class="fas fa-lock text-emerald-500 text-lg"></i><p class="text-xs text-slate-600 leading-tight">Pago seguro con encriptación SSL de 256 bits. No almacenamos los datos de tu tarjeta.</p></div><button type="submit" class="btn-subscribe w-full py-4 rounded-xl font-bold text-slate-900 flex items-center justify-center gap-2 shadow-lg text-sm uppercase tracking-wide mt-2"><i class="fas fa-lock"></i>Confirmar Suscripción
                </button></form></div></div><?php if (is_file(EMX_VIEWS_PATH . '/components/footer.php')) include EMX_VIEWS_PATH . '/components/footer.php'; ?><script>function abrirModalPago(planId, planNombre, planPrecio) {
            document.getElementById('input-plan-id').value = planId;
            document.getElementById('modal-plan-nombre').textContent = planNombre;
            document.getElementById('modal-plan-precio').textContent = planPrecio.toFixed(2);
            document.getElementById('modal-precio-cobro').textContent = '$' + planPrecio.toFixed(2);
            document.getElementById('modal-pago').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalPago() {
            document.getElementById('modal-pago').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('form-pago').reset();
            document.getElementById('card-error').classList.add('hidden');
            document.getElementById('card_number').classList.remove('border-red-500', 'ring-2', 'ring-red-100');
        }

        document.getElementById('card_number').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            let formatted = '';
            let icon = document.getElementById('card-brand-icon');
            
            icon.className = 'fab absolute right-4 top-3 text-2xl text-slate-300';
            if (value.startsWith('4')) icon.classList.add('fa-cc-visa', 'text-blue-700');
            else if (value.startsWith('5')) icon.classList.add('fa-cc-mastercard', 'text-red-600');
            else if (value.startsWith('3')) icon.classList.add('fa-cc-amex', 'text-blue-500');
            else icon.classList.add('fa-credit-card');
            
            for (let i = 0; i < value.length; i++) {
                if (i >0 && i % 4 === 0) formatted += ' ';
                formatted += value[i];
            }
            e.target.value = formatted;
            
            if (e.target.value.length >10) {
                document.getElementById('card-error').classList.add('hidden');
                e.target.classList.remove('border-red-500', 'ring-2', 'ring-red-100');
            }
        });

        document.getElementById('card_expiry').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) value = value.substring(0, 2) + '/' + value.substring(2, 4);
            e.target.value = value;
        });

        document.getElementById('form-pago').addEventListener('submit', function(e) {
            const cardNum = document.getElementById('card_number').value.replace(/\s/g, '');
            if (!validarLuhnJS(cardNum) || cardNum.length < 13) {
                e.preventDefault();
                document.getElementById('card_number').classList.add('border-red-500', 'ring-2', 'ring-red-100');
                document.getElementById('card-error').classList.remove('hidden');
                return;
            }
            
            const overlay = document.getElementById('processing-overlay');
            const texts = ["Conectando con el banco...", "Verificando fondos...", "Procesando suscripción...", "¡Suscripción exitosa!"];
            overlay.classList.add('active');
            let step = 0;
            
            const interval = setInterval(() =>{
                document.getElementById('processing-text').textContent = texts[step];
                step++;
                if (step >= texts.length) {
                    clearInterval(interval);
                }
            }, 700);
        });

        function validarLuhnJS(numero) {
            let suma = 0; 
            let paridad = (numero.length % 2);
            for (let i = 0; i < numero.length; i++) {
                let digito = parseInt(numero[i]);
                if (i % 2 === paridad) { 
                    digito *= 2; 
                    if (digito >9) digito -= 9; 
                }
                suma += digito;
            }
            return (suma % 10 === 0);
        }
        
        document.getElementById('modal-pago').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalPago();
        });
    </script><script src="assets/emx_modales.js"></script></body></html>