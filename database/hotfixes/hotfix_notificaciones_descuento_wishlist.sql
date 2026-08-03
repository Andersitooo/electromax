-- Hotfix notificaciones de wishlist/descuentos
-- Ejecutar una vez si tu tabla notificaciones todavía no tiene estas columnas.

ALTER TABLE notificaciones
ADD COLUMN IF NOT EXISTS producto_id UUID NULL REFERENCES productos(id) ON DELETE SET NULL;

ALTER TABLE notificaciones
ADD COLUMN IF NOT EXISTS enlace_accion TEXT DEFAULT '#';

ALTER TABLE notificaciones
ADD COLUMN IF NOT EXISTS tipo_enlace VARCHAR(40) DEFAULT 'ninguno';

ALTER TABLE notificaciones
ADD COLUMN IF NOT EXISTS leida BOOLEAN DEFAULT FALSE;

ALTER TABLE productos
ADD COLUMN IF NOT EXISTS ultimo_descuento_notificado NUMERIC(10,2);

CREATE INDEX IF NOT EXISTS idx_wishlist_producto_usuario ON wishlist(producto_id, usuario_id);
CREATE INDEX IF NOT EXISTS idx_notificaciones_usuario_leida ON notificaciones(usuario_id, leida);
CREATE INDEX IF NOT EXISTS idx_notificaciones_producto ON notificaciones(producto_id);
