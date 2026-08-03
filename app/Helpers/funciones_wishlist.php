<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_wishlist.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

// funciones_wishlist.php

function agregarAWishlist($pdo, $usuario_id, $producto_id) {
    try {
        $stmt = $pdo->prepare("INSERT INTO wishlist (usuario_id, producto_id) VALUES (?, ?) ON CONFLICT (usuario_id, producto_id) DO NOTHING");
        $stmt->execute([$usuario_id, $producto_id]);
        return true;
    } catch (Exception $e) {
        error_log("Error wishlist: " . $e->getMessage());
        return false;
    }
}

function eliminarDeWishlist($pdo, $usuario_id, $producto_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE usuario_id = ? AND producto_id = ?");
        $stmt->execute([$usuario_id, $producto_id]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function obtenerWishlist($pdo, $usuario_id) {
    $stmt = $pdo->prepare("
        SELECT w.producto_id, w.creado_en, p.nombre, p.precio_base, p.descuento_porcentaje, 
               p.stock_actual_global, pm.url as imagen
        FROM wishlist w
        JOIN productos p ON w.producto_id = p.id
        LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.orden = 1
        WHERE w.usuario_id = ? AND p.deleted_at IS NULL
        ORDER BY w.creado_en DESC
    ");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function estaEnWishlist($pdo, $usuario_id, $producto_id) {
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE usuario_id = ? AND producto_id = ?");
    $stmt->execute([$usuario_id, $producto_id]);
    return $stmt->fetch() !== false;
}


function emxWishlistTableExists($pdo, $tabla) {
    static $cache = [];
    if (array_key_exists($tabla, $cache)) return $cache[$tabla];
    try {
        $st = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name=? LIMIT 1");
        $st->execute([$tabla]);
        return $cache[$tabla] = (bool)$st->fetchColumn();
    } catch (Throwable $e) {
        return $cache[$tabla] = false;
    }
}

function emxWishlistColExists($pdo, $tabla, $columna) {
    static $cache = [];
    $key = $tabla . '.' . $columna;
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $st = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=? AND column_name=? LIMIT 1");
        $st->execute([$tabla, $columna]);
        return $cache[$key] = (bool)$st->fetchColumn();
    } catch (Throwable $e) {
        return $cache[$key] = false;
    }
}

function emxWishlistFechaCol($pdo) {
    if (emxWishlistColExists($pdo, 'notificaciones', 'creado_en')) return 'creado_en';
    if (emxWishlistColExists($pdo, 'notificaciones', 'created_at')) return 'created_at';
    return null;
}

function emxWishlistNotificacionDuplicadaReciente($pdo, $usuario_id, $tipo, $mensaje, $producto_id = null) {
    try {
        if (!emxWishlistTableExists($pdo, 'notificaciones')) return false;
        $fechaCol = emxWishlistFechaCol($pdo);

        $where = ["usuario_id = ?", "tipo = ?", "mensaje = ?"];
        $params = [$usuario_id, $tipo, $mensaje];

        if (emxWishlistColExists($pdo, 'notificaciones', 'producto_id')) {
            $where[] = "producto_id = ?";
            $params[] = $producto_id;
        }

        if ($fechaCol) {
            $where[] = "{$fechaCol} >= NOW() - INTERVAL '2 minutes'";
        }

        $sql = "SELECT 1 FROM notificaciones WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return (bool)$st->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function crearNotificacion($pdo, $usuario_id, $tipo, $titulo, $mensaje, $producto_id = null, $enlace = null, $tipo_enlace = null) {
    try {
        if (!emxWishlistTableExists($pdo, 'notificaciones')) return false;

        $enlace = $enlace ?: ($producto_id ? 'producto.php?id=' . urlencode((string)$producto_id) : '#');
        $tipo_enlace = $tipo_enlace ?: ($producto_id ? 'producto' : 'ninguno');

        if (emxWishlistNotificacionDuplicadaReciente($pdo, $usuario_id, $tipo, $mensaje, $producto_id)) {
            return false;
        }

        $data = [];
        foreach ([
            'usuario_id' => $usuario_id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'producto_id' => $producto_id,
            'enlace_accion' => $enlace,
            'tipo_enlace' => $tipo_enlace,
            'leida' => false,
        ] as $col => $val) {
            if (emxWishlistColExists($pdo, 'notificaciones', $col)) {
                $data[$col] = $val;
            }
        }

        $fechaCol = emxWishlistFechaCol($pdo);
        if ($fechaCol) {
            $data[$fechaCol] = date('Y-m-d H:i:s');
        }

        if (empty($data['usuario_id'] ?? null) || empty($data['titulo'] ?? null) || empty($data['mensaje'] ?? null)) {
            return false;
        }

        $cols = array_keys($data);
        $holders = array_fill(0, count($cols), '?');
        $sql = "INSERT INTO notificaciones (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $holders) . ")";
        $st = $pdo->prepare($sql);
        return $st->execute(array_values($data));
    } catch (Throwable $e) {
        error_log("Error notificación flexible: " . $e->getMessage());
        return false;
    }
}

function obtenerNotificaciones($pdo, $usuario_id, $limit = 10) {
    $fechaCol = emxWishlistFechaCol($pdo);
    $fechaSelect = $fechaCol ? "n.{$fechaCol} AS creado_en" : "NOW() AS creado_en";
    $orderCol = $fechaCol ? "n.{$fechaCol}" : "n.id";
    $prodSelect = emxWishlistColExists($pdo, 'notificaciones', 'producto_id') ? 'n.producto_id' : 'NULL AS producto_id';
    $joinProducto = emxWishlistColExists($pdo, 'notificaciones', 'producto_id') ? 'n.producto_id = p.id' : 'FALSE';

    $stmt = $pdo->prepare("
        SELECT n.*, {$fechaSelect}, {$prodSelect}, p.nombre as producto_nombre, pm.url as producto_imagen
        FROM notificaciones n
        LEFT JOIN productos p ON {$joinProducto}
        LEFT JOIN producto_multimedia pm ON p.id = pm.producto_id AND pm.orden = 1
        WHERE n.usuario_id = ?
        ORDER BY {$orderCol} DESC
        LIMIT ?
    ");
    $stmt->execute([$usuario_id, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function contarNotificacionesNoLeidas($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND COALESCE(leida,false) = FALSE");
    $stmt->execute([$usuario_id]);
    return (int)$stmt->fetchColumn();
}

function marcarNotificacionLeida($pdo, $notificacion_id, $usuario_id) {
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = TRUE WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$notificacion_id, $usuario_id]);
}

function marcarTodasNotificacionesLeidas($pdo, $usuario_id) {
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = TRUE WHERE usuario_id = ? AND COALESCE(leida,false) = FALSE");
    $stmt->execute([$usuario_id]);
}

function emxWishlistDescuentoPct($valor) {
    $valor = (float)$valor;
    if ($valor > 0 && $valor <= 1) return round($valor * 100, 2);
    return round($valor, 2);
}

function emxWishlistProductoBase($pdo, $producto_id) {
    $stmt = $pdo->prepare("SELECT id, nombre, precio_base, descuento_porcentaje, descuento_desde, descuento_hasta, stock_actual_global FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function emxWishlistUsuariosProducto($pdo, $producto_id) {
    try {
        $whereUsuario = '';
        if (emxWishlistColExists($pdo, 'usuarios', 'deleted_at')) $whereUsuario .= " AND u.deleted_at IS NULL";
        if (emxWishlistColExists($pdo, 'usuarios', 'is_active')) $whereUsuario .= " AND COALESCE(u.is_active,true)=true";

        $sql = "SELECT DISTINCT w.usuario_id
                FROM wishlist w
                JOIN usuarios u ON u.id = w.usuario_id
                WHERE w.producto_id = ? {$whereUsuario}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$producto_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        $stmt = $pdo->prepare("SELECT DISTINCT usuario_id FROM wishlist WHERE producto_id = ?");
        $stmt->execute([$producto_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

function notificarStockDisponible($pdo, $producto_id) {
    $enviadas = 0;
    try {
        $producto = emxWishlistProductoBase($pdo, $producto_id);
        if (!$producto) return 0;

        foreach (emxWishlistUsuariosProducto($pdo, $producto_id) as $usuario_id) {
            $titulo = $producto['nombre'] . " ya está disponible";
            $mensaje = "El producto que tienes en tu lista de deseos volvió a tener stock.";
            if (crearNotificacion($pdo, $usuario_id, 'stock_disponible', $titulo, $mensaje, $producto_id)) {
                $enviadas++;
            }
        }
    } catch (Throwable $e) {
        error_log("Error notificarStockDisponible: " . $e->getMessage());
    }
    return $enviadas;
}

function notificarDescuentoWishlist($pdo, $producto_id, $descuento, $descuento_anterior = null) {
    $enviadas = 0;
    try {
        $producto = emxWishlistProductoBase($pdo, $producto_id);
        if (!$producto) return 0;

        $nuevoPct = emxWishlistDescuentoPct($descuento);
        $oldPct = $descuento_anterior === null ? null : emxWishlistDescuentoPct($descuento_anterior);
        if ($nuevoPct <= 0) return 0;

        $titulo = $producto['nombre'] . " está en oferta";
        if ($oldPct !== null && $oldPct > 0 && abs($nuevoPct - $oldPct) > 0.009) {
            $mensaje = "El descuento de este producto cambió de " . rtrim(rtrim(number_format($oldPct, 2), '0'), '.') . "% a " . rtrim(rtrim(number_format($nuevoPct, 2), '0'), '.') . "%.";
        } else {
            $mensaje = "El producto que tienes en tu lista de deseos ahora tiene " . rtrim(rtrim(number_format($nuevoPct, 2), '0'), '.') . "% de descuento.";
        }

        foreach (emxWishlistUsuariosProducto($pdo, $producto_id) as $usuario_id) {
            if (crearNotificacion($pdo, $usuario_id, 'descuento_wishlist', $titulo, $mensaje, $producto_id)) {
                $enviadas++;
            }
        }

        if (emxWishlistColExists($pdo, 'productos', 'ultimo_descuento_notificado')) {
            $pdo->prepare("UPDATE productos SET ultimo_descuento_notificado = ? WHERE id = ?")->execute([$nuevoPct, $producto_id]);
        }
    } catch (Throwable $e) {
        error_log("Error notificarDescuentoWishlist: " . $e->getMessage());
    }
    return $enviadas;
}

function notificarPrecioBajoWishlist($pdo, $producto_id, $precio_anterior, $precio_nuevo) {
    $enviadas = 0;
    try {
        $producto = emxWishlistProductoBase($pdo, $producto_id);
        if (!$producto) return 0;

        $precio_anterior = (float)$precio_anterior;
        $precio_nuevo = (float)$precio_nuevo;
        if ($precio_anterior <= 0 || $precio_nuevo <= 0 || $precio_nuevo >= $precio_anterior) return 0;

        foreach (emxWishlistUsuariosProducto($pdo, $producto_id) as $usuario_id) {
            $titulo = $producto['nombre'] . " bajó de precio";
            $mensaje = "El producto de tu lista de deseos bajó de $" . number_format($precio_anterior, 2) . " a $" . number_format($precio_nuevo, 2) . ".";
            if (crearNotificacion($pdo, $usuario_id, 'precio_bajo_wishlist', $titulo, $mensaje, $producto_id)) {
                $enviadas++;
            }
        }
    } catch (Throwable $e) {
        error_log("Error notificarPrecioBajoWishlist: " . $e->getMessage());
    }
    return $enviadas;
}

function emxWishlistNotificarCambioProducto($pdo, $producto_id, array $old, array $nuevo) {
    $res = [
        'stock' => 0,
        'descuento' => 0,
        'precio' => 0,
        'usuarios_wishlist' => 0,
    ];

    try {
        $usuarios = emxWishlistUsuariosProducto($pdo, $producto_id);
        $res['usuarios_wishlist'] = count($usuarios);
        if ($res['usuarios_wishlist'] <= 0) return $res;

        $oldStock = (int)($old['stock_actual_global'] ?? 0);
        $newStock = (int)($nuevo['stock_actual_global'] ?? 0);
        if ($oldStock <= 0 && $newStock > 0) {
            $res['stock'] = notificarStockDisponible($pdo, $producto_id);
        }

        $oldDesc = (float)($old['descuento_porcentaje'] ?? 0);
        $newDesc = (float)($nuevo['descuento_porcentaje'] ?? 0);
        $oldPct = emxWishlistDescuentoPct($oldDesc);
        $newPct = emxWishlistDescuentoPct($newDesc);

        if ($newPct > 0 && abs($newPct - $oldPct) > 0.009) {
            $res['descuento'] = notificarDescuentoWishlist($pdo, $producto_id, $newDesc, $oldDesc);
        }

        $oldPrecio = (float)($old['precio_base'] ?? 0);
        $newPrecio = (float)($nuevo['precio_base'] ?? 0);
        if ($oldPrecio > 0 && $newPrecio > 0 && $newPrecio < $oldPrecio) {
            $res['precio'] = notificarPrecioBajoWishlist($pdo, $producto_id, $oldPrecio, $newPrecio);
        }
    } catch (Throwable $e) {
        error_log("Error emxWishlistNotificarCambioProducto: " . $e->getMessage());
    }

    return $res;
}
?>
