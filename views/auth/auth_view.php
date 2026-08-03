<?php
/**
 * Vista separada de `auth.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `auth.php`.
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
<title><?= $action === 'registro' ? 'Crear cuenta' : 'Iniciar sesión' ?> | ElectroMax</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<?php if (function_exists('emxGoogleActivo') && emxGoogleActivo()): ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<?php endif; ?>
<style>
*{font-family:Inter,system-ui,sans-serif}
:root{
    --emx-navy:#0f172a;
    --emx-blue:#2563eb;
    --emx-sky:#0ea5e9;
    --emx-amber:#f59e0b;
    --emx-emerald:#059669;
}
body{
    background:
        radial-gradient(circle at 8% 10%, rgba(14,165,233,.18), transparent 26%),
        radial-gradient(circle at 90% 18%, rgba(245,158,11,.15), transparent 28%),
        radial-gradient(circle at 52% 96%, rgba(5,150,105,.12), transparent 32%),
        linear-gradient(135deg,#f8fafc 0%,#eef6ff 48%,#fff7ed 100%);
}
.auth-shell{
    background:rgba(255,255,255,.92);
    border:1px solid rgba(226,232,240,.9);
    box-shadow:0 30px 90px rgba(15,23,42,.14);
}
.brand-panel{
    background:
        linear-gradient(145deg,rgba(255,255,255,.98),rgba(239,246,255,.94) 58%,rgba(255,251,235,.86));
    color:#0f172a;
    position:relative;
}
.brand-panel:before{
    content:"";
    position:absolute;
    inset:0;
    background:
        linear-gradient(135deg,transparent 0%,transparent 58%,rgba(37,99,235,.08) 58%,transparent 63%),
        linear-gradient(135deg,transparent 0%,transparent 70%,rgba(245,158,11,.10) 70%,transparent 75%);
    pointer-events:none;
}
.logo-plate{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    background:#ffffff;
    border:1px solid rgba(226,232,240,.95);
    box-shadow:0 18px 40px rgba(15,23,42,.10);
    border-radius:28px;
    padding:18px 22px;
}
.feature-card{
    background:rgba(255,255,255,.78);
    border:1px solid rgba(226,232,240,.85);
    box-shadow:0 14px 30px rgba(15,23,42,.07);
}
.auth-input{
    border:1px solid #dbe4ef;
    background:#f8fafc;
    transition:.2s;
}
.auth-input:focus{
    background:#fff;
    border-color:var(--emx-blue);
    box-shadow:0 0 0 4px rgba(37,99,235,.12);
    outline:none;
}
.auth-tab{transition:.2s}
.auth-tab-active{
    background:#ffffff;
    color:#0f172a;
    box-shadow:0 10px 24px rgba(15,23,42,.10);
    border:1px solid rgba(226,232,240,.9);
}
.auth-tab-inactive{background:transparent;color:#64748b}
.auth-kicker{color:#2563eb}
.btn-auth{
    background:linear-gradient(135deg,#2563eb 0%,#0f766e 100%);
    color:white;
    box-shadow:0 18px 32px rgba(37,99,235,.20);
}
.btn-auth:hover{
    filter:saturate(1.08) brightness(.98);
    transform:translateY(-1px);
}
.back-link{
    background:#fff;
    color:#475569;
    border:1px solid #e2e8f0;
    box-shadow:0 8px 20px rgba(15,23,42,.06);
}
.back-link:hover{background:#f8fafc;color:#0f172a}
</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-0 auth-shell rounded-[2rem] overflow-hidden">
    <section class="brand-panel relative hidden lg:flex flex-col justify-between p-10 min-h-[720px]">
        <div class="absolute inset-0 opacity-100"></div>
        <div class="relative">
            <a href="index.php" class="logo-plate">
                <img src="assets/electromax_logo.png" alt="ElectroMax" class="h-16 w-auto max-w-[285px] object-contain">
            </a>
            <h1 class="text-4xl font-black mt-12 leading-tight text-slate-950">Tu cuenta ElectroMax en un solo lugar.</h1>
            <p class="text-slate-600 mt-4 leading-relaxed max-w-md">Compra, revisa pedidos, gestiona devoluciones, garantías, soporte y beneficios Prime desde Babahoyo, Ecuador.</p>
        </div>
        <div class="relative grid grid-cols-2 gap-4">
            <div class="feature-card rounded-2xl p-4"><i class="fas fa-shield-halved text-blue-600 mb-3"></i><p class="font-bold">Compra segura</p><p class="text-xs text-slate-500 mt-1">Acceso protegido y pedidos trazables.</p></div>
            <div class="feature-card rounded-2xl p-4"><i class="fas fa-truck-fast text-emerald-600 mb-3"></i><p class="font-bold">Seguimiento</p><p class="text-xs text-slate-500 mt-1">Consulta el estado de tus entregas.</p></div>
            <div class="feature-card rounded-2xl p-4"><i class="fas fa-rotate-left text-amber-500 mb-3"></i><p class="font-bold">Devoluciones</p><p class="text-xs text-slate-500 mt-1">Solicitudes organizadas por flujo.</p></div>
            <div class="feature-card rounded-2xl p-4"><i class="fas fa-headset text-sky-600 mb-3"></i><p class="font-bold">Soporte</p><p class="text-xs text-slate-500 mt-1">Comunicación directa con la empresa.</p></div>
        </div>
    </section>

    <section class="p-6 sm:p-10 bg-white">
        <div class="flex items-center justify-between gap-4 mb-8">
            <a href="index.php" class="lg:hidden inline-flex items-center rounded-2xl bg-white border border-slate-200 shadow-sm px-3 py-2"><img src="assets/electromax_logo.png" alt="ElectroMax" class="h-11 w-auto"></a>
            <a href="index.php" class="back-link ml-auto inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold">
                <i class="fas fa-arrow-left"></i>Volver a tienda
            </a>
        </div>

        <div class="max-w-md mx-auto">
            <div class="grid grid-cols-2 gap-2 p-1 rounded-2xl bg-slate-100/80 border border-slate-200 mb-8">
                <a href="?action=login" class="auth-tab text-center rounded-xl py-3 text-sm font-black <?= $action === 'login' ? 'auth-tab-active' : 'auth-tab-inactive' ?>">
                    <i class="fas fa-right-to-bracket mr-2"></i>Iniciar sesión
                </a>
                <a href="?action=registro" class="auth-tab text-center rounded-xl py-3 text-sm font-black <?= $action === 'registro' ? 'auth-tab-active' : 'auth-tab-inactive' ?>">
                    <i class="fas fa-user-plus mr-2"></i>Crear cuenta
                </a>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 rounded-2xl border p-4 text-sm <?= ($msg_type ?? 'error') === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700' ?>">
                    <i class="fas <?= ($msg_type ?? 'error') === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> mr-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($action === 'login'): ?>
                <div class="mb-7">
                    <p class="text-xs font-black tracking-[.25em] auth-kicker uppercase">Bienvenido</p>
                    <h2 class="text-3xl font-black text-slate-900 mt-2">Inicia sesión</h2>
                    <p class="text-slate-500 text-sm mt-2">Accede a tus pedidos, soporte, devoluciones y garantías.</p>
                </div>

                <?php if (function_exists('emxGoogleActivo') && emxGoogleActivo()): ?>
                    <div class="mb-6">
                        <form id="googleLoginForm" method="POST" action="google_auth.php?action=login" class="hidden">
                            <?= emxCsrfCampo() ?>
                            <input type="hidden" name="credential" id="googleCredentialInput">
                        </form>
                        <div id="g_id_onload" data-client_id="<?= htmlspecialchars(emxGoogleClientId(), ENT_QUOTES, 'UTF-8') ?>" data-callback="emxHandleGoogleCredential" data-auto_prompt="false"></div>
                        <div class="flex justify-center"><div class="g_id_signin" data-type="standard" data-shape="pill" data-theme="outline" data-text="continue_with" data-size="large" data-logo_alignment="left" data-width="360"></div></div>
                    </div>
                    <div class="relative my-6"><div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div><div class="relative flex justify-center text-xs"><span class="bg-white px-3 text-slate-400 font-bold uppercase tracking-widest">o con correo</span></div></div>
                <?php endif; ?>

                <form method="POST" action="?action=login" class="space-y-5">
                    <?= emxCsrfCampo() ?>
                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-2">Correo electrónico</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="email" name="email" required class="auth-input w-full rounded-2xl pl-11 pr-4 py-3 text-sm" placeholder="tu@correo.com">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-2">Contraseña</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" name="password" id="login-password" required class="auth-input w-full rounded-2xl pl-11 pr-12 py-3 text-sm" placeholder="••••••••">
                            <button type="button" onclick="togglePassword('login-password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="btn-auth w-full py-3.5 rounded-2xl font-black transition">Ingresar a mi cuenta</button>
                </form>
            <?php else: ?>
                <div class="mb-7">
                    <p class="text-xs font-black tracking-[.25em] auth-kicker uppercase">Nueva cuenta</p>
                    <h2 class="text-3xl font-black text-slate-900 mt-2">Crear cuenta</h2>
                    <p class="text-slate-500 text-sm mt-2">Regístrate para comprar y gestionar tus pedidos.</p>
                </div>

                <?php if (function_exists('emxGoogleActivo') && emxGoogleActivo()): ?>
                    <div class="mb-6">
                        <form id="googleLoginForm" method="POST" action="google_auth.php?action=registro" class="hidden">
                            <?= emxCsrfCampo() ?>
                            <input type="hidden" name="credential" id="googleCredentialInput">
                        </form>
                        <div id="g_id_onload" data-client_id="<?= htmlspecialchars(emxGoogleClientId(), ENT_QUOTES, 'UTF-8') ?>" data-callback="emxHandleGoogleCredential" data-auto_prompt="false"></div>
                        <div class="flex justify-center"><div class="g_id_signin" data-type="standard" data-shape="pill" data-theme="outline" data-text="signup_with" data-size="large" data-logo_alignment="left" data-width="360"></div></div>
                    </div>
                    <div class="relative my-6"><div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div><div class="relative flex justify-center text-xs"><span class="bg-white px-3 text-slate-400 font-bold uppercase tracking-widest">o llena tus datos</span></div></div>
                <?php endif; ?>

                <form method="POST" action="?action=registro" class="space-y-4">
                    <?= emxCsrfCampo() ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-2">Nombres *</label>
                            <input type="text" name="nombres" required class="auth-input w-full rounded-2xl px-4 py-3 text-sm" placeholder="Ander">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-2">Apellidos *</label>
                            <input type="text" name="apellidos" required class="auth-input w-full rounded-2xl px-4 py-3 text-sm" placeholder="Bustamante">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-2">Correo electrónico *</label>
                        <input type="email" name="email" required class="auth-input w-full rounded-2xl px-4 py-3 text-sm" placeholder="tu@correo.com">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-2">Cédula / RUC</label>
                            <input type="text" name="cedula_ruc" class="auth-input w-full rounded-2xl px-4 py-3 text-sm" placeholder="Opcional">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-2">Teléfono</label>
                            <input type="tel" name="telefono" class="auth-input w-full rounded-2xl px-4 py-3 text-sm" placeholder="Opcional">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-2">Contraseña *</label>
                        <div class="relative">
                            <input type="password" name="password" id="reg-password" required minlength="6" class="auth-input w-full rounded-2xl px-4 pr-12 py-3 text-sm" placeholder="Mínimo 6 caracteres">
                            <button type="button" onclick="togglePassword('reg-password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="btn-auth w-full py-3.5 rounded-2xl font-black transition">Crear mi cuenta</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
function emxHandleGoogleCredential(response) {
    const input = document.getElementById('googleCredentialInput');
    const form = document.getElementById('googleLoginForm');
    if (!input || !form || !response || !response.credential) return;
    input.value = response.credential;
    form.submit();
}
</script>
<script src="assets/emx_modales.js"></script>
</body>
</html>
