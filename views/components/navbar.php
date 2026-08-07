<?php
/**
 * Componente visual separado.
 *
 * Fase 5:
 * El componente ahora vive en views/components.
 * La ruta components/ se conserva como adaptador para no romper includes.
 */
?>
<?php
// components/navbar.php - Navbar cliente unificado ElectroMax
if (session_status() === PHP_SESSION_NONE) session_start();

$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$isLogged = isset($_SESSION['usuario_id']);

if (!function_exists('emxNavTableExists')) {
    function emxNavTableExists($pdo, $table) {
        static $cache = [];
        if (array_key_exists($table, $cache)) return $cache[$table];
        try {
            $st = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name=? LIMIT 1");
            $st->execute([$table]);
            return $cache[$table] = (bool)$st->fetchColumn();
        } catch (Throwable $e) { return $cache[$table] = false; }
    }
}
if (!function_exists('emxNavColExists')) {
    function emxNavColExists($pdo, $table, $col) {
        static $cache = [];
        $key = $table.'.'.$col;
        if (array_key_exists($key, $cache)) return $cache[$key];
        try {
            $st = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=? AND column_name=? LIMIT 1");
            $st->execute([$table, $col]);
            return $cache[$key] = (bool)$st->fetchColumn();
        } catch (Throwable $e) { return $cache[$key] = false; }
    }
}

$total_items_carrito = 0;
if (!empty($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) $total_items_carrito += (int)($item['cantidad'] ?? 0);
}

$foto_perfil_usuario = null;
$notificaciones_no_leidas = 0;
$categorias_nav_comp = [];

if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT nombre, slug FROM categorias WHERE COALESCE(is_active,true)=true ORDER BY nombre LIMIT 9");
        $categorias_nav_comp = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Throwable $e) {
        $categorias_nav_comp = [];
    }

    if ($isLogged) {
        try {
            if (emxNavColExists($pdo, 'usuarios', 'foto_perfil_url')) {
                $st = $pdo->prepare("SELECT foto_perfil_url FROM usuarios WHERE id = ?");
                $st->execute([$_SESSION['usuario_id']]);
                $foto_perfil_usuario = $st->fetchColumn() ?: null;
            }
        } catch (Throwable $e) { $foto_perfil_usuario = null; }

        try {
            if (emxNavTableExists($pdo, 'notificaciones')) {
                $st = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND COALESCE(leida,false)=false");
                $st->execute([$_SESSION['usuario_id']]);
                $notificaciones_no_leidas = (int)$st->fetchColumn();
            }
        } catch (Throwable $e) { $notificaciones_no_leidas = 0; }
    }
}
?>
<nav class="sticky top-0 z-50 bg-gradient-to-r from-sky-50 via-white to-blue-50 border-b border-blue-100 shadow-[0_10px_30px_rgba(15,23,42,.07)] backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="min-h-[76px] flex items-center gap-4">
            <a href="index.php" class="flex items-center shrink-0 group" aria-label="ElectroMax">
                <img src="assets/electromax_logo.png" alt="ElectroMax" class="h-13 sm:h-14 w-auto max-w-[220px] object-contain drop-shadow-md group-hover:scale-[1.02] transition">
            </a>

            <form action="index.php" method="GET" class="hidden md:flex flex-1 max-w-xl mx-auto relative" data-emx-search-form autocomplete="off">
                <div class="relative w-full">
                    <input
                        type="text"
                        name="q"
                        data-emx-search-input
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                        placeholder="Buscar productos, marcas o categorías..."
                        class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition text-sm text-slate-800 placeholder:text-slate-400 shadow-inner"
                    >
                    <button type="submit" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-700 transition" aria-label="Buscar">
                        <i class="fas fa-search"></i>
                    </button>
                    <div data-emx-search-results class="hidden absolute left-0 right-0 top-[calc(100%+10px)] bg-white border border-slate-200 rounded-2xl shadow-2xl shadow-slate-900/12 overflow-hidden z-[80]"></div>
                </div>
            </form>

            <div class="flex items-center gap-3 ml-auto">
                <?php if ($isLogged): ?>
                    <a href="wishlist.php" class="relative w-10 h-10 rounded-xl flex items-center justify-center text-slate-600 hover:text-red-600 hover:bg-red-50 transition" title="Lista de deseos">
                        <i class="fas fa-heart text-lg"></i>
                    </a>

                    <a href="notificaciones.php" class="relative w-10 h-10 rounded-xl flex items-center justify-center text-slate-600 hover:text-blue-700 hover:bg-blue-50 transition" title="Notificaciones">
                        <i class="fas fa-bell text-lg"></i>
                        <?php if ($notificaciones_no_leidas > 0): ?>
                            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white"><?= $notificaciones_no_leidas ?></span>
                        <?php endif; ?>
                    </a>

                    <a href="mi_cuenta.php" class="hidden sm:flex items-center gap-2 px-2 py-1 rounded-xl hover:bg-white/80 transition">
                        <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-100 border border-slate-200 shadow-sm">
                            <?php if (!empty($foto_perfil_usuario)): ?>
                                <img src="<?= htmlspecialchars($foto_perfil_usuario) ?>" alt="Perfil" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center font-bold text-sm text-slate-700">
                                    <?= strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <span class="text-sm font-bold text-slate-700 hidden lg:block"><?= htmlspecialchars(explode(' ', $_SESSION['usuario_nombre'] ?? 'Usuario')[0]) ?></span>
                    </a>
                <?php else: ?>
                    <a href="auth.php?action=login" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-black hover:bg-blue-700 transition">
                        <i class="fas fa-user"></i>Ingresar
                    </a>
                <?php endif; ?>

                <a href="carrito.php" id="cart-container" class="relative w-10 h-10 rounded-xl flex items-center justify-center text-slate-600 hover:text-blue-700 hover:bg-blue-50 transition" title="Carrito">
                    <i class="fas fa-shopping-bag text-lg"></i>
                    <?php if ($total_items_carrito > 0): ?>
                        <span id="cart-badge" class="absolute -top-1 -right-1 min-w-[20px] h-[20px] bg-blue-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white"><?= $total_items_carrito ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white/95 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2 overflow-x-auto py-2.5 no-scrollbar">
                <a href="index.php" class="whitespace-nowrap px-3 py-1.5 rounded-lg text-sm font-bold transition <?= $currentPage === 'index.php' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">Inicio</a>

                <?php foreach ($categorias_nav_comp as $cat): ?>
                    <a href="index.php?categoria=<?= urlencode($cat['slug']) ?>" class="whitespace-nowrap px-3 py-1.5 rounded-lg text-sm font-semibold transition text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </a>
                <?php endforeach; ?>

                <a href="planes.php" class="whitespace-nowrap px-3 py-1.5 rounded-lg text-sm font-bold bg-amber-500 text-white ml-auto flex items-center gap-1 hover:bg-amber-600 transition">
                    <i class="fas fa-crown text-xs"></i>Membresías
                </a>

                <a href="soporte.php" class="whitespace-nowrap px-3 py-1.5 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 flex items-center gap-1">
                    <i class="fas fa-headset text-xs"></i>Soporte
                </a>
            </div>
        </div>
    </div>
</nav>

<script src="assets/emx_search_autocomplete.js" defer></script>
