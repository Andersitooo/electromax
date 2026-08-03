-- MIGRACIÓN: estimación logística para pedidos de reemplazo por devolución
-- Ejecutar una sola vez.

ALTER TABLE pedidos
    ADD COLUMN IF NOT EXISTS fecha_estimada_entrega TIMESTAMP,
    ADD COLUMN IF NOT EXISTS prioridad_entrega VARCHAR(30) DEFAULT 'normal',
    ADD COLUMN IF NOT EXISTS mensaje_logistico TEXT,
    ADD COLUMN IF NOT EXISTS fecha_reestimada_en TIMESTAMP,
    ADD COLUMN IF NOT EXISTS motivo_reemplazo VARCHAR(100),
    ADD COLUMN IF NOT EXISTS pedido_original_id UUID REFERENCES pedidos(id),
    ADD COLUMN IF NOT EXISTS historial_estados JSONB DEFAULT '[]'::jsonb;

UPDATE pedidos
SET prioridad_entrega = 'normal'
WHERE prioridad_entrega IS NULL;

CREATE INDEX IF NOT EXISTS idx_pedidos_prioridad_entrega ON pedidos(prioridad_entrega);
CREATE INDEX IF NOT EXISTS idx_pedidos_fecha_estimada_entrega ON pedidos(fecha_estimada_entrega);
CREATE INDEX IF NOT EXISTS idx_pedidos_original_reemplazo ON pedidos(pedido_original_id);
