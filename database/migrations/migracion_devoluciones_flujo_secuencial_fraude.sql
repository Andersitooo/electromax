-- Migración: devoluciones con flujo secuencial, fraude y reemplazos
-- Ejecutar una sola vez en PostgreSQL.

ALTER TABLE devoluciones
    ADD COLUMN IF NOT EXISTS historial_estados JSONB DEFAULT '[]'::jsonb,
    ADD COLUMN IF NOT EXISTS tipo_caso VARCHAR(50) DEFAULT 'devolucion',
    ADD COLUMN IF NOT EXISTS evidencia_tecnico JSONB DEFAULT '[]'::jsonb,
    ADD COLUMN IF NOT EXISTS numero_serie_validado VARCHAR(100),
    ADD COLUMN IF NOT EXISTS numero_serie_devuelto VARCHAR(100),
    ADD COLUMN IF NOT EXISTS tipo_dano VARCHAR(50),
    ADD COLUMN IF NOT EXISTS comentario_tecnico TEXT,
    ADD COLUMN IF NOT EXISTS fecha_inspeccion_tecnica TIMESTAMP,
    ADD COLUMN IF NOT EXISTS tecnico_id UUID REFERENCES usuarios(id),
    ADD COLUMN IF NOT EXISTS motivo_rechazo TEXT,
    ADD COLUMN IF NOT EXISTS codigo_guia VARCHAR(50),
    ADD COLUMN IF NOT EXISTS codigo_etiqueta VARCHAR(50),
    ADD COLUMN IF NOT EXISTS metodo_devolucion VARCHAR(30),
    ADD COLUMN IF NOT EXISTS fecha_recepcion TIMESTAMP,
    ADD COLUMN IF NOT EXISTS solucion_propuesta VARCHAR(50),
    ADD COLUMN IF NOT EXISTS respuesta_usuario VARCHAR(50) DEFAULT 'pendiente',
    ADD COLUMN IF NOT EXISTS pedido_reemplazo_id UUID REFERENCES pedidos(id),
    ADD COLUMN IF NOT EXISTS fraude_detectado BOOLEAN DEFAULT FALSE,
    ADD COLUMN IF NOT EXISTS motivos_fraude JSONB DEFAULT '[]'::jsonb,
    ADD COLUMN IF NOT EXISTS fecha_reemplazo_entregado TIMESTAMP,
    ADD COLUMN IF NOT EXISTS fecha_cierre TIMESTAMP;

UPDATE devoluciones SET historial_estados = '[]'::jsonb WHERE historial_estados IS NULL;
UPDATE devoluciones SET motivos_fraude = '[]'::jsonb WHERE motivos_fraude IS NULL;
UPDATE devoluciones SET fraude_detectado = FALSE WHERE fraude_detectado IS NULL;
UPDATE devoluciones SET respuesta_usuario = 'pendiente' WHERE respuesta_usuario IS NULL;

-- Reabrir como casos no finales aquellos cambios que quedaron en "cambio_despachado".
-- Ahora cambio_despachado NO es final; continúa a reemplazo_en_transito -> reemplazo_entregado -> cerrada.
