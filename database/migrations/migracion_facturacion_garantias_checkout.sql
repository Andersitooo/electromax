-- =====================================================================
-- ELECTROMAX - FACTURACION SIMULADA, GARANTIAS Y CHECKOUT GUIADO
-- Ejecutar sobre la base actual: psql -d electro2 -f migracion_facturacion_garantias_checkout.sql
-- Es idempotente: usa IF NOT EXISTS y no borra datos.
-- =====================================================================

CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- ---------------------------------------------------------------------
-- 1. Configuracion de empresa emisora para facturas y documentos
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS empresa_config (
    id INTEGER PRIMARY KEY DEFAULT 1 CHECK (id = 1),
    razon_social VARCHAR(180) NOT NULL DEFAULT 'ELECTROMAX S.A.S.',
    nombre_comercial VARCHAR(120) NOT NULL DEFAULT 'ElectroMax',
    ruc VARCHAR(13) NOT NULL DEFAULT '0999999999001',
    direccion_matriz TEXT NOT NULL DEFAULT 'Babahoyo, Los Ríos, Ecuador',
    telefono VARCHAR(50) DEFAULT '04-273-0000',
    email VARCHAR(160) DEFAULT 'facturacion@electromax.com',
    logo_url TEXT DEFAULT 'assets/electromax_logo.png',
    ambiente VARCHAR(30) DEFAULT 'PRODUCCION',
    obligado_contabilidad BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT NOW()
);

INSERT INTO empresa_config (id, razon_social, nombre_comercial, ruc, direccion_matriz, telefono, email, logo_url, ambiente)
VALUES (1, 'ELECTROMAX S.A.S.', 'ElectroMax', '0999999999001', 'Babahoyo, Los Ríos, Ecuador', '04-273-0000', 'facturacion@electromax.com', 'assets/electromax_logo.png', 'PRODUCCION')
ON CONFLICT (id) DO NOTHING;

-- ---------------------------------------------------------------------
-- 2. Datos de facturacion guardados por usuario
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS datos_facturacion_usuario (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    tipo_identificacion VARCHAR(20) NOT NULL DEFAULT 'cedula', -- cedula, ruc, pasaporte, consumidor_final
    identificacion VARCHAR(20) NOT NULL,
    razon_social VARCHAR(180) NOT NULL,
    email VARCHAR(160) NOT NULL,
    telefono VARCHAR(50),
    direccion TEXT NOT NULL,
    provincia_id INTEGER REFERENCES provincias(id),
    canton_id INTEGER REFERENCES cantones(id),
    es_predeterminado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_datos_facturacion_usuario ON datos_facturacion_usuario(usuario_id, es_predeterminado);

-- ---------------------------------------------------------------------
-- 3. Extensiones de pedidos para flujo simple de pago/facturacion
-- ---------------------------------------------------------------------
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS estado_pago VARCHAR(40) DEFAULT 'pendiente_aprobacion';
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS facturacion_datos JSONB DEFAULT '{}'::jsonb;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS checkout_paso_info JSONB DEFAULT '{}'::jsonb;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS cancelado_en TIMESTAMP;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS cancelado_por UUID REFERENCES usuarios(id);
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS motivo_cancelacion TEXT;

-- ---------------------------------------------------------------------
-- 4. Facturas simuladas
-- ---------------------------------------------------------------------
CREATE SEQUENCE IF NOT EXISTS emx_factura_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS facturas (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    pedido_id UUID NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    usuario_id UUID REFERENCES usuarios(id),
    numero_factura VARCHAR(30) UNIQUE NOT NULL,
    clave_acceso_simulada VARCHAR(60) UNIQUE NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'emitida', -- emitida, anulada, nota_credito_total, nota_credito_parcial
    ambiente VARCHAR(30) DEFAULT 'PRODUCCION',
    fecha_emision TIMESTAMP DEFAULT NOW(),
    subtotal NUMERIC(12,2) NOT NULL DEFAULT 0,
    descuento NUMERIC(12,2) NOT NULL DEFAULT 0,
    iva NUMERIC(12,2) NOT NULL DEFAULT 0,
    total NUMERIC(12,2) NOT NULL DEFAULT 0,
    datos_empresa JSONB NOT NULL DEFAULT '{}'::jsonb,
    datos_cliente JSONB NOT NULL DEFAULT '{}'::jsonb,
    pdf_url TEXT,
    enviada_email BOOLEAN DEFAULT FALSE,
    email_enviado_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(pedido_id)
);

CREATE INDEX IF NOT EXISTS idx_facturas_usuario ON facturas(usuario_id, fecha_emision DESC);
CREATE INDEX IF NOT EXISTS idx_facturas_estado ON facturas(estado);

CREATE TABLE IF NOT EXISTS factura_detalles (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    factura_id UUID NOT NULL REFERENCES facturas(id) ON DELETE CASCADE,
    producto_id UUID REFERENCES productos(id),
    sku VARCHAR(80),
    descripcion TEXT NOT NULL,
    cantidad INTEGER NOT NULL,
    precio_unitario NUMERIC(12,2) NOT NULL DEFAULT 0,
    descuento NUMERIC(12,2) NOT NULL DEFAULT 0,
    iva_porcentaje NUMERIC(5,2) NOT NULL DEFAULT 15,
    subtotal NUMERIC(12,2) NOT NULL DEFAULT 0,
    iva NUMERIC(12,2) NOT NULL DEFAULT 0,
    total NUMERIC(12,2) NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_factura_detalles_factura ON factura_detalles(factura_id);

-- ---------------------------------------------------------------------
-- 5. Notas de credito simuladas para devoluciones/reembolsos/anulaciones
-- ---------------------------------------------------------------------
CREATE SEQUENCE IF NOT EXISTS emx_nota_credito_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS notas_credito (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    factura_id UUID REFERENCES facturas(id) ON DELETE SET NULL,
    pedido_id UUID REFERENCES pedidos(id) ON DELETE SET NULL,
    devolucion_id UUID REFERENCES devoluciones(id) ON DELETE SET NULL,
    numero_nota VARCHAR(30) UNIQUE NOT NULL,
    motivo TEXT NOT NULL,
    tipo VARCHAR(30) DEFAULT 'total', -- total, parcial
    subtotal NUMERIC(12,2) NOT NULL DEFAULT 0,
    iva NUMERIC(12,2) NOT NULL DEFAULT 0,
    total NUMERIC(12,2) NOT NULL DEFAULT 0,
    estado VARCHAR(30) DEFAULT 'emitida',
    datos_empresa JSONB NOT NULL DEFAULT '{}'::jsonb,
    datos_cliente JSONB NOT NULL DEFAULT '{}'::jsonb,
    pdf_url TEXT,
    enviada_email BOOLEAN DEFAULT FALSE,
    email_enviado_at TIMESTAMP,
    fecha_emision TIMESTAMP DEFAULT NOW(),
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_notas_credito_factura ON notas_credito(factura_id);
CREATE INDEX IF NOT EXISTS idx_notas_credito_pedido ON notas_credito(pedido_id);

ALTER TABLE devoluciones ADD COLUMN IF NOT EXISTS nota_credito_id UUID REFERENCES notas_credito(id);

-- ---------------------------------------------------------------------
-- 6. Bandeja de correos simulada/pendiente si SMTP no esta configurado
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_outbox (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id UUID REFERENCES usuarios(id) ON DELETE SET NULL,
    email_destino VARCHAR(180) NOT NULL,
    asunto VARCHAR(250) NOT NULL,
    cuerpo_html TEXT NOT NULL,
    archivo_adjunto TEXT,
    tipo VARCHAR(50) DEFAULT 'general',
    estado VARCHAR(30) DEFAULT 'pendiente', -- pendiente, enviado, error
    error_msg TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    enviado_at TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_email_outbox_estado ON email_outbox(estado, created_at);

-- ---------------------------------------------------------------------
-- 7. Garantias por producto y casos de garantia despues de 30 dias
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS producto_garantias (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    producto_id UUID NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    componente VARCHAR(120) NOT NULL,
    duracion_meses INTEGER NOT NULL DEFAULT 12,
    cobertura TEXT NOT NULL DEFAULT 'Defectos de fábrica bajo uso normal.',
    condiciones TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(producto_id, componente)
);

CREATE INDEX IF NOT EXISTS idx_producto_garantias_producto ON producto_garantias(producto_id, is_active);

ALTER TABLE detalle_pedidos ADD COLUMN IF NOT EXISTS garantia_snapshot JSONB DEFAULT '[]'::jsonb;
ALTER TABLE detalle_pedidos ADD COLUMN IF NOT EXISTS garantia_inicio DATE;
ALTER TABLE detalle_pedidos ADD COLUMN IF NOT EXISTS garantia_fin DATE;

CREATE TABLE IF NOT EXISTS garantia_casos (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    pedido_id UUID NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    detalle_pedido_id UUID REFERENCES detalle_pedidos(id) ON DELETE SET NULL,
    producto_id UUID REFERENCES productos(id),
    usuario_id UUID REFERENCES usuarios(id),
    numero_serie VARCHAR(120),
    componente_afectado VARCHAR(120),
    descripcion_falla TEXT NOT NULL,
    evidencias JSONB DEFAULT '[]'::jsonb,
    estado VARCHAR(40) DEFAULT 'pendiente_revision', -- pendiente_revision, en_revision_tecnica, aprobado_reparacion, aprobado_reemplazo, garantia_proveedor, rechazado, cerrado
    tecnico_id UUID REFERENCES usuarios(id),
    diagnostico TEXT,
    resolucion TEXT,
    historial_estados JSONB DEFAULT '[]'::jsonb,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_garantia_casos_usuario ON garantia_casos(usuario_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_garantia_casos_estado ON garantia_casos(estado);

-- ---------------------------------------------------------------------
-- 8. Datos mínimos de garantía para productos sin captura específica
--    No reemplaza garantías por componente; solo crea garantía general si no existe.
-- ---------------------------------------------------------------------
INSERT INTO producto_garantias (producto_id, componente, duracion_meses, cobertura, condiciones)
SELECT p.id, 'Garantía general', 12, 'Defectos de fábrica bajo uso normal.', 'No cubre golpes, humedad, manipulación indebida o daños eléctricos externos.'
FROM productos p
WHERE p.deleted_at IS NULL
ON CONFLICT (producto_id, componente) DO NOTHING;

-- ---------------------------------------------------------------------
-- FIN
-- ---------------------------------------------------------------------

-- ---------------------------------------------------------------------
-- Complemento: datos administrables de empresa para facturacion
-- ---------------------------------------------------------------------
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS logo_pdf_url TEXT DEFAULT 'assets/electromax_logo_pdf.jpg';
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS establecimiento VARCHAR(3) DEFAULT '001';
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS punto_emision VARCHAR(3) DEFAULT '001';
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS moneda VARCHAR(10) DEFAULT 'USD';
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS website VARCHAR(180);
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS regimen TEXT DEFAULT 'Documento generado electrónicamente por ElectroMax.';
UPDATE empresa_config SET
    logo_pdf_url = COALESCE(logo_pdf_url, 'assets/electromax_logo_pdf.jpg'),
    establecimiento = COALESCE(establecimiento, '001'),
    punto_emision = COALESCE(punto_emision, '001'),
    moneda = COALESCE(moneda, 'USD'),
    regimen = COALESCE(regimen, 'Documento generado electrónicamente por ElectroMax.'),
    updated_at = NOW()
WHERE id = 1;
