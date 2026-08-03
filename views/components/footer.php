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
// components/footer.php - Footer cliente unificado ElectroMax

$categorias_footer_comp = [];
$empresa_footer = [
    'razon_social' => 'ElectroMax S.A.S.',
    'email' => 'abustamante831@fafi.utb.edu.ec',
    'telefono' => '04-273-0000',
    'direccion' => 'Babahoyo, Los Ríos, Ecuador',
];

if (!function_exists('emxFooterTableExists')) {
    function emxFooterTableExists($pdo, $table) {
        static $cache = [];
        if (array_key_exists($table, $cache)) return $cache[$table];
        try {
            $st = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name=? LIMIT 1");
            $st->execute([$table]);
            return $cache[$table] = (bool)$st->fetchColumn();
        } catch (Throwable $e) { return $cache[$table] = false; }
    }
}
if (!function_exists('emxFooterColExists')) {
    function emxFooterColExists($pdo, $table, $col) {
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

if (isset($pdo)) {
    try {
        $stmtCat = $pdo->query("SELECT nombre, slug FROM categorias WHERE COALESCE(is_active,true)=true ORDER BY nombre LIMIT 6");
        $categorias_footer_comp = $stmtCat ? $stmtCat->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Throwable $e) {
        $categorias_footer_comp = [];
    }

    try {
        foreach (['empresa_config', 'empresa_configuracion', 'empresa'] as $tabla) {
            if (!emxFooterTableExists($pdo, $tabla)) continue;
            $cols = [];
            foreach (['razon_social','nombre_comercial','email','correo','telefono','direccion_matriz','direccion'] as $col) {
                if (emxFooterColExists($pdo, $tabla, $col)) $cols[] = $col;
            }
            if (!$cols) continue;
            $row = $pdo->query("SELECT " . implode(',', $cols) . " FROM {$tabla} LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if (!$row) continue;

            $empresa_footer['razon_social'] = $row['razon_social'] ?? $row['nombre_comercial'] ?? $empresa_footer['razon_social'];
            $empresa_footer['email'] = $row['email'] ?? $row['correo'] ?? $empresa_footer['email'];
            $empresa_footer['telefono'] = $row['telefono'] ?? $empresa_footer['telefono'];
            $empresa_footer['direccion'] = $row['direccion_matriz'] ?? $row['direccion'] ?? $empresa_footer['direccion'];
            break;
        }
    } catch (Throwable $e) {}
}
?>
<footer class="bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 border-t border-blue-900/50 mt-auto text-slate-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-9 text-sm">
            <div class="md:col-span-1">
                <a href="index.php" class="inline-flex items-center mb-4" aria-label="ElectroMax">
                    <img src="assets/electromax_logo.png" alt="ElectroMax" class="h-14 w-auto max-w-[220px] object-contain drop-shadow-[0_14px_24px_rgba(56,189,248,.20)]">
                </a>
                <p class="text-slate-400 leading-relaxed">
                    Tecnología para tu hogar desde Babahoyo, Ecuador. Compras, seguimiento, garantías, devoluciones y soporte en un solo lugar.
                </p>
            </div>

            <div>
                <h4 class="font-black text-white mb-4 uppercase tracking-wide text-xs">Categorías</h4>
                <ul class="space-y-2.5">
                    <?php foreach ($categorias_footer_comp as $cat): ?>
                        <li><a href="index.php?categoria=<?= urlencode($cat['slug']) ?>" class="text-slate-400 hover:text-sky-300 transition"><?= htmlspecialchars($cat['nombre']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="index.php?descuento_min=10" class="text-amber-300 hover:text-amber-200 transition flex items-center gap-1.5"><i class="fas fa-fire text-xs"></i>Ofertas</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-black text-white mb-4 uppercase tracking-wide text-xs">Soporte</h4>
                <ul class="space-y-2.5">
                    <li><a href="soporte.php" class="text-slate-400 hover:text-sky-300 transition">Centro de soporte</a></li>
                    <li><a href="tracking.php" class="text-slate-400 hover:text-sky-300 transition">Seguimiento de pedido</a></li>
                    <li><a href="mi_cuenta.php?seccion=devoluciones" class="text-slate-400 hover:text-sky-300 transition">Devoluciones</a></li>
                    <li><a href="garantia.php" class="text-slate-400 hover:text-sky-300 transition">Garantías</a></li>
                    <li><a href="planes.php" class="text-amber-300 hover:text-amber-200 transition flex items-center gap-1.5"><i class="fas fa-crown text-xs"></i>Membresías</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-black text-white mb-4 uppercase tracking-wide text-xs">Contacto</h4>
                <div class="space-y-3 text-slate-400">
                    <p class="flex items-start gap-2"><i class="fas fa-building text-sky-400 mt-1"></i><span><?= htmlspecialchars($empresa_footer['razon_social']) ?></span></p>
                    <p class="flex items-start gap-2"><i class="fas fa-envelope text-sky-400 mt-1"></i><span><?= htmlspecialchars($empresa_footer['email']) ?></span></p>
                    <p class="flex items-start gap-2"><i class="fas fa-phone text-sky-400 mt-1"></i><span><?= htmlspecialchars($empresa_footer['telefono']) ?></span></p>
                    <p class="flex items-start gap-2"><i class="fas fa-location-dot text-sky-400 mt-1"></i><span><?= htmlspecialchars($empresa_footer['direccion']) ?></span></p>
                </div>
            </div>
        </div>

        <div class="border-t border-white/10 mt-8 pt-5 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-500">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($empresa_footer['razon_social']) ?>. Todos los derechos reservados.</p>
            <div class="flex items-center gap-3">
                <span>Babahoyo</span>
                <span>Los Ríos</span>
                <span>Ecuador</span>
            </div>
        </div>
    </div>
</footer>
