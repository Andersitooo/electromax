# ElectroMax - Estructura final neta

Este proyecto quedó preparado para funcionar con `public/` como punto de entrada web.

## Entrada principal

```text
public/index.php
```

En XAMPP puedes probar con:

```text
http://localhost/electro2/public/index.php
```

## Estructura final

```text
app/
bootstrap/
database/
docs/
public/
routes/
scripts/
storage/
views/
```

## Importante

Los archivos PHP antiguos de la raíz ya no están como flujo activo.

La lógica de entrada está en:

```text
public/
```

Los controladores están en:

```text
app/Controllers/
```

Las vistas están en:

```text
views/
```

La configuración está en:

```text
app/Config/
```


## URL compatible sin PHP raíz

Aunque la raíz ya no tiene `index.php`, se agregó `.htaccess` para que Apache pueda redirigir internamente:

```text
http://localhost/electro3/index.php
```

hacia:

```text
public/index.php
```

Si Apache no tiene `mod_rewrite` activo, usa directamente:

```text
http://localhost/electro3/public/index.php
```


## Favicon global

Se agregó favicon global en:

```text
public/assets/favicon/
views/components/favicon.php
```

Todas las vistas principales con etiqueta `<head>` incluyen los iconos de ElectroMax.


## Producción anderspace.online

Esta versión incluye preparación para VPS AlmaLinux.

Documentación principal:

```text
DEPLOY_ANDERSPACE_ALMALINUX.md
docs/produccion/DEPLOY_ANDERSPACE_ALMALINUX.md
docs/produccion/CORREO_SMTP_FACTURAS.md
docs/produccion/CHECKLIST_PRODUCCION.md
```

Verificador:

```bash
php scripts/verificar_produccion_anderspace.php
```
