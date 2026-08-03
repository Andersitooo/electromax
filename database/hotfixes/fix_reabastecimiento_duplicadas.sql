-- HOTFIX: cerrar solicitudes de reabastecimiento duplicadas ya existentes
-- Ejecutar una sola vez después de copiar los archivos.

BEGIN;

WITH activas AS (
    SELECT
        id,
        producto_id,
        ROW_NUMBER() OVER (PARTITION BY producto_id ORDER BY created_at DESC, id DESC) AS rn
    FROM solicitudes_reabastecimiento
    WHERE estado IN ('pendiente','cotizada','en_revision')
),
duplicadas AS (
    SELECT id FROM activas WHERE rn > 1
)
UPDATE propuestas_proveedor
SET estado = 'rechazada'
WHERE solicitud_id IN (SELECT id FROM duplicadas)
  AND estado IN ('pendiente','en_revision');

-- Si tienes una restricción CHECK de estados y no acepta 'reemplazada',
-- cambia 'reemplazada' por 'cancelada' o 'cerrada' según tu tabla.
WITH activas AS (
    SELECT
        id,
        producto_id,
        ROW_NUMBER() OVER (PARTITION BY producto_id ORDER BY created_at DESC, id DESC) AS rn
    FROM solicitudes_reabastecimiento
    WHERE estado IN ('pendiente','cotizada','en_revision')
),
duplicadas AS (
    SELECT id FROM activas WHERE rn > 1
)
UPDATE solicitudes_reabastecimiento
SET estado = 'reemplazada'
WHERE id IN (SELECT id FROM duplicadas);

COMMIT;
