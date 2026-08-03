-- Hotfix definitivo: notificaciones automáticas de wishlist desde la base de datos
-- Esto soluciona cuando el admin actualiza un descuento visualmente pero no se dispara la notificación en PHP.
-- Ejecutar una sola vez:
-- psql -d electro2 -f hotfix_notificaciones_wishlist_trigger.sql

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Asegura columnas que usan las notificaciones del sistema.
ALTER TABLE notificaciones
ADD COLUMN IF NOT EXISTS producto_id UUID NULL REFERENCES productos(id) ON DELETE SET NULL;

ALTER TABLE notificaciones
ADD COLUMN IF NOT EXISTS enlace_accion TEXT DEFAULT '#';

ALTER TABLE notificaciones
ADD COLUMN IF NOT EXISTS tipo_enlace VARCHAR(40) DEFAULT 'ninguno';

ALTER TABLE notificaciones
ADD COLUMN IF NOT EXISTS leida BOOLEAN DEFAULT FALSE;

ALTER TABLE notificaciones
ADD COLUMN IF NOT EXISTS creado_en TIMESTAMP DEFAULT NOW();

ALTER TABLE productos
ADD COLUMN IF NOT EXISTS ultimo_descuento_notificado NUMERIC(10,2);

CREATE INDEX IF NOT EXISTS idx_wishlist_producto_usuario ON wishlist(producto_id, usuario_id);
CREATE INDEX IF NOT EXISTS idx_notificaciones_usuario_leida ON notificaciones(usuario_id, leida);
CREATE INDEX IF NOT EXISTS idx_notificaciones_producto ON notificaciones(producto_id);

CREATE OR REPLACE FUNCTION emx_pct_descuento(v NUMERIC)
RETURNS NUMERIC AS $$
BEGIN
    IF COALESCE(v, 0) > 0 AND COALESCE(v, 0) <= 1 THEN
        RETURN ROUND((v * 100)::numeric, 2);
    END IF;
    RETURN ROUND(COALESCE(v, 0)::numeric, 2);
END;
$$ LANGUAGE plpgsql IMMUTABLE;

CREATE OR REPLACE FUNCTION emx_fmt_pct(v NUMERIC)
RETURNS TEXT AS $$
BEGIN
    RETURN regexp_replace(to_char(COALESCE(v, 0), 'FM999999990.00'), '\.?0+$', '');
END;
$$ LANGUAGE plpgsql IMMUTABLE;

CREATE OR REPLACE FUNCTION emx_notificar_wishlist_producto_update()
RETURNS TRIGGER AS $$
DECLARE
    old_desc_pct NUMERIC;
    new_desc_pct NUMERIC;
    usuarios_objetivo INTEGER := 0;
BEGIN
    -- Nunca bloquear la actualización del producto por un error de notificación.
    BEGIN
        SELECT COUNT(*) INTO usuarios_objetivo
        FROM wishlist w
        WHERE w.producto_id = NEW.id;

        IF usuarios_objetivo <= 0 THEN
            RETURN NEW;
        END IF;

        old_desc_pct := emx_pct_descuento(OLD.descuento_porcentaje);
        new_desc_pct := emx_pct_descuento(NEW.descuento_porcentaje);

        -- Descuento agregado o cambiado: 0→10, 10→20, 20→15, etc.
        IF new_desc_pct > 0 AND ABS(new_desc_pct - old_desc_pct) > 0.009 THEN
            INSERT INTO notificaciones
                (usuario_id, tipo, titulo, mensaje, producto_id, enlace_accion, tipo_enlace, leida, creado_en)
            SELECT DISTINCT
                w.usuario_id,
                'descuento_wishlist',
                NEW.nombre || ' tiene descuento',
                CASE
                    WHEN old_desc_pct > 0 THEN
                        'El descuento de este producto cambió de ' || emx_fmt_pct(old_desc_pct) || '% a ' || emx_fmt_pct(new_desc_pct) || '%.'
                    ELSE
                        'El producto que tienes en tu lista de deseos ahora tiene ' || emx_fmt_pct(new_desc_pct) || '% de descuento.'
                END,
                NEW.id,
                'producto.php?id=' || NEW.id::text,
                'producto',
                FALSE,
                NOW()
            FROM wishlist w
            WHERE w.producto_id = NEW.id;

            UPDATE productos
            SET ultimo_descuento_notificado = new_desc_pct
            WHERE id = NEW.id;
        END IF;

        -- Volvió stock.
        IF COALESCE(OLD.stock_actual_global, 0) <= 0 AND COALESCE(NEW.stock_actual_global, 0) > 0 THEN
            INSERT INTO notificaciones
                (usuario_id, tipo, titulo, mensaje, producto_id, enlace_accion, tipo_enlace, leida, creado_en)
            SELECT DISTINCT
                w.usuario_id,
                'stock_disponible',
                NEW.nombre || ' ya está disponible',
                'El producto que tienes en tu lista de deseos volvió a tener stock.',
                NEW.id,
                'producto.php?id=' || NEW.id::text,
                'producto',
                FALSE,
                NOW()
            FROM wishlist w
            WHERE w.producto_id = NEW.id;
        END IF;

        -- Bajó precio base.
        IF COALESCE(OLD.precio_base, 0) > 0
           AND COALESCE(NEW.precio_base, 0) > 0
           AND COALESCE(NEW.precio_base, 0) < COALESCE(OLD.precio_base, 0) THEN
            INSERT INTO notificaciones
                (usuario_id, tipo, titulo, mensaje, producto_id, enlace_accion, tipo_enlace, leida, creado_en)
            SELECT DISTINCT
                w.usuario_id,
                'precio_bajo_wishlist',
                NEW.nombre || ' bajó de precio',
                'El producto de tu lista de deseos bajó de $' ||
                    to_char(OLD.precio_base, 'FM999999990.00') ||
                    ' a $' ||
                    to_char(NEW.precio_base, 'FM999999990.00') ||
                    '.',
                NEW.id,
                'producto.php?id=' || NEW.id::text,
                'producto',
                FALSE,
                NOW()
            FROM wishlist w
            WHERE w.producto_id = NEW.id;
        END IF;

    EXCEPTION WHEN OTHERS THEN
        RAISE NOTICE 'No se pudo crear notificación wishlist para producto %: %', NEW.id, SQLERRM;
    END;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_emx_wishlist_producto_update ON productos;

CREATE TRIGGER trg_emx_wishlist_producto_update
AFTER UPDATE OF descuento_porcentaje, precio_base, stock_actual_global
ON productos
FOR EACH ROW
EXECUTE FUNCTION emx_notificar_wishlist_producto_update();

-- Consulta de prueba para confirmar que el trigger existe:
-- SELECT tgname FROM pg_trigger WHERE tgname = 'trg_emx_wishlist_producto_update';
