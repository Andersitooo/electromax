-- Migración: trazabilidad de series para reemplazos
-- Ejecutar una vez:
-- psql -d electro2 -f migracion_reemplazo_series_trazabilidad.sql

ALTER TABLE devoluciones
ADD COLUMN IF NOT EXISTS producto_id UUID;

ALTER TABLE devoluciones
ADD COLUMN IF NOT EXISTS detalle_pedido_id UUID;

ALTER TABLE devoluciones
ADD COLUMN IF NOT EXISTS series_originales_json JSONB DEFAULT '[]'::jsonb;

ALTER TABLE devoluciones
ADD COLUMN IF NOT EXISTS series_reemplazo_json JSONB DEFAULT '[]'::jsonb;

ALTER TABLE devoluciones
ADD COLUMN IF NOT EXISTS acta_reemplazo_json JSONB DEFAULT '{}'::jsonb;

ALTER TABLE devoluciones
ADD COLUMN IF NOT EXISTS fecha_series_reemplazo TIMESTAMP;

ALTER TABLE detalle_pedidos
ADD COLUMN IF NOT EXISTS numero_serie_vendido TEXT;

ALTER TABLE detalle_pedidos
ADD COLUMN IF NOT EXISTS es_reemplazo BOOLEAN DEFAULT FALSE;

ALTER TABLE detalle_pedidos
ADD COLUMN IF NOT EXISTS detalle_original_id UUID;

ALTER TABLE detalle_pedidos
ADD COLUMN IF NOT EXISTS series_originales_json JSONB DEFAULT '[]'::jsonb;

ALTER TABLE detalle_pedidos
ADD COLUMN IF NOT EXISTS series_reemplazo_json JSONB DEFAULT '[]'::jsonb;

ALTER TABLE detalle_pedidos
ADD COLUMN IF NOT EXISTS trazabilidad_reemplazo_json JSONB DEFAULT '{}'::jsonb;

CREATE INDEX IF NOT EXISTS idx_devoluciones_pedido_reemplazo
ON devoluciones(pedido_id, pedido_reemplazo_id);

CREATE INDEX IF NOT EXISTS idx_detalle_pedidos_reemplazo
ON detalle_pedidos(pedido_id, es_reemplazo);
