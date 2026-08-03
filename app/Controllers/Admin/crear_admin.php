<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

/**
 * Script de emergencia para crear admin.
 * Por seguridad NO se ejecuta desde navegador. Úsalo solo por CLI:
 * php crear_admin.php admin@correo.com "ContraseñaSegura"
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script solo puede ejecutarse desde consola.');
}
require_once EMX_CONFIG_PATH . '/database.php';
$email = $argv[1] ?? null;
$password = $argv[2] ?? null;
if (!$email || !$password || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
    exit("Uso: php crear_admin.php admin@correo.com \"ContraseñaSegura10+\"\n");
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmtRol = $pdo->prepare("SELECT id FROM roles WHERE nombre = 'SUPERADMIN'");
$stmtRol->execute();
$rol = $stmtRol->fetchColumn();
if (!$rol) exit("No existe el rol SUPERADMIN.\n");
$stmt = $pdo->prepare("\n    INSERT INTO usuarios (rol_id, nombres, apellidos, email, password_hash, is_active, tiene_badge_verificado, created_at)\n    VALUES (?, 'Super', 'Admin', ?, ?, TRUE, TRUE, NOW())\n    ON CONFLICT (email) DO UPDATE SET rol_id = EXCLUDED.rol_id, password_hash = EXCLUDED.password_hash, is_active = TRUE\n");
$stmt->execute([$rol, $email, $hash]);
echo "Admin creado/actualizado de forma segura: {$email}\n";
?>