# Google Login y vinculación automática

Archivos agregados:
- `config_google.php`
- `funciones_google_auth.php`
- `google_auth.php`
- `migracion_google_login.sql`

Flujo implementado:
1. Cliente presiona "Continuar con Google".
2. Google devuelve un ID token.
3. El servidor verifica la firma, emisor, audiencia, expiración y correo verificado.
4. El sistema busca primero por `google_id`.
5. Si no existe, busca por `email`.
6. Si el email ya existe en una cuenta CLIENTE, se vincula Google a esa misma cuenta y entra.
7. Si el email no existe, se crea una cuenta CLIENTE nueva.
8. Si el correo pertenece a ADMIN, SUPERADMIN o PROVEEDOR, no se vincula automáticamente por seguridad.

Instalación:
1. Ejecuta:
   `psql -d electro2 -f migracion_google_login.sql`
2. Configura tu Client ID en `config_google.php` o en variable de entorno:
   `EMX_GOOGLE_CLIENT_ID=TU_CLIENT_ID.apps.googleusercontent.com`
3. En Google Cloud configura como origen autorizado tu dominio o localhost.

Nota:
- No se usa token falso ni dato ficticio.
- La verificación del token se hace en servidor con certificados públicos de Google.
- El servidor necesita poder consultar `https://www.googleapis.com/oauth2/v1/certs` para validar firmas.
