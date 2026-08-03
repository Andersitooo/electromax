-- Migración Centro de Soporte ElectroMax
-- Crea un flujo simple de tickets entre cliente y administración.

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

CREATE TABLE IF NOT EXISTS soporte_tickets (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    pedido_id UUID NULL REFERENCES pedidos(id) ON DELETE SET NULL,
    asunto VARCHAR(160) NOT NULL,
    motivo VARCHAR(40) NOT NULL DEFAULT 'general',
    estado VARCHAR(40) NOT NULL DEFAULT 'abierto',
    prioridad VARCHAR(20) NOT NULL DEFAULT 'media',
    ultimo_mensaje TEXT,
    ultimo_mensaje_por VARCHAR(20) DEFAULT 'cliente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cerrado_at TIMESTAMP NULL,
    CONSTRAINT soporte_tickets_motivo_chk CHECK (motivo IN (
        'pedido','pago_factura','entrega','devolucion_garantia','cuenta','general'
    )),
    CONSTRAINT soporte_tickets_estado_chk CHECK (estado IN (
        'abierto','en_revision','respondido','esperando_cliente','cerrado'
    )),
    CONSTRAINT soporte_tickets_prioridad_chk CHECK (prioridad IN (
        'baja','media','alta'
    ))
);

CREATE TABLE IF NOT EXISTS soporte_mensajes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ticket_id UUID NOT NULL REFERENCES soporte_tickets(id) ON DELETE CASCADE,
    usuario_id UUID NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    enviado_por VARCHAR(20) NOT NULL DEFAULT 'cliente',
    mensaje TEXT NOT NULL,
    adjunto_url TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT soporte_mensajes_enviado_por_chk CHECK (enviado_por IN ('cliente','admin','sistema'))
);

CREATE INDEX IF NOT EXISTS idx_soporte_tickets_usuario ON soporte_tickets(usuario_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_soporte_tickets_estado ON soporte_tickets(estado, updated_at DESC);
CREATE INDEX IF NOT EXISTS idx_soporte_tickets_motivo ON soporte_tickets(motivo);
CREATE INDEX IF NOT EXISTS idx_soporte_mensajes_ticket ON soporte_mensajes(ticket_id, created_at ASC);

CREATE OR REPLACE FUNCTION soporte_touch_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_soporte_tickets_updated_at ON soporte_tickets;
CREATE TRIGGER trg_soporte_tickets_updated_at
BEFORE UPDATE ON soporte_tickets
FOR EACH ROW
EXECUTE FUNCTION soporte_touch_updated_at();
