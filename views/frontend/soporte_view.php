<?php
/**
 * Vista separada de `soporte.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `soporte.php`.
 *
 * Las variables usadas aquí vienen del controlador raíz por compatibilidad.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<!-- Favicon ElectroMax global -->
<link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
<link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
<link rel="manifest" href="assets/favicon/site.webmanifest">
<meta name="theme-color" content="#0b4da2">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Centro de Soporte | ElectroMax</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>*{font-family:Inter,system-ui,sans-serif}.panel{background:white;border:1px solid #e2e8f0;box-shadow:0 10px 30px rgba(15,23,42,.05)}</style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">
<?php require EMX_VIEWS_PATH . '/components/navbar.php'; ?>

<main class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 flex-grow">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-black tracking-widest text-blue-600 uppercase">ElectroMax</p>
            <h1 class="text-3xl font-black text-slate-900 mt-1">Centro de Soporte</h1>
            <p class="text-slate-500 mt-1">Comunícate con la empresa por pedidos, pagos, entregas, devoluciones, garantías o consultas generales.</p>
        </div>
        <a href="mi_cuenta.php" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold hover:bg-slate-100 transition">
            <i class="fas fa-user mr-2"></i>Mi cuenta
        </a>
    </div>

    <?php if (!$tablasOk): ?>
        <div class="panel rounded-2xl p-6 border-amber-200 bg-amber-50">
            <h2 class="font-black text-amber-900">Falta activar el módulo de soporte</h2>
            <p class="text-amber-800 text-sm mt-2">Ejecuta la migración <strong>migracion_soporte_tickets.sql</strong> en PostgreSQL.</p>
        </div>
    <?php else: ?>

    <?php if ($msg): ?>
        <div class="mb-5 rounded-2xl border p-4 <?= $msgType === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section class="xl:col-span-1 space-y-6">
            <div class="panel rounded-2xl p-6">
                <h2 class="font-black text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-headset text-blue-600"></i>Nuevo ticket</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?= emxCsrfCampo() ?>
                    <input type="hidden" name="accion" value="crear_ticket">

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-1">Motivo</label>
                        <select name="motivo" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <?php foreach ($motivos as $k=>$v): ?>
                                <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-1">Pedido relacionado</label>
                        <select name="pedido_id" class="w-full rounded-xl border border-slate-200 px-3 py-2.5">
                            <option value="">Sin pedido específico</option>
                            <?php foreach ($pedidos as $p): ?>
                                <option value="<?= htmlspecialchars($p['id']) ?>">
                                    Pedido #<?= emxSoporteCodigo($p['id']) ?> · <?= date('d/m/Y', strtotime($p['created_at'])) ?> · $<?= number_format((float)$p['total'],2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-1">Asunto</label>
                        <input type="text" name="asunto" maxlength="160" required placeholder="Ej: Necesito ayuda con mi pedido" class="w-full rounded-xl border border-slate-200 px-3 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-1">Mensaje</label>
                        <textarea name="mensaje" rows="5" required placeholder="Describe tu consulta..." class="w-full rounded-xl border border-slate-200 px-3 py-2.5"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-1">Adjunto opcional</label>
                        <input type="file" name="adjunto" accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-bold">
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG, WEBP o PDF. Máximo 5 MB.</p>
                    </div>

                    <button class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black py-3 transition">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar solicitud
                    </button>
                </form>
            </div>

            <div class="panel rounded-2xl p-6">
                <h2 class="font-black text-slate-900 mb-4">Mis tickets</h2>
                <?php if (empty($tickets)): ?>
                    <p class="text-slate-500 text-sm">Aún no tienes tickets de soporte.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($tickets as $t): ?>
                            <a href="soporte.php?ticket=<?= urlencode($t['id']) ?>" class="block rounded-xl border border-slate-200 p-4 hover:border-blue-300 hover:bg-blue-50/40 transition <?= $ticketActual && $ticketActual['id'] === $t['id'] ? 'ring-2 ring-blue-500/20 border-blue-300' : '' ?>">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-black text-slate-900">#<?= emxSoporteCodigo($t['id']) ?></p>
                                        <p class="text-sm text-slate-600 line-clamp-1"><?= htmlspecialchars($t['asunto']) ?></p>
                                    </div>
                                    <span class="text-[11px] font-black px-2 py-1 rounded-lg border <?= emxSoporteEstadoClase($t['estado']) ?>"><?= htmlspecialchars(emxSoporteLabel($estados, $t['estado'])) ?></span>
                                </div>
                                <p class="text-xs text-slate-400 mt-2"><?= date('d/m/Y H:i', strtotime($t['updated_at'])) ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="xl:col-span-2">
            <?php if (!$ticketActual): ?>
                <div class="panel rounded-2xl p-10 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comments text-2xl"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-900">Selecciona un ticket o crea uno nuevo</h2>
                    <p class="text-slate-500 mt-2">Aquí verás la conversación con soporte.</p>
                </div>
            <?php else: ?>
                <div class="panel rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-slate-900 to-blue-900 text-white">
                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                            <div>
                                <p class="text-blue-200 text-xs font-black uppercase tracking-widest">Ticket #<?= emxSoporteCodigo($ticketActual['id']) ?></p>
                                <h2 class="text-2xl font-black mt-1"><?= htmlspecialchars($ticketActual['asunto']) ?></h2>
                                <p class="text-blue-100 text-sm mt-2"><?= htmlspecialchars(emxSoporteLabel($motivos, $ticketActual['motivo'])) ?></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="text-xs font-black px-3 py-1 rounded-full border bg-white/10 border-white/20"><?= htmlspecialchars(emxSoporteLabel($estados, $ticketActual['estado'])) ?></span>
                                <span class="text-xs font-black px-3 py-1 rounded-full border bg-white/10 border-white/20">Prioridad <?= htmlspecialchars(emxSoporteLabel($prioridades, $ticketActual['prioridad'])) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-4 bg-slate-50">
                        <?php foreach ($mensajes as $m): $isCliente = $m['enviado_por'] === 'cliente'; ?>
                            <div class="flex <?= $isCliente ? 'justify-end' : 'justify-start' ?>">
                                <div class="max-w-[85%] rounded-2xl p-4 border <?= $isCliente ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-200' ?>">
                                    <p class="text-xs font-black uppercase tracking-wide mb-2 <?= $isCliente ? 'text-blue-100' : 'text-slate-400' ?>">
                                        <?= $isCliente ? 'Tú' : 'Soporte ElectroMax' ?> · <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
                                    </p>
                                    <p class="whitespace-pre-wrap text-sm leading-relaxed"><?= htmlspecialchars($m['mensaje']) ?></p>
                                    <?php if (!empty($m['adjunto_url'])): ?>
                                        <a href="<?= htmlspecialchars($m['adjunto_url']) ?>" target="_blank" class="inline-flex items-center gap-2 mt-3 text-xs font-bold underline">
                                            <i class="fas fa-paperclip"></i>Ver adjunto
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="p-6 bg-white border-t border-slate-100">
                        <?php if ($ticketActual['estado'] === 'cerrado'): ?>
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-slate-600 text-sm">Este ticket está cerrado.</div>
                        <?php else: ?>
                            <form method="POST" enctype="multipart/form-data" class="space-y-3">
                                <?= emxCsrfCampo() ?>
                                <input type="hidden" name="accion" value="responder_ticket">
                                <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticketActual['id']) ?>">
                                <textarea name="mensaje" rows="3" required placeholder="Responder a soporte..." class="w-full rounded-xl border border-slate-200 px-3 py-2.5"></textarea>
                                <div class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
                                    <input type="file" name="adjunto" accept=".jpg,.jpeg,.png,.webp,.pdf" class="text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:font-bold">
                                    <div class="flex gap-2">
                                        <button class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black px-5 py-2.5 transition">Enviar respuesta</button>
                                    </div>
                                </div>
                            </form>
                            <form method="POST" class="mt-3">
                                <?= emxCsrfCampo() ?>
                                <input type="hidden" name="accion" value="cerrar_ticket">
                                <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticketActual['id']) ?>">
                                <button class="text-sm font-bold text-slate-500 hover:text-slate-800">Cerrar ticket porque ya fue resuelto</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <?php endif; ?>
</main>

<?php require EMX_VIEWS_PATH . '/components/footer.php'; ?>
<script src="assets/emx_modales.js"></script>
</body>
</html>
