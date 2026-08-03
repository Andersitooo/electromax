-- Índices opcionales para mejorar la búsqueda inteligente.
-- Ejecutar una vez si quieres mejorar rendimiento en PostgreSQL:
-- psql -d electro2 -f migracion_busqueda_inteligente.sql

CREATE EXTENSION IF NOT EXISTS pg_trgm;

CREATE INDEX IF NOT EXISTS idx_productos_nombre_trgm
ON productos USING gin (LOWER(nombre) gin_trgm_ops);

CREATE INDEX IF NOT EXISTS idx_productos_descripcion_trgm
ON productos USING gin (LOWER(COALESCE(descripcion_corta,'')) gin_trgm_ops);

CREATE INDEX IF NOT EXISTS idx_productos_sku_trgm
ON productos USING gin (LOWER(COALESCE(sku,'')) gin_trgm_ops);

CREATE INDEX IF NOT EXISTS idx_marcas_nombre_trgm
ON marcas USING gin (LOWER(nombre) gin_trgm_ops);

CREATE INDEX IF NOT EXISTS idx_categorias_nombre_trgm
ON categorias USING gin (LOWER(nombre) gin_trgm_ops);
