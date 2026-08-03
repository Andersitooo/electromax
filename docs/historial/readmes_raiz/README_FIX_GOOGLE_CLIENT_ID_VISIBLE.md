# Fix Google Login visible

Problema:
- El botón de Google no aparecía porque `config_google.php` seguía devolviendo un Client ID vacío.
- Por eso `auth.php` mostraba: "falta configurar EMX_GOOGLE_CLIENT_ID".

Corrección:
- Se dejó fijo el Client ID en `config_google.php`.

Client ID configurado:
974067815868-8qhd20n0p5aeo3paiqp63534qpqqikj7.apps.googleusercontent.com

Después de copiar el parche:
1. Reemplaza `config_google.php`.
2. Limpia caché del navegador con Ctrl + F5.
3. Entra a `auth.php?action=login`.

Si todavía no aparece el botón, revisa en Google Cloud:
- Authorized JavaScript origins: `http://localhost`
- Authorized redirect URI: `http://localhost/electro2/google_auth.php`
