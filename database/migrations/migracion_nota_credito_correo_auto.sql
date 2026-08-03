-- Migración: correo automático de nota de crédito
-- Ejecutar una vez:
-- psql -d electro2 -f migracion_nota_credito_correo_auto.sql

ALTER TABLE notas_credito
ADD COLUMN IF NOT EXISTS enviada_email BOOLEAN DEFAULT FALSE;

ALTER TABLE notas_credito
ADD COLUMN IF NOT EXISTS email_enviado_at TIMESTAMP;

CREATE INDEX IF NOT EXISTS idx_notas_credito_email_estado
ON notas_credito(enviada_email, created_at);

-- Asegura que la bandeja de correos pueda registrar notas de crédito.
CREATE TABLE IF NOT EXISTS email_outbox (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id UUID REFERENCES usuarios(id) ON DELETE SET NULL,
    email_destino VARCHAR(180) NOT NULL,
    asunto VARCHAR(250) NOT NULL,
    cuerpo_html TEXT NOT NULL,
    archivo_adjunto TEXT,
    tipo VARCHAR(50) DEFAULT 'general',
    estado VARCHAR(30) DEFAULT 'pendiente',
    error_msg TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    enviado_at TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_email_outbox_tipo_estado
ON email_outbox(tipo, estado, created_at);
