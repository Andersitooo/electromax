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
CREATE INDEX IF NOT EXISTS idx_email_outbox_estado ON email_outbox(estado, created_at);
CREATE INDEX IF NOT EXISTS idx_email_outbox_destino ON email_outbox(email_destino);
