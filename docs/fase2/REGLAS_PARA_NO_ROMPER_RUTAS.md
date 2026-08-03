# Reglas para no romper rutas

Durante esta fase se respetan estas reglas:

1. No mover archivos PHP principales de la raíz.
2. No cambiar nombres de archivos usados por formularios o enlaces.
3. No cambiar rutas de `action` en formularios.
4. No cambiar rutas usadas por `header('Location: ...')`.
5. No cambiar rutas de assets, uploads, facturas o PDFs.
6. No modificar `db.php` ni `seguridad.php` todavía.
7. No activar `public/` como webroot todavía.
8. No borrar archivos duplicados todavía.
9. No tocar migraciones SQL antiguas hasta Fase 6.
10. Cualquier archivo nuevo debe ser compatible y no obligatorio para el flujo actual.

La prioridad es que el sistema siga funcionando en XAMPP exactamente con las mismas URLs actuales.
