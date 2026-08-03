-- =============================================================
-- MIGRACIÓN REFORMULACIÓN SEGURA - ELECTROMAX
-- Ejecutar sobre electro2 antes de reemplazar archivos PHP.
-- =============================================================
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Historial y flujo guiado
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS historial_estados JSONB DEFAULT '[]'::jsonb;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS confirmacion_cliente_estado VARCHAR(30) DEFAULT 'pendiente';
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS fotos_confirmacion JSONB DEFAULT '[]'::jsonb;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS comentario_confirmacion TEXT;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS fecha_limite_confirmacion TIMESTAMP;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS fecha_confirmacion_cliente TIMESTAMP;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS motivo_reemplazo VARCHAR(100);
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS pedido_original_id UUID REFERENCES pedidos(id);

ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS historial_estados JSONB DEFAULT '[]'::jsonb;
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS tipo_caso VARCHAR(50) DEFAULT 'devolucion';
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS solucion_propuesta VARCHAR(50);
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS respuesta_usuario VARCHAR(50) DEFAULT 'pendiente';
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS codigo_etiqueta VARCHAR(50) UNIQUE;
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS codigo_guia VARCHAR(50);
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS metodo_devolucion VARCHAR(30);
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS fecha_recepcion TIMESTAMP;
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS numero_serie_devuelto VARCHAR(100);
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS numero_serie_validado VARCHAR(100);
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS motivo_rechazo TEXT;
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS evidencia_tecnico JSONB DEFAULT '[]'::jsonb;
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS tipo_dano VARCHAR(50);
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS comentario_tecnico TEXT;
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS fecha_inspeccion_tecnica TIMESTAMP;
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS tecnico_id UUID REFERENCES usuarios(id);
ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS pedido_reemplazo_id UUID REFERENCES pedidos(id);

-- Evita duplicar casos activos por pedido salvo que estén cerrados/rechazados.
CREATE INDEX IF NOT EXISTS idx_devoluciones_pedido_estado ON devoluciones(pedido_id, estado);
CREATE INDEX IF NOT EXISTS idx_devoluciones_tipo_caso ON devoluciones(tipo_caso);

-- Reclamos courier: útil cuando el transporte pierde o daña el pedido.
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
CREATE INDEX IF NOT EXISTS idx_reclamos_courier_pedido ON reclamos_courier(pedido_id);
CREATE INDEX IF NOT EXISTS idx_reclamos_courier_estado ON reclamos_courier(estado);

-- Proveedores y reabastecimiento
ALTER TABLE solicitudes_reabastecimiento ADD COLUMN IF NOT EXISTS notas_admin TEXT;
ALTER TABLE solicitudes_reabastecimiento ADD COLUMN IF NOT EXISTS backorder_id UUID;

CREATE TABLE IF NOT EXISTS capacidad_proveedor (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    proveedor_id UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    producto_id UUID NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    capacidad_diaria INTEGER NOT NULL DEFAULT 0,
    capacidad_semanal INTEGER NOT NULL DEFAULT 0,
    capacidad_maxima_pedido INTEGER NOT NULL DEFAULT 0,
    tiempo_entrega_estandar INTEGER NOT NULL DEFAULT 5,
    distancia_km DECIMAL(10,2) DEFAULT 0,
    velocidad_promedio_kmh DECIMAL(5,2) DEFAULT 60,
    tiempo_aduanas_dias INTEGER DEFAULT 0,
    tasa_defectos_fabrica DECIMAL(5,4) DEFAULT 0.05,
    unidades_disponibles INTEGER DEFAULT 0,
    proxima_produccion DATE,
    unidades_proxima_produccion INTEGER DEFAULT 0,
    zonas_cobertura JSONB DEFAULT '[]'::jsonb,
    descuentos_volumen JSONB DEFAULT '[]'::jsonb,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    CONSTRAINT unique_proveedor_producto UNIQUE (proveedor_id, producto_id)
);

CREATE TABLE IF NOT EXISTS propuestas_proveedor (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    solicitud_id UUID NOT NULL REFERENCES solicitudes_reabastecimiento(id) ON DELETE CASCADE,
    proveedor_id UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    cantidad_ofrecida INTEGER,
    dias_entrega INTEGER,
    precio_unitario DECIMAL(10,2),
    precio_total DECIMAL(12,2),
    calendario_entregas JSONB DEFAULT '[]'::jsonb,
    descuento_aplicado DECIMAL(5,2) DEFAULT 0,
    notas TEXT,
    estado VARCHAR(20) DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    CONSTRAINT unique_propuesta_solicitud UNIQUE (solicitud_id, proveedor_id)
);
CREATE INDEX IF NOT EXISTS idx_propuestas_solicitud ON propuestas_proveedor(solicitud_id);
CREATE INDEX IF NOT EXISTS idx_propuestas_estado ON propuestas_proveedor(estado);

-- Backorder / sobrestock aceptado por cliente
CREATE TABLE IF NOT EXISTS pedidos_backorder (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    pedido_original_id UUID REFERENCES pedidos(id),
    usuario_id UUID REFERENCES usuarios(id),
    producto_id UUID REFERENCES productos(id),
    cantidad_pendiente INTEGER NOT NULL,
    cantidad_resuelta INTEGER DEFAULT 0,
    estado VARCHAR(30) DEFAULT 'pendiente_cronograma',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS cronogramas_reabastecimiento (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    backorder_id UUID REFERENCES pedidos_backorder(id) ON DELETE CASCADE,
    proveedor_id UUID REFERENCES usuarios(id),
    fecha_llegada_tienda DATE NOT NULL,
    cantidad INTEGER NOT NULL,
    tipo_entrega VARCHAR(20) NOT NULL,
    opcion_grupo VARCHAR(1) NOT NULL,
    estado VARCHAR(30) DEFAULT 'pendiente_llegada',
    created_at TIMESTAMP DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_backorder_pedido ON pedidos_backorder(pedido_original_id);
CREATE INDEX IF NOT EXISTS idx_cronograma_backorder ON cronogramas_reabastecimiento(backorder_id);

-- Producto / ficha técnica
ALTER TABLE productos ADD COLUMN IF NOT EXISTS modelo VARCHAR(100);
ALTER TABLE productos ADD COLUMN IF NOT EXISTS costo_unitario NUMERIC(12,2) DEFAULT 0;
ALTER TABLE productos ADD COLUMN IF NOT EXISTS descuentos_volumen_rangos JSONB DEFAULT '[]'::jsonb;
ALTER TABLE productos ADD COLUMN IF NOT EXISTS ultimo_descuento_notificado DECIMAL(5,2) DEFAULT 0;
ALTER TABLE detalle_pedidos ADD COLUMN IF NOT EXISTS numero_serie_vendido TEXT;

-- Seguridad de cookies y sesión se maneja en PHP. Esta migración no modifica usuarios.
