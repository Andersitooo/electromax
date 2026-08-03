<?php
/**
 * Vista separada de `soporte_admin.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `soporte_admin.php`.
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
<title>Soporte Admin | ElectroMax</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>*{font-family:Inter,system-ui,sans-serif}.sidebar{background:#0f172a}.panel{background:white;border:1px solid #e2e8f0;box-shadow:0 10px 28px rgba(15,23,42,.05)}.active{background:rgba(59,130,246,.18);color:#fff}</style>
</head>
<body class="bg-slate-100 min-h-screen flex">
<aside class="sidebar w-64 text-white flex flex-col shadow-2xl flex-shrink-0 h-screen sticky top-0">
    <div class="p-6 border-b border-slate-700/40">
        <a href="admin.php" class="block"><img src="assets/electromax_logo.png" alt="ElectroMax" class="h-14 w-auto max-w-[210px] object-contain"><p class="mt-3 text-[10px] uppercase tracking-[.24em] text-slate-300 font-black">Panel administrativo</p></a>
    </div>
    <nav class="flex-1 p-4 space-y-1">
        <a href="admin.php?module=dashboard" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-white/10"><i class="fas fa-chart-line w-5"></i>Dashboard</a>
        <a href="admin.php?module=pedidos" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-white/10"><i class="fas fa-shopping-bag w-5"></i>Pedidos</a>
        <a href="admin.php?module=devoluciones" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-white/10"><i class="fas fa-undo w-5"></i>Devoluciones</a>
        <a href="admin.php?module=garantias" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-white/10"><i class="fas fa-shield-halved w-5"></i>Garantías</a>
        <a href="soporte_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-lg active"><i class="fas fa-headset w-5"></i>Soporte</a>
        <a href="admin.php?module=productos" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-white/10"><i class="fas fa-box w-5"></i>Productos</a>
        <a href="admin.php?module=producto_proveedores" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-white/10"><i class="fas fa-truck-loading w-5"></i>Proveedores</a>
        <a href="analitica.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-white/10"><i class="fas fa-chart-pie w-5"></i>Analítica</a>
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-white/10 mt-8"><i class="fas fa-store w-5"></i>Ver tienda</a>
        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-400 hover:bg-red-900/20"><i class="fas fa-sign-out-alt w-5"></i>Cerrar sesión</a>
    </nav>
</aside>

<main class="flex-1 overflow-y-auto">
    <header class="bg-white border-b border-slate-200 px-8 py-5">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Soporte / Tickets</h1>
                <p class="text-sm text-slate-500">Consulta y responde solicitudes de clientes.</p>
            </div>
            <a href="admin.php" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-sm"><i class="fas fa-arrow-left mr-2"></i>Volver al admin</a>
        </div>
    </header>

    <div class="p-8">
        <?php if (!$tablasOk): ?>
            <div class="panel rounded-2xl p-6 border-amber-200 bg-amber-50">
                <h2 class="font-black text-amber-900">Falta activar soporte</h2>
                <p class="text-amber-800 text-sm mt-2">Ejecuta <strong>migracion_soporte_tickets.sql</strong> en PostgreSQL.</p>
            </div>
        <?php else: ?>

        <?php if ($msg): ?>
            <div class="mb-5 rounded-2xl border p-4 <?= $msgType === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' ?>">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="panel rounded-2xl p-5"><p class="text-xs font-black text-slate-500 uppercase">Abiertos</p><p class="text-3xl font-black text-blue-700 mt-1"><?= $stats['abiertos'] ?></p></div>
            <div class="panel rounded-2xl p-5"><p class="text-xs font-black text-slate-500 uppercase">En revisión</p><p class="text-3xl font-black text-amber-700 mt-1"><?= $stats['revision'] ?></p></div>
            <div class="panel rounded-2xl p-5"><p class="text-xs font-black text-slate-500 uppercase">Respondidos</p><p class="text-3xl font-black text-emerald-700 mt-1"><?= $stats['respondidos'] ?></p></div>
            <div class="panel rounded-2xl p-5"><p class="text-xs font-black text-slate-500 uppercase">Cerrados</p><p class="text-3xl font-black text-slate-700 mt-1"><?= $stats['cerrados'] ?></p></div>
        </div>

        <form method="GET" class="panel rounded-2xl p-4 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar cliente, correo o asunto..." class="rounded-xl border border-slate-200 px-3 py-2.5">
            <select name="estado" class="rounded-xl border border-slate-200 px-3 py-2.5">
                <option value="">Todos los estados</option>
                <?php foreach ($estados as $k=>$v): ?><option value="<?= $k ?>" <?= $fEstado===$k?'selected':'' ?>><?= htmlspecialchars($v) ?></option><?php endforeach; ?>
            </select>
            <select name="motivo" class="rounded-xl border border-slate-200 px-3 py-2.5">
                <option value="">Todos los motivos</option>
                <?php foreach ($motivos as $k=>$v): ?><option value="<?= $k ?>" <?= $fMotivo===$k?'selected':'' ?>><?= htmlspecialchars($v) ?></option><?php endforeach; ?>
            </select>
            <button class="rounded-xl bg-slate-900 text-white font-black px-4 py-2.5">Filtrar</button>
        </form>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <section class="xl:col-span-1 panel rounded-2xl p-5">
                <h2 class="font-black text-slate-900 mb-4">Tickets</h2>
                <div class="space-y-3 max-h-[760px] overflow-y-auto pr-1">
                    <?php if (empty($tickets)): ?>
                        <p class="text-sm text-slate-500">No hay tickets con esos filtros.</p>
                    <?php endif; ?>
                    <?php foreach ($tickets as $t): ?>
                        <a href="soporte_admin.php?ticket=<?= urlencode($t['id']) ?>" class="block rounded-xl border border-slate-200 p-4 hover:border-blue-300 hover:bg-blue-50/40 transition <?= $ticketActual && $ticketActual['id'] === $t['id'] ? 'ring-2 ring-blue-500/20 border-blue-300' : '' ?>">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-black text-slate-900">#<?= emxSoporteCodigo($t['id']) ?></p>
                                    <p class="text-sm font-semibold text-slate-700 line-clamp-1"><?= htmlspecialchars($t['asunto']) ?></p>
                                    <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($t['nombres'] . ' ' . $t['apellidos']) ?></p>
                                </div>
                                <span class="text-[10px] font-black px-2 py-1 rounded-lg border <?= emxSoportePrioridadClase($t['prioridad']) ?>"><?= htmlspecialchars(emxSoporteLabel($prioridades, $t['prioridad'])) ?></span>
                            </div>
                            <div class="flex items-center gap-2 mt-3">
                                <span class="text-[10px] font-black px-2 py-1 rounded-lg border <?= emxSoporteEstadoClase($t['estado']) ?>"><?= htmlspecialchars(emxSoporteLabel($estados, $t['estado'])) ?></span>
                                <span class="text-[10px] text-slate-400"><?= date('d/m H:i', strtotime($t['updated_at'])) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="xl:col-span-2">
                <?php if (!$ticketActual): ?>
                    <div class="panel rounded-2xl p-10 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4"><i class="fas fa-headset text-2xl"></i></div>
                        <h2 class="text-xl font-black text-slate-900">Selecciona un ticket</h2>
                        <p class="text-slate-500 mt-2">Aquí podrás responder y cerrar casos de soporte.</p>
                    </div>
                <?php else: ?>
                    <div class="panel rounded-2xl overflow-hidden">
                        <div class="p-6 border-b border-slate-100">
                            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black text-blue-600 uppercase tracking-widest">Ticket #<?= emxSoporteCodigo($ticketActual['id']) ?></p>
                                    <h2 class="text-2xl font-black text-slate-900 mt-1"><?= htmlspecialchars($ticketActual['asunto']) ?></h2>
                                    <p class="text-sm text-slate-500 mt-2">
                                        Cliente: <strong><?= htmlspecialchars($ticketActual['nombres'] . ' ' . $ticketActual['apellidos']) ?></strong> · <?= htmlspecialchars($ticketActual['email']) ?>
                                    </p>
                                    <?php if (!empty($ticketActual['pedido_id'])): ?>
                                        <p class="text-sm text-slate-500 mt-1">Pedido relacionado: <strong>#<?= emxSoporteCodigo($ticketActual['pedido_id']) ?></strong> · <?= htmlspecialchars($ticketActual['pedido_estado'] ?? '') ?> · $<?= number_format((float)($ticketActual['pedido_total'] ?? 0),2) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="text-xs font-black px-3 py-1 rounded-lg border <?= emxSoporteEstadoClase($ticketActual['estado']) ?>"><?= htmlspecialchars(emxSoporteLabel($estados, $ticketActual['estado'])) ?></span>
                                    <span class="text-xs font-black px-3 py-1 rounded-lg border <?= emxSoportePrioridadClase($ticketActual['prioridad']) ?>">Prioridad <?= htmlspecialchars(emxSoporteLabel($prioridades, $ticketActual['prioridad'])) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-slate-50 space-y-4">
                            <?php foreach ($mensajes as $m): $isAdmin = $m['enviado_por'] === 'admin'; ?>
                                <div class="flex <?= $isAdmin ? 'justify-end' : 'justify-start' ?>">
                                    <div class="max-w-[85%] rounded-2xl p-4 border <?= $isAdmin ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-200' ?>">
                                        <p class="text-xs font-black uppercase tracking-wide mb-2 <?= $isAdmin ? 'text-slate-300' : 'text-slate-400' ?>">
                                            <?= $isAdmin ? 'Admin' : 'Cliente' ?> · <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
                                        </p>
                                        <p class="whitespace-pre-wrap text-sm leading-relaxed"><?= htmlspecialchars($m['mensaje']) ?></p>
                                        <?php if (!empty($m['adjunto_url'])): ?>
                                            <a href="<?= htmlspecialchars($m['adjunto_url']) ?>" target="_blank" class="inline-flex items-center gap-2 mt-3 text-xs font-bold underline"><i class="fas fa-paperclip"></i>Ver adjunto</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="p-6 bg-white border-t border-slate-100">
                            <form method="POST" class="space-y-4">
                                <?= emxCsrfCampo() ?>
                                <input type="hidden" name="accion" value="responder">
                                <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticketActual['id']) ?>">
                                <div>
                                    <label class="block text-xs font-black text-slate-500 uppercase mb-1">Respuesta al cliente</label>
                                    <textarea name="mensaje" rows="4" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5" placeholder="Escribe la respuesta..."></textarea>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
                                    <select name="estado" class="rounded-xl border border-slate-200 px-3 py-2.5">
                                        <option value="respondido">Respondido</option>
                                        <option value="esperando_cliente">Esperando cliente</option>
                                        <option value="en_revision">En revisión</option>
                                        <option value="cerrado">Cerrar ticket</option>
                                    </select>
                                    <button class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black px-5 py-2.5">Enviar respuesta</button>
                                </div>
                            </form>

                            <form method="POST" class="mt-3 flex gap-2">
                                <?= emxCsrfCampo() ?>
                                <input type="hidden" name="accion" value="cambiar_estado">
                                <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticketActual['id']) ?>">
                                <select name="estado" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <?php foreach ($estados as $k=>$v): ?><option value="<?= $k ?>" <?= $ticketActual['estado']===$k?'selected':'' ?>><?= htmlspecialchars($v) ?></option><?php endforeach; ?>
                                </select>
                                <button class="rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2 text-sm">Cambiar estado</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
        <?php endif; ?>
    </div>
</main>
<script src="assets/emx_modales.js"></script>
</body>
</html>
