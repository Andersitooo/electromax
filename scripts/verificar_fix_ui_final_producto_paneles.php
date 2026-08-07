<?php
$root = dirname(__DIR__);
$files = [
    'views/frontend/producto_view.php',
    'app/Helpers/funciones_home.php',
    'app/Helpers/funciones_ficha_tecnica.php',
    'app/Controllers/Web/ficha_tecnica.php',
    'app/Controllers/Web/ficha_tecnica_pdf.php',
    'views/admin/admin_view.php',
    'views/proveedor/proveedor_view.php',
];
$ok = true;
foreach ($files as $file) {
    $path = $root . DIRECTORY_SEPARATOR . $file;
    if (!is_file($path)) { echo "FALTA: $file\n"; $ok = false; continue; }
    $cmd = 'php -l ' . escapeshellarg($path) . ' 2>&1';
    $out = shell_exec($cmd);
    echo trim($out) . "\n";
    if (strpos($out, 'No syntax errors detected') === false) $ok = false;
}
$checks = [
    'views/frontend/producto_view.php' => ['related-section-final', 'related-card-final'],
    'app/Helpers/funciones_home.php' => ['ORDER BY CASE WHEN p.categoria_id = ? THEN 0 ELSE 1 END'],
    'app/Helpers/funciones_ficha_tecnica.php' => ['emx-spec-table', 'Tabla técnica'],
    'app/Controllers/Web/ficha_tecnica_pdf.php' => ['draw_table_head', 'draw_spec_row'],
    'views/admin/admin_view.php' => ['Responsividad final panel admin'],
    'views/proveedor/proveedor_view.php' => ['Responsividad final portal proveedor'],
];
foreach ($checks as $file => $needles) {
    $content = file_get_contents($root . DIRECTORY_SEPARATOR . $file);
    foreach ($needles as $needle) {
        if (strpos($content, $needle) === false) { echo "NO ENCONTRADO: $needle en $file\n"; $ok = false; }
    }
}
echo $ok ? "Resultado: fix visual final aplicado correctamente.\n" : "Resultado: revisar avisos anteriores.\n";
exit($ok ? 0 : 1);
