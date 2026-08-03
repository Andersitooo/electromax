<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

/**
 * Crear usuario interno de empresa para ElectroMax.
 * ------------------------------------------------------------
 * Uso recomendado desde consola/XAMPP Shell:
 *   php crear_usuario_empresa.php
 *
 * Este usuario sirve para entrar al sistema como ADMIN de empresa
 * o facturación. No reemplaza el correo SMTP; el correo SMTP se
 * configura en config_correo.php.
 *
 * Seguridad:
 * - Solo se puede ejecutar desde consola.
 * - Cambia EMAIL, PASSWORD y TELÉFONO antes de ejecutarlo.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script solo puede ejecutarse desde consola.');
}

require_once EMX_CONFIG_PATH . '/database.php';

if (!isset($pdo) || !$pdo instanceof PDO) {
    exit("No hay conexión PDO. Revisa db.php.\n");
}

// ============================================================
// CAMBIA ESTOS DATOS ANTES DE EJECUTAR
// ============================================================
$email = 'facturacion@tudominio.com';
$passwordPlano = 'CAMBIAR_ESTA_CLAVE_SEGURA_123';
$telefonoEmpresa = '04-273-0000'; // Puede ser teléfono fijo o celular corporativo.
$nombres = 'ElectroMax';
$apellidos = 'Facturación';
$cedulaRuc = '0999999999001';
$rolNombre = 'ADMIN'; // ADMIN recomendado. Usa SUPERADMIN solo si de verdad lo necesitas.

if ($email === 'facturacion@tudominio.com' || $passwordPlano === 'CAMBIAR_ESTA_CLAVE_SEGURA_123') {
    exit("Edita crear_usuario_empresa.php y cambia email/password antes de ejecutar.\n");
}

if (strlen($passwordPlano) < 8) {
    exit("La contraseña debe tener al menos 8 caracteres.\n");
}

try {
    $pdo->beginTransaction();

    $stmtRol = $pdo->prepare("SELECT id FROM roles WHERE nombre = ? LIMIT 1");
    $stmtRol->execute([$rolNombre]);
    $rolId = $stmtRol->fetchColumn();

    if (!$rolId) {
        $stmtInsertRol = $pdo->prepare("INSERT INTO roles (nombre, descripcion) VALUES (?, ?) RETURNING id");
        $stmtInsertRol->execute([$rolNombre, 'Usuario administrativo de empresa/facturación']);
        $rolId = $stmtInsertRol->fetchColumn();
    }

    $stmtExiste = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND deleted_at IS NULL LIMIT 1");
    $stmtExiste->execute([$email]);
    $usuarioId = $stmtExiste->fetchColumn();

    $hash = password_hash($passwordPlano, PASSWORD_DEFAULT);

    if ($usuarioId) {
        $stmt = $pdo->prepare("\n            UPDATE usuarios\n            SET rol_id = ?, nombres = ?, apellidos = ?, password_hash = ?, telefono = ?, cedula_ruc = ?, is_active = TRUE, updated_at = CURRENT_TIMESTAMP\n            WHERE id = ?\n        ");
        $stmt->execute([$rolId, $nombres, $apellidos, $hash, $telefonoEmpresa, $cedulaRuc, $usuarioId]);
        echo "Usuario de empresa actualizado: {$email}\n";
    } else {
        $stmt = $pdo->prepare("\n            INSERT INTO usuarios (rol_id, nombres, apellidos, email, password_hash, telefono, cedula_ruc, tiene_badge_verificado, is_active)\n            VALUES (?, ?, ?, ?, ?, ?, ?, TRUE, TRUE)\n            RETURNING id\n        ");
        $stmt->execute([$rolId, $nombres, $apellidos, $email, $hash, $telefonoEmpresa, $cedulaRuc]);
        $usuarioId = $stmt->fetchColumn();
        echo "Usuario de empresa creado: {$email}\n";
    }

    $pdo->commit();
    echo "ID: {$usuarioId}\n";
    echo "Rol: {$rolNombre}\n";
    echo "Teléfono guardado: {$telefonoEmpresa}\n";
    echo "Ya puedes iniciar sesión con ese correo y contraseña.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
