-- =====================================================================
-- MIGRACIÓN: Flujo guiado de pedidos, devoluciones e incidencias
-- Proyecto ElectroMax
-- Objetivo: permitir acciones secuenciales en admin y guardar historial.
-- =====================================================================

CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Historial y clasificación para devoluciones/incidencias.
ALTER TABLE devoluciones
    ADD COLUMN IF NOT EXISTS historial_estados JSONB DEFAULT '[]'::jsonb,
    ADD COLUMN IF NOT EXISTS tipo_caso VARCHAR(50) DEFAULT 'devolucion',
    ADD COLUMN IF NOT EXISTS evidencia_tecnico JSONB DEFAULT '[]'::jsonb,
    ADD COLUMN IF NOT EXISTS numero_serie_validado VARCHAR(100),
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

-- Si pedido_reemplazo_id fue creado por error como INT, convertirlo a UUID de forma segura.
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_name = 'devoluciones'
          AND column_name = 'pedido_reemplazo_id'
          AND data_type <> 'uuid'
    ) THEN
        ALTER TABLE devoluciones DROP COLUMN pedido_reemplazo_id;
    END IF;
END $$;

ALTER TABLE devoluciones
    ADD COLUMN IF NOT EXISTS pedido_reemplazo_id UUID REFERENCES pedidos(id);

-- Campos de soporte para pedidos.
ALTER TABLE pedidos
    ADD COLUMN IF NOT EXISTS historial_estados JSONB DEFAULT '[]'::jsonb,
    ADD COLUMN IF NOT EXISTS confirmacion_cliente_estado VARCHAR(30) DEFAULT 'pendiente',
    ADD COLUMN IF NOT EXISTS fotos_confirmacion JSONB DEFAULT '[]'::jsonb,
    ADD COLUMN IF NOT EXISTS comentario_confirmacion TEXT,
    ADD COLUMN IF NOT EXISTS fecha_limite_confirmacion TIMESTAMP,
    ADD COLUMN IF NOT EXISTS fecha_confirmacion_cliente TIMESTAMP,
    ADD COLUMN IF NOT EXISTS motivo_reemplazo VARCHAR(100),
    ADD COLUMN IF NOT EXISTS pedido_original_id UUID REFERENCES pedidos(id);

-- Campos de soporte para detalle de pedidos.
ALTER TABLE detalle_pedidos
    ADD COLUMN IF NOT EXISTS numero_serie_vendido TEXT,
    ADD COLUMN IF NOT EXISTS sucursal_origen_id UUID REFERENCES sucursales(id);

-- Si no existe reclamos_proveedor, se crea para los pocos casos reales de defecto de fábrica.
CREATE TABLE IF NOT EXISTS reclamos_proveedor (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    devolucion_id UUID REFERENCES devoluciones(id) ON DELETE SET NULL,
    producto_id UUID REFERENCES productos(id),
    numero_serie VARCHAR(100) NOT NULL,
    proveedor_id UUID REFERENCES usuarios(id),
    estado VARCHAR(30) DEFAULT 'pendiente',
    evidencia_fotos JSONB DEFAULT '[]'::jsonb,
    comentario_tecnico TEXT,
    tipo_reclamo VARCHAR(50) DEFAULT 'defecto_fabrica',
    solucion_propuesta VARCHAR(50),
    fecha_respuesta_proveedor TIMESTAMP,
    respuesta_proveedor TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Opcional pero útil: tabla para reclamar al courier cuando el daño/extravío no es de fábrica.
CREATE TABLE IF NOT EXISTS reclamos_courier (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    devolucion_id UUID REFERENCES devoluciones(id) ON DELETE SET NULL,
    pedido_id UUID REFERENCES pedidos(id) ON DELETE SET NULL,
    guia_envio VARCHAR(100),
    courier_nombre VARCHAR(200),
    motivo_reclamo VARCHAR(50) NOT NULL DEFAULT 'dano_transporte',
    monto_reclamado DECIMAL(10,2) DEFAULT 0,
    evidencia_fotos JSONB DEFAULT '[]'::jsonb,
    descripcion_dano TEXT,
    estado VARCHAR(30) DEFAULT 'pendiente',
    fecha_reclamo DATE DEFAULT CURRENT_DATE,
    fecha_respuesta_courier DATE,
    respuesta_courier TEXT,
    monto_compensacion DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Índices para que el admin cargue rápido.
CREATE INDEX IF NOT EXISTS idx_devoluciones_estado ON devoluciones(estado);
CREATE INDEX IF NOT EXISTS idx_devoluciones_tipo_caso ON devoluciones(tipo_caso);
CREATE INDEX IF NOT EXISTS idx_devoluciones_historial ON devoluciones USING GIN(historial_estados);
CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos(estado);
CREATE INDEX IF NOT EXISTS idx_reclamos_proveedor_devolucion ON reclamos_proveedor(devolucion_id);
CREATE INDEX IF NOT EXISTS idx_reclamos_courier_devolucion ON reclamos_courier(devolucion_id);

-- Normalizar estados históricos viejos al nuevo vocabulario.
UPDATE devoluciones SET estado = 'autorizada_retorno' WHERE estado = 'aprobada';
UPDATE devoluciones SET estado = 'en_camino_retorno' WHERE estado = 'en_proceso';
UPDATE devoluciones SET tipo_caso = 'incidencia_courier' WHERE motivo IN ('no_recibido', 'extravio_courier') AND (tipo_caso IS NULL OR tipo_caso = 'devolucion');
UPDATE devoluciones SET tipo_caso = 'incidencia_entrega' WHERE motivo = 'danado_envio' AND (tipo_caso IS NULL OR tipo_caso = 'devolucion');

-- Rellenar historial inicial donde esté vacío.
UPDATE devoluciones
SET historial_estados = jsonb_build_array(jsonb_build_object(
    'estado', estado,
    'descripcion', 'Historial inicial generado por migración de flujo guiado.',
    'fecha', to_char(COALESCE(created_at, NOW()), 'YYYY-MM-DD HH24:MI:SS'),
    'icono', 'fa-info-circle'
))
WHERE historial_estados IS NULL OR historial_estados = '[]'::jsonb;

UPDATE pedidos
SET historial_estados = jsonb_build_array(jsonb_build_object(
    'estado', estado,
    'descripcion', 'Historial inicial generado por migración de flujo guiado.',
    'fecha', to_char(COALESCE(created_at, NOW()), 'YYYY-MM-DD HH24:MI:SS'),
    'icono', 'fa-info-circle'
))
WHERE historial_estados IS NULL OR historial_estados = '[]'::jsonb;
