<?php
/**
 * Verificador de limpieza Fase 8.
 *
 * Uso:
 * php scripts/verificar_limpieza_fase8.php
 *
 * No ejecuta base de datos.
 * Solo comprueba que archivos críticos sigan existiendo.
 */

$root = dirname(__DIR__);

$criticos = [
    'index.php',
    'producto.php',
    'carrito.php',
    'checkout.php',
    'mi_cuenta.php',
    'admin.php',
    'proveedor.php',
    'auth.php',
    'db.php',
    'seguridad.php',
    'bootstrap/app.php',
    'app/Support/legacy_routes.php',
    'docs/fase8/00_RESUMEN_FASE_8.md',
];

$errores = 0;

echo "Verificación de limpieza Fase 8\n";
echo "Raíz: {$root}\n\n";

foreach ($criticos as $rel) {
    if (!is_file($root . '/' . $rel)) {
        echo "[ERROR] Falta {$rel}\n";
        $errores++;
    }
}

if ($errores === 0) {
    echo "Resultado: limpieza segura. Archivos críticos conservados.\n";
    exit(0);
}

echo "Resultado: se detectaron {$errores} problema(s).\n";
exit(1);
?>
