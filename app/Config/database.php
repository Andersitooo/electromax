<?php
/**
 * Configuración centralizada de conexión PostgreSQL.
 *
 * Responsabilidad:
 * - Leer variables de entorno de base de datos.
 * - Crear el objeto PDO compartido en `$pdo`.
 * - Mantener el mismo nombre de variable para que las rutas antiguas sigan funcionando.
 *
 * Nota:
 * El archivo raíz `db.php` ahora solo es un adaptador de compatibilidad.
 */

// Configuración de PostgreSQL por variables de entorno.
// En local se permite respaldo para XAMPP; en producción define variables de entorno y no guardes claves reales en el código.
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'electro2';
$user = getenv('DB_USER') ?: 'postgres';

$envPassword = getenv('DB_PASSWORD');
$isLocalRequest = in_array($_SERVER['SERVER_NAME'] ?? 'localhost', ['localhost', '127.0.0.1', '::1'], true)
    || PHP_SAPI === 'cli';
$password = $envPassword !== false ? $envPassword : ($isLocalRequest ? 'ander' : '');

if (!$isLocalRequest && $envPassword === false) {
    error_log('[DB] DB_PASSWORD no está configurada para producción.');
}

try {
    $pdo = new PDO(
        "pgsql:host={$host};port={$port};dbname={$dbname}",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE =>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE =>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES =>false,
        ]
    );
} catch (PDOException $e) {
    $db_error = $e->getMessage();
    error_log('[DB] ' . $e->getMessage());
    if (PHP_SAPI !== 'cli') {
        http_response_code(500);
    }
}
?>
