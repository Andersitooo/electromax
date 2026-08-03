-- ============================================================
-- MIGRACIÓN: Devoluciones con decisión del cliente
-- ElectroMax
--
-- Soluciona el error:
--   SQLSTATE[42703]: no existe la columna historial_estados
--
-- Y agrega soporte para el flujo:
--   Cliente reporta -> Admin revisa -> Técnico inspecciona ->
--   Admin ofrece reembolso/cambio -> Cliente elige -> Admin ejecuta.
-- ============================================================

CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

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
    ADD COLUMN IF NOT EXISTS respuesta_usuario VARCHAR(50) DEFAULT 'pendiente';

-- Si en una migración anterior pedido_reemplazo_id quedó con tipo incorrecto, se elimina y recrea como UUID.
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema='public'
          AND table_name='devoluciones'
          AND column_name='pedido_reemplazo_id'
          AND data_type <> 'uuid'
    ) THEN
        ALTER TABLE devoluciones DROP COLUMN pedido_reemplazo_id;
    END IF;
END $$;

ALTER TABLE devoluciones
    ADD COLUMN IF NOT EXISTS pedido_reemplazo_id UUID REFERENCES pedidos(id);

UPDATE devoluciones
SET historial_estados = '[]'::jsonb
WHERE historial_estados IS NULL;

UPDATE devoluciones
SET respuesta_usuario = 'pendiente'
WHERE respuesta_usuario IS NULL;

CREATE INDEX IF NOT EXISTS idx_devoluciones_historial ON devoluciones USING GIN(historial_estados);
CREATE INDEX IF NOT EXISTS idx_devoluciones_tipo_caso ON devoluciones(tipo_caso);
CREATE INDEX IF NOT EXISTS idx_devoluciones_respuesta_usuario ON devoluciones(respuesta_usuario);
CREATE INDEX IF NOT EXISTS idx_devoluciones_solucion ON devoluciones(solucion_propuesta);

-- Estados nuevos usados por el flujo:
-- esperando_decision_cliente
-- cliente_eligio_reembolso
-- cliente_eligio_cambio

-- Valores nuevos usados en solucion_propuesta:
-- opcion_reembolso
-- opcion_cambio
-- opcion_reembolso_cambio
-- reembolso_total
-- cambio_producto
