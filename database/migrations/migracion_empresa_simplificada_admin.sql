-- ==========================================================================
-- ElectroMax - Panel Empresa simplificado para facturación simulada
-- Ejecutar sobre tu BD electro2. No borra datos existentes.
-- ===========================================================================

CREATE TABLE IF NOT EXISTS empresa_config (
    id INTEGER PRIMARY KEY DEFAULT 1 CHECK (id = 1),
    razon_social VARCHAR(180) NOT NULL DEFAULT 'ELECTROMAX S.A.S.',
    nombre_comercial VARCHAR(120) NOT NULL DEFAULT 'ElectroMax',
    ruc VARCHAR(13) NOT NULL DEFAULT '0999999999001',
    direccion_matriz TEXT NOT NULL DEFAULT 'Babahoyo, Los Ríos, Ecuador',
    telefono VARCHAR(50) DEFAULT '04-273-0000',
    email VARCHAR(160) DEFAULT 'facturacion@electromax.com',
    logo_url TEXT DEFAULT 'assets/electromax_logo.png',
    logo_pdf_url TEXT DEFAULT 'assets/electromax_logo_pdf.jpg',
    ambiente VARCHAR(30) DEFAULT 'PRODUCCION',
    establecimiento VARCHAR(3) DEFAULT '001',
    punto_emision VARCHAR(3) DEFAULT '001',
    moneda VARCHAR(10) DEFAULT 'USD',
    website VARCHAR(180) DEFAULT '',
    regimen TEXT DEFAULT 'Documento generado electrónicamente por ElectroMax.',
    obligado_contabilidad BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT NOW()
);

ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS logo_pdf_url TEXT DEFAULT 'assets/electromax_logo_pdf.jpg';
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS establecimiento VARCHAR(3) DEFAULT '001';
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS punto_emision VARCHAR(3) DEFAULT '001';
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS moneda VARCHAR(10) DEFAULT 'USD';
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS website VARCHAR(180) DEFAULT '';
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS regimen TEXT DEFAULT 'Documento generado electrónicamente por ElectroMax.';
ALTER TABLE empresa_config ADD COLUMN IF NOT EXISTS obligado_contabilidad BOOLEAN DEFAULT FALSE;

INSERT INTO empresa_config (
    id, razon_social, nombre_comercial, ruc, direccion_matriz, telefono, email,
    logo_url, logo_pdf_url, ambiente, establecimiento, punto_emision, moneda, website, regimen, obligado_contabilidad, updated_at
)
VALUES (
    1,
    'ELECTROMAX S.A.S.',
    'ElectroMax',
    '0999999999001',
    'Babahoyo, Los Ríos, Ecuador',
    '04-273-0000',
    'facturacion@electromax.com',
    'assets/electromax_logo.png',
    'assets/electromax_logo_pdf.jpg',
    'PRODUCCION',
    '001',
    '001',
    'USD',
    '',
    'Documento generado electrónicamente por ElectroMax.',
    FALSE,
    NOW()
)
ON CONFLICT (id) DO UPDATE SET
    ambiente = 'PRODUCCION',
    moneda = 'USD',
    website = COALESCE(empresa_config.website, ''),
    regimen = COALESCE(NULLIF(empresa_config.regimen, ''), EXCLUDED.regimen),
    obligado_contabilidad = FALSE,
    updated_at = NOW();

SELECT 'OK - empresa_config simplificada' AS estado,
       razon_social, nombre_comercial, ruc, ambiente, moneda, establecimiento, punto_emision
FROM empresa_config
WHERE id = 1;
