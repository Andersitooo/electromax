# Configuración centralizada

## Nueva ubicación

La configuración quedó en:

```text
app/Config/
```

## Archivos

```text
app/Config/database.php
app/Config/google.php
app/Config/mail.php
app/Config/mail.example.php
app/Config/company.php
```

## Responsabilidad

La configuración no debe mezclarse con vistas ni controladores.

La conexión a PostgreSQL queda en `database.php`.

La configuración de correo queda en `mail.php`.

La configuración de Google Login queda en `google.php`.

Los datos públicos de empresa quedan en `company.php`.
