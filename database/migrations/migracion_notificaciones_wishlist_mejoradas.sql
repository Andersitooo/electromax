-- Mejoras opcionales para notificaciones de wishlist/ofertas.
-- No es obligatorio para que funcione el parche, pero ayuda a registrar el último descuento notificado.

ALTER TABLE productos
ADD COLUMN IF NOT EXISTS ultimo_descuento_notificado NUMERIC(10,2);

CREATE INDEX IF NOT EXISTS idx_wishlist_producto_usuario
ON wishlist(producto_id, usuario_id);

CREATE INDEX IF NOT EXISTS idx_notificaciones_usuario_leida
ON notificaciones(usuario_id, leida);
