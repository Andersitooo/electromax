-- Google Login / vinculación de cuentas para ElectroMax
-- Ejecutar una sola vez antes de usar el botón "Continuar con Google".

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS google_id VARCHAR(255);
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS google_email_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS google_foto_url TEXT;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS auth_provider VARCHAR(30) DEFAULT 'local';
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS ultimo_login_google TIMESTAMP;

CREATE UNIQUE INDEX IF NOT EXISTS idx_usuarios_google_id_unique
ON usuarios (google_id)
WHERE google_id IS NOT NULL AND google_id <> '';

CREATE UNIQUE INDEX IF NOT EXISTS idx_usuarios_email_lower_unique
ON usuarios (LOWER(email))
WHERE deleted_at IS NULL;

CREATE TABLE IF NOT EXISTS usuarios_auth_eventos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id) ON DELETE SET NULL,
    tipo VARCHAR(80) NOT NULL,
    detalle TEXT,
    ip VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_usuarios_auth_eventos_usuario_fecha
ON usuarios_auth_eventos (usuario_id, created_at DESC);
