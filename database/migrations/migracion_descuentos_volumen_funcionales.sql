-- Migración: descuentos por volumen funcionales para clientes y proveedores
-- Ejecutar una vez:
-- psql -d electro2 -f migracion_descuentos_volumen_funcionales.sql

ALTER TABLE productos
ADD COLUMN IF NOT EXISTS descuentos_volumen_rangos JSONB DEFAULT '[]'::jsonb;

ALTER TABLE capacidad_proveedor
ADD COLUMN IF NOT EXISTS descuentos_volumen JSONB DEFAULT '[]'::jsonb;

CREATE INDEX IF NOT EXISTS idx_productos_descuentos_volumen_gin
ON productos USING gin (descuentos_volumen_rangos);

CREATE INDEX IF NOT EXISTS idx_capacidad_proveedor_descuentos_volumen_gin
ON capacidad_proveedor USING gin (descuentos_volumen);
