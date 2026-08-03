<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_stock.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

/**
 * Stock bajo, solicitudes y cotizaciones simuladas para ElectroMax.
 * Compatible con bases que aún no tienen algunas columnas nuevas.
 */

if (!function_exists('emxColumnExistsLocal')) {
    function emxColumnExistsLocal($pdo, $tabla, $columna) {
        if (function_exists('emxDbColumnExists')) {
            return emxDbColumnExists($pdo, $tabla, $columna);
        }
        static $cache = [];
        $key = $tabla . '.' . $columna;
        if (array_key_exists($key, $cache)) return $cache[$key];
        try {
            $stmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=? AND column_name=? LIMIT 1");
            $stmt->execute([$tabla, $columna]);
            $cache[$key] = (bool)$stmt->fetchColumn();
        } catch (Throwable $e) {
            $cache[$key] = false;
        }
        return $cache[$key];
    }
}


if (!function_exists('emxReabCerrarSolicitudesDuplicadasProducto')) {
function emxReabCerrarSolicitudesDuplicadasProducto($pdo, $producto_id, $solicitud_actual_id = null, $motivo = '') {
    if (!$producto_id) return 0;

    $params = [$producto_id];
    $whereActual = '';
    if (!empty($solicitud_actual_id)) {
        $whereActual = ' AND id <>?';
        $params[] = $solicitud_actual_id;
    }

    $sqlSel = "SELECT id
               FROM solicitudes_reabastecimiento
               WHERE producto_id = ?
                 {$whereActual}
                 AND estado IN ('pendiente','cotizada','en_revision')
               FOR UPDATE";
    $stSel = $pdo->prepare($sqlSel);
    $stSel->execute($params);
    $ids = $stSel->fetchAll(PDO::FETCH_COLUMN);

    if (empty($ids)) return 0;

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Rechazar propuestas pendientes de solicitudes viejas para que ya no sean aprobables.
    $hasUpdatedProp = emxColumnExistsLocal($pdo, 'propuestas_proveedor', 'updated_at');
    $sqlProp = "UPDATE propuestas_proveedor
                SET estado = 'rechazada'" . ($hasUpdatedProp ? ", updated_at = NOW()" : "") . "
                WHERE solicitud_id IN ({$placeholders})
                  AND estado IN ('pendiente','en_revision')";
    $pdo->prepare($sqlProp)->execute($ids);

    $tieneNotas = emxColumnExistsLocal($pdo, 'solicitudes_reabastecimiento', 'notas_admin');
    $nota = trim((string)$motivo);
    if ($nota === '') {
        $nota = 'Cerrada automáticamente por existir una solicitud/cotización más reciente para el mismo producto.';
    }

    // Intentamos con estados comunes. Si tu BD tiene CHECK y no acepta uno, prueba el siguiente.
    $estadosCierre = ['reemplazada', 'cerrada', 'cancelada'];
    foreach ($estadosCierre as $estado) {
        try {
            if ($tieneNotas) {
                $sqlSol = "UPDATE solicitudes_reabastecimiento
                           SET estado = ?,
                               notas_admin = CONCAT(
                                   COALESCE(notas_admin,''),
                                   CASE WHEN COALESCE(notas_admin,'') = '' THEN '' ELSE E'\n' END,
                                   ?
                               )
                           WHERE id IN ({$placeholders})";
                $pdo->prepare($sqlSol)->execute(array_merge([$estado, $nota], $ids));
            } else {
                $sqlSol = "UPDATE solicitudes_reabastecimiento
                           SET estado = ?
                           WHERE id IN ({$placeholders})";
                $pdo->prepare($sqlSol)->execute(array_merge([$estado], $ids));
            }
            return count($ids);
        } catch (Throwable $e) {
            // probar siguiente estado compatible
        }
    }

    return 0;
}
}

// Alias usado por el admin/parches anteriores.
if (!function_exists('emxCerrarSolicitudesDuplicadasProducto')) {
function emxCerrarSolicitudesDuplicadasProducto($pdo, $producto_id, $solicitud_actual_id = null, $motivo = '') {
    return emxReabCerrarSolicitudesDuplicadasProducto($pdo, $producto_id, $solicitud_actual_id, $motivo);
}
}


if (!function_exists('emxAplicarDescuentoVolumenStock')) {
    function emxAplicarDescuentoVolumenStock($precio, $cantidad, $json) {
        $rangos = json_decode($json ?: '[]', true);
        if (!is_array($rangos)) return (float)$precio;
        $desc = 0;
        foreach ($rangos as $r) {
            $min = (int)($r['cantidad_min'] ?? 0);
            $max = array_key_exists('cantidad_max', $r) && $r['cantidad_max'] !== null && $r['cantidad_max'] !== '' ? (int)$r['cantidad_max'] : null;
            $d = (float)($r['descuento'] ?? 0);
            if ($cantidad >= $min && ($max === null || $cantidad <= $max)) $desc = max($desc, $d);
        }
        return round(max(0.01, (float)$precio) * (1 - ($desc / 100)), 2);
    }
}

if (!function_exists('emxCalcularDiasProveedorStock')) {
    function emxCalcularDiasProveedorStock(array $cp, int $cantidad): int {
        $disponibles = max(0, (int)($cp['unidades_disponibles'] ?? 0));
        $capacidad = max(1, (int)($cp['capacidad_diaria'] ?? 1));
        $base = max(1, (int)($cp['tiempo_entrega_estandar'] ?? 5));
        $distancia = max(0, (float)($cp['distancia_km'] ?? 0));
        $velocidad = max(1, (float)($cp['velocidad_promedio_kmh'] ?? 60));
        $aduanas = max(0, (int)($cp['tiempo_aduanas_dias'] ?? 0));
        $aProducir = max(0, $cantidad - $disponibles);
        $diasProduccion = (int)ceil($aProducir / $capacidad);
        $diasTransporte = $distancia >0 ? (int)ceil(($distancia / $velocidad) / 8) : 0;
        return max(1, $base + $diasProduccion + $diasTransporte + $aduanas);
    }
}

/**
 * Crea cotizaciones SIMULADAS para que el admin pueda comparar proveedores sin esperar
 * un proceso externo. Si luego el proveedor entra a su portal, puede ver o enviar su propia propuesta
 * solo si no existe una para esa solicitud.
 */
function emxGenerarCotizacionesSimuladas($pdo, $solicitud_id, $producto_id, $cantidad_necesaria) {
    if (!$solicitud_id || !$producto_id || $cantidad_necesaria <= 0) return 0;
    if (!emxColumnExistsLocal($pdo, 'propuestas_proveedor', 'cantidad_ofrecida')) return 0;

    $stmt = $pdo->prepare("SELECT precio_base, COALESCE(costo_unitario, 0) AS costo_unitario FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    $costoBase = (float)($prod['costo_unitario'] ?? 0);
    if ($costoBase <= 0) $costoBase = max(1, (float)($prod['precio_base'] ?? 1) * 0.70);

    $stmt = $pdo->prepare("\n        SELECT pp.proveedor_id,\n               COALESCE(cp.capacidad_diaria, 5) AS capacidad_diaria,\n               COALESCE(cp.capacidad_maxima_pedido, 0) AS capacidad_maxima_pedido,\n               COALESCE(cp.tiempo_entrega_estandar, 5) AS tiempo_entrega_estandar,\n               COALESCE(cp.distancia_km, 0) AS distancia_km,\n               COALESCE(cp.velocidad_promedio_kmh, 60) AS velocidad_promedio_kmh,\n               COALESCE(cp.tiempo_aduanas_dias, 0) AS tiempo_aduanas_dias,\n               COALESCE(cp.tasa_defectos_fabrica, 0.05) AS tasa_defectos_fabrica,\n               COALESCE(cp.unidades_disponibles, 0) AS unidades_disponibles,\n               COALESCE(cp.descuentos_volumen, '[]'::jsonb) AS descuentos_volumen\n        FROM producto_proveedor pp\n        LEFT JOIN capacidad_proveedor cp ON cp.proveedor_id = pp.proveedor_id AND cp.producto_id = pp.producto_id\n        WHERE pp.producto_id = ?\n        LIMIT 5\n    ");
    $stmt->execute([$producto_id]);
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$proveedores) return 0;

    $tieneUpdatedAt = emxColumnExistsLocal($pdo, 'propuestas_proveedor', 'updated_at');
    $insertadas = 0;

    foreach ($proveedores as $cp) {
        $proveedorId = $cp['proveedor_id'];
        $check = $pdo->prepare("SELECT id FROM propuestas_proveedor WHERE solicitud_id = ? AND proveedor_id = ? LIMIT 1");
        $check->execute([$solicitud_id, $proveedorId]);
        if ($check->fetchColumn()) continue;

        $maxPedido = (int)($cp['capacidad_maxima_pedido'] ?? 0);
        $cantidadOfrecida = $maxPedido >0 ? min($cantidad_necesaria, $maxPedido) : $cantidad_necesaria;
        $cantidadOfrecida = max(1, (int)$cantidadOfrecida);
        $dias = emxCalcularDiasProveedorStock($cp, $cantidadOfrecida);
        $precioUnitario = emxAplicarDescuentoVolumenStock($costoBase, $cantidadOfrecida, $cp['descuentos_volumen'] ?? '[]');
        $precioTotal = round($precioUnitario * $cantidadOfrecida, 2);
        $fecha = (new DateTime('today'))->modify('+' . $dias . ' days')->format('Y-m-d');
        $cal = [[
            'fecha' =>$fecha,
            'unidades' =>$cantidadOfrecida,
            'tipo' =>'total',
            'simulada' =>true
        ]];
        $nota = 'Cotización simulada desde capacidad del proveedor. Admin puede aprobar la más conveniente.';

        if ($tieneUpdatedAt) {
            $ins = $pdo->prepare("\n                INSERT INTO propuestas_proveedor\n                (solicitud_id, proveedor_id, cantidad_ofrecida, dias_entrega, precio_unitario, precio_total, calendario_entregas, notas, estado, created_at, updated_at)\n                VALUES (?, ?, ?, ?, ?, ?, ?::jsonb, ?, 'pendiente', NOW(), NOW())\n            ");
            $ins->execute([$solicitud_id, $proveedorId, $cantidadOfrecida, $dias, $precioUnitario, $precioTotal, json_encode($cal), $nota]);
        } else {
            $ins = $pdo->prepare("\n                INSERT INTO propuestas_proveedor\n                (solicitud_id, proveedor_id, cantidad_ofrecida, dias_entrega, precio_unitario, precio_total, calendario_entregas, notas, estado)\n                VALUES (?, ?, ?, ?, ?, ?, ?::jsonb, ?, 'pendiente')\n            ");
            $ins->execute([$solicitud_id, $proveedorId, $cantidadOfrecida, $dias, $precioUnitario, $precioTotal, json_encode($cal), $nota]);
        }
        $insertadas++;
    }

    if ($insertadas >0) {
        $pdo->prepare("UPDATE solicitudes_reabastecimiento SET estado = 'cotizada' WHERE id = ? AND estado = 'pendiente'")->execute([$solicitud_id]);
    }
    return $insertadas;
}

function verificarYGenerarSolicitudes($pdo) {
    $stmtM = $pdo->query("SELECT id FROM sucursales WHERE es_matriz = TRUE LIMIT 1");
    $matriz = $stmtM->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("\n        SELECT p.id, p.nombre, p.stock_actual_global, p.punto_reorden, p.stock_maximo,\n               GREATEST(COALESCE(p.stock_maximo,0) - COALESCE(p.stock_actual_global,0), COALESCE(p.punto_reorden,5) * 2) AS cantidad_sugerida\n        FROM productos p\n        WHERE p.deleted_at IS NULL\n          AND p.is_active = TRUE\n          AND COALESCE(p.punto_reorden,0) >0\n          AND p.stock_actual_global <= p.punto_reorden\n    ");

    $tieneNotas = emxColumnExistsLocal($pdo, 'solicitudes_reabastecimiento', 'notas_admin');
    $tieneSucursalMatriz = emxColumnExistsLocal($pdo, 'solicitudes_reabastecimiento', 'sucursal_matriz_id');

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $producto) {
        $check = $pdo->prepare("SELECT id FROM solicitudes_reabastecimiento WHERE producto_id = ? AND estado IN ('pendiente','cotizada','en_revision') LIMIT 1");
        $check->execute([$producto['id']]);
        if ($check->fetchColumn()) continue;

        $cantidad = max(1, (int)$producto['cantidad_sugerida']);
        $nota = 'Generada automáticamente por stock bajo';
        $solicitud_id = null;

        if ($tieneNotas && $tieneSucursalMatriz) {
            $ins = $pdo->prepare("INSERT INTO solicitudes_reabastecimiento (producto_id, sucursal_matriz_id, cantidad_necesaria, estado, fecha_limite, notas_admin, created_at) VALUES (?, ?, ?, 'pendiente', CURRENT_DATE + INTERVAL '7 days', ?, NOW()) RETURNING id");
            $ins->execute([$producto['id'], $matriz['id'] ?? null, $cantidad, $nota]);
        } elseif ($tieneSucursalMatriz) {
            $ins = $pdo->prepare("INSERT INTO solicitudes_reabastecimiento (producto_id, sucursal_matriz_id, cantidad_necesaria, estado, fecha_limite, created_at) VALUES (?, ?, ?, 'pendiente', CURRENT_DATE + INTERVAL '7 days', NOW()) RETURNING id");
            $ins->execute([$producto['id'], $matriz['id'] ?? null, $cantidad]);
        } elseif ($tieneNotas) {
            $ins = $pdo->prepare("INSERT INTO solicitudes_reabastecimiento (producto_id, cantidad_necesaria, estado, fecha_limite, notas_admin, created_at) VALUES (?, ?, 'pendiente', CURRENT_DATE + INTERVAL '7 days', ?, NOW()) RETURNING id");
            $ins->execute([$producto['id'], $cantidad, $nota]);
        } else {
            $ins = $pdo->prepare("INSERT INTO solicitudes_reabastecimiento (producto_id, cantidad_necesaria, estado, fecha_limite, created_at) VALUES (?, ?, 'pendiente', CURRENT_DATE + INTERVAL '7 days', NOW()) RETURNING id");
            $ins->execute([$producto['id'], $cantidad]);
        }
        $solicitud_id = $ins->fetchColumn();
        emxGenerarCotizacionesSimuladas($pdo, $solicitud_id, $producto['id'], $cantidad);
    }
}

function obtenerSolicitudesActivas($pdo) {
    $notasExpr = emxColumnExistsLocal($pdo, 'solicitudes_reabastecimiento', 'notas_admin')
        ? "s.notas_admin"
        : "''::text AS notas_admin";

    // Mostrar solo la solicitud activa más reciente por producto.
    // Si por datos anteriores quedaron varias solicitudes abiertas del mismo producto,
    // no deben aparecer todas con botón de aprobar.
    $stmt = $pdo->query("\n        SELECT s.id, s.producto_id, s.cantidad_necesaria, s.estado, s.fecha_limite, s.created_at, {$notasExpr},\n               p.nombre AS producto_nombre, p.stock_actual_global, p.punto_reorden,\n               (SELECT COUNT(*) FROM propuestas_proveedor pp WHERE pp.solicitud_id = s.id) AS total_cotizaciones,\n               (SELECT COUNT(*) FROM producto_proveedor rel WHERE rel.producto_id = s.producto_id) AS total_proveedores\n        FROM solicitudes_reabastecimiento s\n        JOIN productos p ON s.producto_id = p.id\n        WHERE s.estado IN ('pendiente','cotizada','en_revision')\n          AND s.id = (\n              SELECT s2.id\n              FROM solicitudes_reabastecimiento s2\n              WHERE s2.producto_id = s.producto_id\n                AND s2.estado IN ('pendiente','cotizada','en_revision')\n              ORDER BY s2.created_at DESC, s2.id DESC\n              LIMIT 1\n          )\n        ORDER BY p.stock_actual_global ASC, s.created_at DESC\n    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function emxScorePropuesta($prop) {
    $precio = max(0.01, (float)($prop['precio_unitario'] ?? 0));
    $dias = max(1, (int)($prop['dias_entrega'] ?? 1));
    $cantidad = max(1, (int)($prop['cantidad_ofrecida'] ?? 1));
    $defectos = max(0, (float)($prop['tasa_defectos_fabrica'] ?? 0.05));
    $disp = max(0, (int)($prop['unidades_disponibles'] ?? 0));
    // Menor score = mejor: costo 50%, tiempo 35%, cantidad 10%, riesgo 5%, disponibilidad como bonificación.
    return round(($precio * 0.50) + ($dias * 3.50) - min($cantidad, 500) * 0.02 + ($defectos * 100) - min($disp, 100) * 0.03, 2);
}

function obtenerCotizaciones($pdo, $solicitud_id) {
    $stmtSol = $pdo->prepare("SELECT producto_id, cantidad_necesaria FROM solicitudes_reabastecimiento WHERE id = ?");
    $stmtSol->execute([$solicitud_id]);
    $sol = $stmtSol->fetch(PDO::FETCH_ASSOC);
    if ($sol) emxGenerarCotizacionesSimuladas($pdo, $solicitud_id, $sol['producto_id'], (int)$sol['cantidad_necesaria']);

    $stmt = $pdo->prepare("\n        SELECT pp.id, pp.proveedor_id, pp.cantidad_ofrecida, pp.dias_entrega, pp.precio_unitario, pp.precio_total,\n               pp.calendario_entregas, pp.estado, pp.created_at, pp.notas,\n               u.nombres, u.apellidos, u.email,\n               COALESCE(cp.tasa_defectos_fabrica, 0.05) AS tasa_defectos_fabrica,\n               COALESCE(cp.unidades_disponibles, 0) AS unidades_disponibles,\n               COALESCE(cp.capacidad_diaria, 1) AS capacidad_diaria,\n               COALESCE(cp.distancia_km, 0) AS distancia_km\n        FROM propuestas_proveedor pp\n        JOIN usuarios u ON pp.proveedor_id = u.id\n        LEFT JOIN solicitudes_reabastecimiento sr ON sr.id = pp.solicitud_id\n        LEFT JOIN capacidad_proveedor cp ON cp.proveedor_id = pp.proveedor_id AND cp.producto_id = sr.producto_id\n        WHERE pp.solicitud_id = ?\n        ORDER BY pp.estado = 'pendiente' DESC, pp.precio_unitario ASC, pp.dias_entrega ASC\n    ");
    $stmt->execute([$solicitud_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['score'] = emxScorePropuesta($r);
        $r['es_mejor'] = false;
    }
    unset($r);
    usort($rows, fn($a, $b) =>$a['score'] <=>$b['score']);
    if (!empty($rows)) $rows[0]['es_mejor'] = true;
    return $rows;
}

function aprobarCotizacion($pdo, $cotizacion_id) {
    try {
        $pdo->beginTransaction();

        $tieneBackorder = emxColumnExistsLocal($pdo, 'solicitudes_reabastecimiento', 'backorder_id');
        $tieneSucursalMatriz = emxColumnExistsLocal($pdo, 'solicitudes_reabastecimiento', 'sucursal_matriz_id');
        $selectBackorder = $tieneBackorder ? 'sr.backorder_id' : 'NULL AS backorder_id';
        $selectSucursal = $tieneSucursalMatriz ? 'sr.sucursal_matriz_id' : 'NULL AS sucursal_matriz_id';

        $stmt = $pdo->prepare("\n            SELECT pp.*, sr.producto_id, sr.cantidad_necesaria, {$selectSucursal}, sr.id AS solicitud_id, {$selectBackorder}\n            FROM propuestas_proveedor pp\n            JOIN solicitudes_reabastecimiento sr ON pp.solicitud_id = sr.id\n            WHERE pp.id = ? FOR UPDATE\n        ");
        $stmt->execute([$cotizacion_id]);
        $prop = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prop) throw new Exception('Propuesta no encontrada');
        if (!in_array($prop['estado'], ['pendiente','en_revision'], true)) throw new Exception('La propuesta ya fue procesada');

        $stmtOld = $pdo->prepare("SELECT COALESCE(stock_actual_global,0) FROM productos WHERE id = ? FOR UPDATE");
        $stmtOld->execute([$prop['producto_id']]);
        $stockAntes = (int)($stmtOld->fetchColumn() ?: 0);

        $hasUpdated = emxColumnExistsLocal($pdo, 'propuestas_proveedor', 'updated_at');
        $pdo->prepare("UPDATE propuestas_proveedor SET estado = 'aprobada'" . ($hasUpdated ? ", updated_at = NOW()" : "") . " WHERE id = ?")->execute([$cotizacion_id]);
        $pdo->prepare("UPDATE propuestas_proveedor SET estado = 'rechazada'" . ($hasUpdated ? ", updated_at = NOW()" : "") . " WHERE solicitud_id = ? AND id != ? AND estado IN ('pendiente','en_revision')")->execute([$prop['solicitud_id'], $cotizacion_id]);
        $pdo->prepare("UPDATE solicitudes_reabastecimiento SET estado = 'aprobada' WHERE id = ?")->execute([$prop['solicitud_id']]);

        // Al aprobar una cotización de un producto, las demás solicitudes activas del mismo producto
        // ya no deben seguir en pie ni permitir aprobación accidental.
        emxCerrarSolicitudesDuplicadasProducto(
            $pdo,
            $prop['producto_id'],
            $prop['solicitud_id'],
            'Reemplazada automáticamente porque se aprobó la cotización de la solicitud #' . substr((string)$prop['solicitud_id'], 0, 8) . ' del mismo producto.'
        );

        // Simulación académica mejorada:
        // Si la solicitud viene de un pedido con sobrestock, una parte completa el pedido del cliente
        // y solo el excedente (punto de reorden) entra al stock vendible.
        $cantidad = max(1, (int)$prop['cantidad_ofrecida']);
        $cantidadParaCliente = 0;
        $cantidadParaStock = $cantidad;

        if (!empty($prop['backorder_id'])) {
            try {
                $stmtBo = $pdo->prepare("SELECT cantidad_pendiente, COALESCE(cantidad_resuelta,0) AS cantidad_resuelta FROM pedidos_backorder WHERE id = ? FOR UPDATE");
                $stmtBo->execute([$prop['backorder_id']]);
                $bo = $stmtBo->fetch(PDO::FETCH_ASSOC);
                if ($bo) {
                    $pendienteCliente = max(0, (int)$bo['cantidad_pendiente'] - (int)$bo['cantidad_resuelta']);
                    $cantidadParaCliente = min($cantidad, $pendienteCliente);
                    $cantidadParaStock = max(0, $cantidad - $cantidadParaCliente);

                    if ($cantidadParaCliente >0) {
                        $updBo = $pdo->prepare("\n                            UPDATE pedidos_backorder\n                            SET cantidad_resuelta = COALESCE(cantidad_resuelta,0) + ?,\n                                estado = CASE\n                                    WHEN COALESCE(cantidad_resuelta,0) + ? >= cantidad_pendiente THEN 'completado'\n                                    ELSE estado\n                                END,\n                                updated_at = NOW()\n                            WHERE id = ?\n                        ");
                        $updBo->execute([$cantidadParaCliente, $cantidadParaCliente, $prop['backorder_id']]);
                    }
                }
            } catch (Throwable $e) {
                // Si la tabla backorder no está disponible, se conserva el comportamiento anterior.
                $cantidadParaCliente = 0;
                $cantidadParaStock = $cantidad;
            }
        }

        if ($cantidadParaStock >0) {
            $pdo->prepare("UPDATE productos SET stock_actual_global = COALESCE(stock_actual_global,0) + ? WHERE id = ?")->execute([$cantidadParaStock, $prop['producto_id']]);
            if (!empty($prop['sucursal_matriz_id'])) {
                $pdo->prepare("\n                    INSERT INTO inventario_sucursal (sucursal_id, producto_id, stock, ultimo_reabastecimiento)\n                    VALUES (?, ?, ?, NOW())\n                    ON CONFLICT (sucursal_id, producto_id) DO UPDATE SET stock = inventario_sucursal.stock + EXCLUDED.stock, ultimo_reabastecimiento = NOW()\n                ")->execute([$prop['sucursal_matriz_id'], $prop['producto_id'], $cantidadParaStock]);
            }
        }

        $stmtNew = $pdo->prepare("SELECT COALESCE(stock_actual_global,0) FROM productos WHERE id = ?");
        $stmtNew->execute([$prop['producto_id']]);
        $stockDespues = (int)($stmtNew->fetchColumn() ?: 0);
        if ($stockAntes <= 0 && $stockDespues >0 && function_exists('notificarStockDisponible')) {
            notificarStockDisponible($pdo, $prop['producto_id']);
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

?>