<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_backorder.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

/**
 * Simulación de sobrestock/backorder para ElectroMax.
 * Objetivo académico: permitir cantidades mayores al stock y mostrar al cliente
 * dos caminos claros: entrega total o entrega parcial.
 */

function emxStockSucursalMasCercana($pdo, $producto_id) {
    // Stock inmediato para estimación en carrito.
    // Prioridad: inventario por sucursal menos reservado. Si existen filas de inventario,
    // ese valor manda aunque sea 0. Solo se usa stock global como respaldo si no hay filas.
    $stockGlobal = 0;
    try {
        $stmt = $pdo->prepare("SELECT COALESCE(stock_actual_global,0) FROM productos WHERE id = ?");
        $stmt->execute([$producto_id]);
        $stockGlobal = max(0, (int)$stmt->fetchColumn());
    } catch (Throwable $e) {
        $stockGlobal = 0;
    }

    try {
        $tieneReservado = function_exists('emxDbColumnExists') ? emxDbColumnExists($pdo, 'inventario_sucursal', 'stock_reservado') : true;
        $sql = $tieneReservado
            ? "SELECT COUNT(*) AS filas, COALESCE(SUM(GREATEST(stock - COALESCE(stock_reservado,0),0)),0) AS disponible FROM inventario_sucursal WHERE producto_id = ?"
            : "SELECT COUNT(*) AS filas, COALESCE(SUM(GREATEST(stock,0)),0) AS disponible FROM inventario_sucursal WHERE producto_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$producto_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $filas = (int)($row['filas'] ?? 0);
        $stockSucursalesDisponible = max(0, (int)($row['disponible'] ?? 0));

        // Si hay inventario por sucursal, ese es el stock vendible real.
        // Esto evita que una reserva por entrega total siga apareciendo como disponible.
        if ($filas >0) return $stockSucursalesDisponible;
    } catch (Throwable $e) {
        // Si no existe inventario_sucursal o falla la consulta, usamos el global como respaldo.
    }

    return $stockGlobal;
}

function emxObtenerCostoReferenciaProducto($pdo, $producto_id, $precio_fallback = 0) {
    try {
        $tieneCosto = function_exists('emxDbColumnExists') ? emxDbColumnExists($pdo, 'productos', 'costo_unitario') : true;
        if ($tieneCosto) {
            $stmt = $pdo->prepare("SELECT COALESCE(NULLIF(costo_unitario,0), precio_base * 0.70, ?) FROM productos WHERE id = ?");
            $stmt->execute([(float)$precio_fallback, $producto_id]);
            return (float)$stmt->fetchColumn();
        }
    } catch (Throwable $e) {
        // Si tu BD todavía no tiene costo_unitario, usamos una estimación conservadora.
    }
    return max(0.01, (float)$precio_fallback * 0.70);
}

function emxObtenerCapacidadProveedores($pdo, $producto_id) {
    $stmt = $pdo->prepare("\n        SELECT u.id AS proveedor_id, u.nombres, u.apellidos, u.email,\n               COALESCE(cp.unidades_disponibles,0) AS unidades_disponibles,\n               COALESCE(cp.capacidad_diaria,1) AS capacidad_diaria,\n               COALESCE(cp.tiempo_entrega_estandar,5) AS tiempo_entrega_estandar,\n               COALESCE(cp.distancia_km,0) AS distancia_km,\n               COALESCE(cp.tasa_defectos_fabrica,0.05) AS tasa_defectos_fabrica,\n               COALESCE(cp.descuentos_volumen,'[]'::jsonb) AS descuentos_volumen\n        FROM producto_proveedor pp\n        JOIN usuarios u ON u.id = pp.proveedor_id\n        LEFT JOIN capacidad_proveedor cp ON cp.proveedor_id = pp.proveedor_id AND cp.producto_id = pp.producto_id\n        WHERE pp.producto_id = ?\n        ORDER BY cp.unidades_disponibles DESC NULLS LAST, cp.tiempo_entrega_estandar ASC NULLS LAST\n        LIMIT 5\n    ");
    $stmt->execute([$producto_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function emxCalcularDescuentoProveedor($descuentosJson, $cantidad) {
    $rangos = json_decode($descuentosJson ?: '[]', true);
    if (!is_array($rangos)) return 0;
    $mejor = 0;
    foreach ($rangos as $r) {
        $min = (int)($r['cantidad_min'] ?? 0);
        $max = isset($r['cantidad_max']) && $r['cantidad_max'] !== '' ? (int)$r['cantidad_max'] : null;
        if ($cantidad >= $min && ($max === null || $cantidad <= $max)) {
            $mejor = max($mejor, (float)($r['descuento'] ?? 0));
        }
    }
    return $mejor;
}

function emxNombreProveedor($prov) {
    $nombre = trim(($prov['nombres'] ?? '') . ' ' . ($prov['apellidos'] ?? ''));
    return $nombre !== '' ? $nombre : ($prov['email'] ?? 'Proveedor');
}

function emxFechaDesdeDias($dias) {
    return (new DateTime('today'))->modify('+' . max(0, (int)$dias) . ' days')->format('Y-m-d');
}

function emxFechaLegible($fechaIso) {
    if (!$fechaIso) return 'Por confirmar';
    try {
        $dt = new DateTime($fechaIso);
        $meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        return $dt->format('d') . ' ' . $meses[(int)$dt->format('n') - 1] . ' ' . $dt->format('Y');
    } catch (Throwable $e) {
        return 'Por confirmar';
    }
}


function emxBackorderColumnExists($pdo, $tabla, $columna) {
    if (function_exists('emxDbColumnExists')) return emxDbColumnExists($pdo, $tabla, $columna);
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

function emxObtenerPoliticaInventarioProducto($pdo, $producto_id) {
    try {
        $stmt = $pdo->prepare("SELECT COALESCE(punto_reorden,0) AS punto_reorden, COALESCE(stock_maximo,0) AS stock_maximo, COALESCE(stock_actual_global,0) AS stock_actual_global FROM productos WHERE id = ?");
        $stmt->execute([$producto_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'punto_reorden' =>max(0, (int)($row['punto_reorden'] ?? 0)),
            'stock_maximo' =>max(0, (int)($row['stock_maximo'] ?? 0)),
            'stock_actual_global' =>max(0, (int)($row['stock_actual_global'] ?? 0)),
        ];
    } catch (Throwable $e) {
        return ['punto_reorden' =>0, 'stock_maximo' =>0, 'stock_actual_global' =>0];
    }
}

function emxCantidadReposicionMinima($pdo, $producto_id, $faltante_cliente) {
    $politica = emxObtenerPoliticaInventarioProducto($pdo, $producto_id);
    $puntoReorden = max(0, (int)($politica['punto_reorden'] ?? 0));
    // Regla académica elegida: nunca pedir solo el faltante del cliente si eso deja el inventario en cero.
    // Se pide faltante + punto de reorden para que, al completar el pedido, quede un colchón mínimo.
    return max(0, $puntoReorden);
}

function emxCalcularCantidadSolicitudInterna($pdo, $producto_id, $faltante_cliente) {
    $faltante = max(0, (int)$faltante_cliente);
    $reposicion = emxCantidadReposicionMinima($pdo, $producto_id, $faltante);
    return [
        'faltante_cliente' =>$faltante,
        'reposicion_minima' =>$reposicion,
        'cantidad_solicitud_interna' =>$faltante + $reposicion,
        'criterio' =>'faltante_cliente + punto_reorden'
    ];
}

function emxActualizarStockProductoDisponible($pdo, $producto_id, $delta) {
    $delta = (int)$delta;
    if ($delta === 0) return;
    if ($delta >0) {
        $stmt = $pdo->prepare("UPDATE productos SET stock_actual_global = COALESCE(stock_actual_global,0) + ? WHERE id = ?");
        $stmt->execute([$delta, $producto_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE productos SET stock_actual_global = GREATEST(COALESCE(stock_actual_global,0) - ?, 0) WHERE id = ?");
        $stmt->execute([abs($delta), $producto_id]);
    }
}

function emxAplicarStockInmediatoDespuesPago($pdo, $sucursal_id, $producto_id, $cantidad, $modo_entrega) {
    $cantidad = max(0, (int)$cantidad);
    if ($cantidad <= 0) return ['cantidad' =>0, 'accion' =>'sin_stock_inmediato'];

    $modo = $modo_entrega === 'total' ? 'total' : 'parcial';
    $tieneReservado = emxBackorderColumnExists($pdo, 'inventario_sucursal', 'stock_reservado');

    if ($modo === 'total' && $tieneReservado) {
        // Entrega total: no se despacha todavía, pero se aparta el stock físico existente.
        $stmt = $pdo->prepare("UPDATE inventario_sucursal SET stock_reservado = COALESCE(stock_reservado,0) + ? WHERE sucursal_id = ? AND producto_id = ? AND GREATEST(stock - COALESCE(stock_reservado,0),0) >= ?");
        $stmt->execute([$cantidad, $sucursal_id, $producto_id, $cantidad]);
        if ($stmt->rowCount() === 0) throw new Exception('El stock disponible cambió mientras procesabas la compra. Actualiza el carrito.');
        emxActualizarStockProductoDisponible($pdo, $producto_id, -$cantidad);
        return ['cantidad' =>$cantidad, 'accion' =>'reservado_entrega_total'];
    }

    // Entrega parcial o BD sin stock_reservado: se descuenta físicamente para evitar reventa.
    $stmt = $pdo->prepare("UPDATE inventario_sucursal SET stock = stock - ? WHERE sucursal_id = ? AND producto_id = ? AND stock >= ?");
    $stmt->execute([$cantidad, $sucursal_id, $producto_id, $cantidad]);
    if ($stmt->rowCount() === 0) throw new Exception('El stock cambió mientras procesabas la compra. Actualiza el carrito.');
    emxActualizarStockProductoDisponible($pdo, $producto_id, -$cantidad);
    return ['cantidad' =>$cantidad, 'accion' =>$modo === 'total' ? 'descontado_reserva_fallback' : 'despachado_parcial'];
}

function emxCrearSolicitudReabastecimientoBackorder($pdo, $producto_id, $cantidad_necesaria, $backorder_id = null, $motivo = '') {
    $cantidad = max(1, (int)$cantidad_necesaria);
    $tieneSucursalMatriz = emxBackorderColumnExists($pdo, 'solicitudes_reabastecimiento', 'sucursal_matriz_id');
    $tieneNotas = emxBackorderColumnExists($pdo, 'solicitudes_reabastecimiento', 'notas_admin');
    $tieneBackorder = emxBackorderColumnExists($pdo, 'solicitudes_reabastecimiento', 'backorder_id');

    $sucursalMatrizId = null;
    if ($tieneSucursalMatriz) {
        try {
            $stmtM = $pdo->query("SELECT id FROM sucursales WHERE es_matriz = TRUE LIMIT 1");
            $sucursalMatrizId = $stmtM->fetchColumn() ?: null;
        } catch (Throwable $e) {
            $sucursalMatrizId = null;
        }
    }

    // Si ya existe una solicitud activa para este backorder, no se duplica.
    if ($tieneBackorder && $backorder_id) {
        $check = $pdo->prepare("SELECT id FROM solicitudes_reabastecimiento WHERE backorder_id = ? AND estado IN ('pendiente','cotizada','en_revision','aprobada') LIMIT 1");
        $check->execute([$backorder_id]);
        $existente = $check->fetchColumn();
        if ($existente) return $existente;
    }

    $nota = $motivo ?: 'Generada por pedido con sobrestock. Cantidad = faltante del cliente + punto de reorden.';
    $cols = ['producto_id', 'cantidad_necesaria', 'estado', 'fecha_limite', 'created_at'];
    $vals = ['?', '?', "'pendiente'", "CURRENT_DATE + INTERVAL '7 days'", 'NOW()'];
    $params = [$producto_id, $cantidad];

    if ($tieneSucursalMatriz) {
        $cols[] = 'sucursal_matriz_id';
        $vals[] = '?';
        $params[] = $sucursalMatrizId;
    }
    if ($tieneNotas) {
        $cols[] = 'notas_admin';
        $vals[] = '?';
        $params[] = $nota;
    }
    if ($tieneBackorder && $backorder_id) {
        $cols[] = 'backorder_id';
        $vals[] = '?';
        $params[] = $backorder_id;
    }

    $sql = "INSERT INTO solicitudes_reabastecimiento (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ") RETURNING id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $solicitudId = $stmt->fetchColumn();

    if (function_exists('emxGenerarCotizacionesSimuladas')) {
        emxGenerarCotizacionesSimuladas($pdo, $solicitudId, $producto_id, $cantidad);
    }

    return $solicitudId;
}

function emxEventoCalendarioCliente($titulo, $cantidad, $dias, $tipo = 'programado', $descripcion = '') {
    $dias = max(0, (int)$dias);
    $fecha = emxFechaDesdeDias($dias);
    return [
        'titulo' =>$titulo,
        'cantidad' =>max(0, (int)$cantidad),
        'dias' =>$dias,
        'fecha' =>$fecha,
        'fecha_legible' =>emxFechaLegible($fecha),
        'tipo' =>$tipo,
        'descripcion' =>$descripcion,
    ];
}

function emxEvaluarProveedorBackorder($prov, $cantidad, $costoReferencia) {
    $cantidad = max(1, (int)$cantidad);
    $disponibles = max(0, (int)($prov['unidades_disponibles'] ?? 0));
    $capacidadDia = max(1, (int)($prov['capacidad_diaria'] ?? 1));
    $diasBase = max(1, (int)($prov['tiempo_entrega_estandar'] ?? 5));
    $tasaDefectos = max(0, (float)($prov['tasa_defectos_fabrica'] ?? 0.05));
    $distancia = max(0, (float)($prov['distancia_km'] ?? 0));
    $descuento = emxCalcularDescuentoProveedor($prov['descuentos_volumen'] ?? '[]', $cantidad);

    $cantidadAProducir = max(0, $cantidad - $disponibles);
    $diasProduccion = (int)ceil($cantidadAProducir / $capacidadDia);
    $diasLogisticaExtra = $distancia >0 ? (int)ceil($distancia / 500) : 0;
    $diasTotal = max(1, $diasBase + $diasProduccion + $diasLogisticaExtra);
    $costoUnitarioEstimado = max(0.01, (float)$costoReferencia) * (1 - ($descuento / 100));
    $costoTotalEstimado = $costoUnitarioEstimado * $cantidad;
    $coberturaInmediata = min($cantidad, $disponibles);

    // Menor puntaje = más conveniente para la empresa.
    // Se usa internamente: cliente ve fechas, no ranking ni costos de empresa.
    $score = ($costoUnitarioEstimado * 0.50)
           + ($diasTotal * 3.50)
           + ($tasaDefectos * 100)
           - ($coberturaInmediata * 0.05);

    return [
        'proveedor_id' =>$prov['proveedor_id'],
        'proveedor' =>emxNombreProveedor($prov),
        'cantidad' =>$cantidad,
        'dias' =>$diasTotal,
        'fecha' =>emxFechaDesdeDias($diasTotal),
        'fecha_legible' =>emxFechaLegible(emxFechaDesdeDias($diasTotal)),
        'unidades_disponibles' =>$disponibles,
        'capacidad_diaria' =>$capacidadDia,
        'cantidad_a_producir' =>$cantidadAProducir,
        'dias_produccion' =>$diasProduccion,
        'dias_logistica_extra' =>$diasLogisticaExtra,
        'dias_base' =>$diasBase,
        'descuento' =>$descuento,
        'costo_unitario_estimado' =>round($costoUnitarioEstimado, 2),
        'precio_estimado' =>round($costoTotalEstimado, 2),
        'tasa_defectos' =>$tasaDefectos,
        'distancia_km' =>$distancia,
        'puntaje' =>round($score, 2),
        'tipo' =>'total'
    ];
}

function emxConstruirLotesParcialesConProveedor($prov, $faltante, $costoReferencia) {
    $faltante = max(0, (int)$faltante);
    if ($faltante <= 0) return [];

    $disponibles = max(0, (int)($prov['unidades_disponibles'] ?? 0));
    $capacidadDia = max(1, (int)($prov['capacidad_diaria'] ?? 1));
    $diasBase = max(1, (int)($prov['tiempo_entrega_estandar'] ?? 5));
    $distancia = max(0, (float)($prov['distancia_km'] ?? 0));
    $diasLogisticaExtra = $distancia >0 ? (int)ceil($distancia / 500) : 0;
    $diasMinEntrega = max(1, $diasBase + $diasLogisticaExtra);

    // Intervalo mínimo entre despachos para que el calendario se vea realista.
    // No es aleatorio: sale del tiempo estándar de entrega. Proveedores más lentos generan
    // despachos más separados; proveedores rápidos pueden despachar en ventanas más cortas.
    $intervaloDespacho = max(2, min(7, (int)ceil($diasBase / 2)));

    $lotes = [];
    $pendiente = $faltante;
    $ultimoDia = 0;

    // Primer lote: unidades que el proveedor ganador ya tiene listas.
    // Aun estando listas, se suma logística, porque deben llegar a la tienda/cliente.
    if ($disponibles >0 && $pendiente >0) {
        $cantidadLista = min($pendiente, $disponibles);
        $ev = emxEvaluarProveedorBackorder($prov, $cantidadLista, $costoReferencia);
        $ev['tipo'] = 'parcial';
        $ev['dias'] = max($diasMinEntrega, $ultimoDia + $intervaloDespacho);
        $ev['fecha'] = emxFechaDesdeDias($ev['dias']);
        $ev['fecha_legible'] = emxFechaLegible($ev['fecha']);
        $ev['cantidad_a_producir'] = 0;
        $ev['dias_produccion'] = 0;
        $ev['titulo_cliente'] = 'Despacho inicial';
        $ev['descripcion_cliente'] = 'Unidades listas del proveedor seleccionado. La fecha incluye revisión y traslado.';
        $lotes[] = $ev;
        $pendiente -= $cantidadLista;
        $ultimoDia = (int)$ev['dias'];
    }

    // Producción por tandas usando la capacidad diaria real declarada por el proveedor.
    // Para no saturar la pantalla, el calendario se agrupa en máximo 4 tandas visibles.
    // Cada tanda respeta capacidad diaria + tiempo logístico + intervalo mínimo entre despachos.
    $producidasAcumuladas = 0;
    $despachosMaximos = 4;
    $cantidadPorVentana = max(1, $capacidadDia * $intervaloDespacho);
    $loteProduccion = max($cantidadPorVentana, (int)ceil(max(1, $pendiente) / $despachosMaximos));

    while ($pendiente >0) {
        $cantidadLote = min($pendiente, $loteProduccion);
        $producidasAcumuladas += $cantidadLote;
        $diasProduccionAcumulada = (int)ceil($producidasAcumuladas / $capacidadDia);
        $diasLote = max(
            $diasMinEntrega + $diasProduccionAcumulada,
            $ultimoDia + $intervaloDespacho
        );

        $ev = emxEvaluarProveedorBackorder($prov, $cantidadLote, $costoReferencia);
        $ev['tipo'] = 'parcial';
        $ev['dias'] = $diasLote;
        $ev['fecha'] = emxFechaDesdeDias($diasLote);
        $ev['fecha_legible'] = emxFechaLegible($ev['fecha']);
        $ev['cantidad_a_producir'] = $cantidadLote;
        $ev['dias_produccion'] = $diasProduccionAcumulada;
        $ev['titulo_cliente'] = 'Despacho programado';
        $ev['descripcion_cliente'] = 'Tanda calculada con capacidad diaria de producción, revisión y traslado del proveedor seleccionado.';
        $lotes[] = $ev;

        $pendiente -= $cantidadLote;
        $ultimoDia = $diasLote;
    }

    return $lotes;
}

function emxConsolidarLotesPorFecha(array $lotes) {
    // Se mantiene por compatibilidad, pero el flujo nuevo usa un solo proveedor ganador,
    // por lo que no debería mezclar varios proveedores en una misma fecha.
    if (!$lotes) return [];
    usort($lotes, fn($a, $b) =>[($a['dias'] ?? 999), ($a['fecha'] ?? '')] <=>[($b['dias'] ?? 999), ($b['fecha'] ?? '')]);
    return $lotes;
}

function emxGenerarCalendarioBackorder($pdo, $producto_id, $cantidad, $precio_unitario = 0) {
    $cantidad = max(1, (int)$cantidad);
    $stock_actual = emxStockSucursalMasCercana($pdo, $producto_id);
    $faltante = max(0, $cantidad - $stock_actual);
    $politicaInterna = emxCalcularCantidadSolicitudInterna($pdo, $producto_id, $faltante);
    $cantidadEvaluacionProveedor = max($faltante, (int)($politicaInterna['cantidad_solicitud_interna'] ?? $faltante));
    $proveedores = emxObtenerCapacidadProveedores($pdo, $producto_id);
    $costoReferencia = emxObtenerCostoReferenciaProducto($pdo, $producto_id, $precio_unitario);

    $resultado = [
        'requiere_backorder' =>$faltante >0,
        'cantidad_solicitada' =>$cantidad,
        'stock_actual' =>$stock_actual,
        'despacho_inmediato' =>min($stock_actual, $cantidad),
        'faltante' =>$faltante,
        'reposicion_minima' =>(int)($politicaInterna['reposicion_minima'] ?? 0),
        'cantidad_solicitud_interna' =>(int)($politicaInterna['cantidad_solicitud_interna'] ?? $faltante),
        'criterio_reabastecimiento' =>$politicaInterna['criterio'] ?? 'faltante_cliente + punto_reorden',
        'proveedores_considerados' =>count($proveedores),
        'proveedores_evaluados' =>[],
        'ganador_total' =>null,
        'opcion_total' =>null,
        'opcion_parcial' =>null,
        'recomendacion' =>'stock_inmediato',
        'resumen' =>'Hay stock suficiente para despacho normal.',
        'formula' =>'Estimación conectada a capacidad diaria, unidades disponibles, tiempo estándar de entrega y distancia del proveedor.'
    ];

    if ($faltante <= 0) return $resultado;

    if (empty($proveedores)) {
        $resultado['resumen'] = 'No hay proveedores asociados al producto. El admin debe asignarlos antes de aceptar sobrestock.';
        $resultado['recomendacion'] = 'sin_proveedores';
        return $resultado;
    }

    // Se evalúan hasta 5 proveedores, pero para el cliente se muestra un calendario basado
    // en UN proveedor ganador. Esto simula la decisión de empresa/admin: costo, tiempo,
    // disponibilidad y riesgo. El cliente no ve el ranking interno.
    $evaluados = [];
    foreach ($proveedores as $prov) {
        $evaluados[] = emxEvaluarProveedorBackorder($prov, $cantidadEvaluacionProveedor, $costoReferencia);
    }
    usort($evaluados, fn($a, $b) =>$a['puntaje'] <=>$b['puntaje']);
    $mejorTotal = $evaluados[0] ?? null;
    $resultado['proveedores_evaluados'] = $evaluados;

    if (!$mejorTotal) {
        $resultado['recomendacion'] = 'sin_proveedores';
        return $resultado;
    }

    $proveedorGanador = null;
    foreach ($proveedores as $prov) {
        if ($prov['proveedor_id'] === $mejorTotal['proveedor_id']) {
            $proveedorGanador = $prov;
            break;
        }
    }
    if (!$proveedorGanador) $proveedorGanador = $proveedores[0];

    // Opción B: entrega total. Un solo despacho cuando todo esté completo.
    $mejorTotal['tipo'] = 'total';
    $mejorTotal['cantidad_cliente_pendiente'] = $faltante;
    $mejorTotal['cantidad_solicitud_interna'] = (int)($politicaInterna['cantidad_solicitud_interna'] ?? $faltante);
    $mejorTotal['reposicion_minima'] = (int)($politicaInterna['reposicion_minima'] ?? 0);
    $mejorTotal['cantidad'] = $faltante;
    $mejorTotal['eventos_calendario'] = [
        emxEventoCalendarioCliente(
            'Entrega total consolidada',
            $cantidad,
            (int)$mejorTotal['dias'],
            'total',
            'Recibes todas las unidades juntas en una sola entrega programada.'
        )
    ];
    $resultado['ganador_total'] = $mejorTotal;
    $resultado['opcion_total'] = $mejorTotal;

    // Opción A: entrega parcial. Se entrega primero lo inmediato y luego tandas del mismo proveedor ganador.
    $lotes = emxConstruirLotesParcialesConProveedor($proveedorGanador, $faltante, $costoReferencia);
    $fechaFinalParcial = !empty($lotes) ? max(array_column($lotes, 'fecha')) : null;
    $primerLoteDias = !empty($lotes) ? min(array_column($lotes, 'dias')) : null;
    $costoParcial = array_sum(array_column($lotes, 'precio_estimado'));
    $eventosParcial = [];
    if ($resultado['despacho_inmediato'] >0) {
        $eventosParcial[] = emxEventoCalendarioCliente(
            'Despacho inmediato',
            $resultado['despacho_inmediato'],
            0,
            'inmediato',
            'Unidades disponibles en la tienda/sucursal para despacho normal.'
        );
    }
    foreach ($lotes as $i =>$lote) {
        $eventosParcial[] = emxEventoCalendarioCliente(
            'Despacho ' . ($i + 1),
            (int)($lote['cantidad'] ?? 0),
            (int)($lote['dias'] ?? 0),
            'parcial',
            $lote['descripcion_cliente'] ?? 'Despacho programado según capacidad del proveedor.'
        );
    }

    $resultado['opcion_parcial'] = [
        'despacho_inmediato' =>$resultado['despacho_inmediato'],
        'lotes' =>$lotes,
        'lotes_internos' =>$lotes,
        'eventos_calendario' =>$eventosParcial,
        'fecha_final' =>$fechaFinalParcial,
        'fecha_final_legible' =>emxFechaLegible($fechaFinalParcial),
        'primer_lote_dias' =>$primerLoteDias,
        'cantidad_total' =>array_sum(array_column($lotes, 'cantidad')) + $resultado['despacho_inmediato'],
        'precio_estimado' =>round($costoParcial, 2),
        'cantidad_solicitud_interna' =>(int)($politicaInterna['cantidad_solicitud_interna'] ?? $faltante),
        'reposicion_minima' =>(int)($politicaInterna['reposicion_minima'] ?? 0),
        'proveedor_id' =>$mejorTotal['proveedor_id']
    ];

    // Recomendación orientativa para el cliente: parcial si recibe algo antes sin abrir demasiados despachos.
    $costoTotal = (float)$mejorTotal['precio_estimado'];
    $diasTotal = (int)$mejorTotal['dias'];
    $parcialLlegaAntes = $primerLoteDias !== null && $primerLoteDias < $diasTotal;
    $parcialNoMuyLarga = count($lotes) <= 4;
    $parcialNoMuyCara = $costoTotal <= 0 || $costoParcial <= ($costoTotal * 1.08);
    $resultado['recomendacion'] = ($parcialLlegaAntes && $parcialNoMuyLarga && $parcialNoMuyCara) ? 'parcial' : 'total';
    $resultado['resumen'] = "Solicitas {$cantidad} unidad(es). Hay {$stock_actual} disponible(s) y faltan {$faltante}. Puedes elegir entrega parcial o entrega total.";
    return $resultado;
}

function emxGuardarPlanBackorderEnSesion($producto_id, array $plan, $opcion) {
    if (!isset($_SESSION['backorder_planes'])) $_SESSION['backorder_planes'] = [];
    $_SESSION['backorder_planes'][$producto_id] = [
        'opcion' =>in_array($opcion, ['total', 'parcial'], true) ? $opcion : ($plan['recomendacion'] ?? 'total'),
        'plan' =>$plan,
        'aceptado_en' =>date('Y-m-d H:i:s')
    ];
}

function emxCarritoTieneBackorderPendiente() {
    foreach ($_SESSION['carrito'] ?? [] as $item) {
        $productoId = $item['producto_id'] ?? null;
        if (!$productoId) continue;
        $cantidad = (int)($item['cantidad'] ?? 0);
        $stock = (int)($item['stock'] ?? 0);
        $requiere = !empty($item['requiere_backorder']) || $cantidad >$stock;
        if ($requiere && empty($_SESSION['backorder_planes'][$productoId])) return true;
    }
    return false;
}
?>