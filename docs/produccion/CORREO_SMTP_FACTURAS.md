# Correo SMTP en producción académica

## ¿Seguirá enviando facturas por correo?

Sí, siempre que se cumplan estas condiciones:

```text
1. EMX_SMTP_HOST, EMX_SMTP_PORT y EMX_SMTP_SECURE están bien configurados.
2. EMX_SMTP_USER y EMX_SMTP_PASS son válidos.
3. PHPMailer está instalado con composer install.
4. El VPS permite conexiones salientes al puerto SMTP usado, normalmente 587.
5. El correo del cliente es válido.
```

## Gmail

Para Gmail no uses la contraseña normal de la cuenta. Usa una contraseña de aplicación.

En `.env`:

```text
EMX_SMTP_HOST=smtp.gmail.com
EMX_SMTP_PORT=587
EMX_SMTP_SECURE=tls
EMX_SMTP_USER=tu_correo@gmail.com
EMX_SMTP_PASS=TU_APP_PASSWORD
EMX_SMTP_FROM_EMAIL=tu_correo@gmail.com
EMX_SMTP_FROM_NAME=ElectroMax Facturación
```

## Modo prueba

Si quieres que todos los correos lleguen a tu propio correo durante la defensa:

```text
EMX_MAIL_MODO_PRUEBA=1
EMX_MAIL_CORREO_PRUEBA=tu_correo@gmail.com
```

Si quieres enviar al correo real del cliente:

```text
EMX_MAIL_MODO_PRUEBA=0
```

## Si SMTP no funciona

El sistema guarda el correo como pendiente en `email_outbox` si la tabla existe.

Eso permite explicar en la defensa que el sistema tiene una bandeja de salida interna cuando el SMTP externo no está disponible.
