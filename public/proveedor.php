<?php
/**
 * Entrada pública neta - Fase 10.
 *
 * Esta ruta ya no carga el archivo heredado de la raíz.
 * Carga directamente el controlador ubicado en:
 * app/Controllers/Proveedor/proveedor.php
 */

require_once dirname(__DIR__) . '/bootstrap/app.php';
chdir(EMX_PUBLIC_PATH);
require EMX_ROOT . '/app/Controllers/Proveedor/proveedor.php';
?>
