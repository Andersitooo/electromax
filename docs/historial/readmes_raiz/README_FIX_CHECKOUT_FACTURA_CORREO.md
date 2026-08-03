# Ajuste checkout, direcciones, factura y correo

## Qué corrige

- Corrige el warning `Undefined array key "producto_id"` en `checkout.php`, normalizando carritos antiguos guardados en sesión.
- Mejora la selección de dirección del checkout:
  - mis direcciones guardadas,
  - ubicación actual con GPS,
  - nueva dirección con opción de guardarla.
- Obliga a usar coordenadas para calcular sucursal cercana. Si la dirección guardada no tiene coordenadas, pide capturar ubicación.
- Guarda la nueva dirección en `direcciones_usuario` cuando el cliente marca `Guardar esta dirección`.
- Mantiene provincia/cantón mediante combos.
- Mejora el PDF de factura con diseño más profesional, logo, número de factura y clave/código simulado.
- Mantiene visualización de factura desde `Mis pedidos` mediante `factura_pdf.php`.
- Deja preparado PHPMailer con Composer y archivo `config_correo.example.php`.

## Pasos para correo real con PHPMailer

1. En la carpeta del proyecto, ejecuta:

```bash
composer install
```

Si no tienes vendor instalado:

```bash
composer require phpmailer/phpmailer
```

2. Copia el archivo:

```bash
copy config_correo.example.php config_correo.php
```

en Linux/Mac:

```bash
cp config_correo.example.php config_correo.php
```

3. Edita `config_correo.php` con tus datos SMTP reales.

4. Cuando el admin apruebe el pago y se genere la factura, el sistema intentará enviar el PDF al correo de facturación.

Si SMTP no está configurado o falla, el sistema no se rompe: guarda el intento en `email_outbox` como pendiente.

## Datos que debes completar para envío real

- Host SMTP.
- Puerto: usualmente 587 con TLS o 465 con SSL.
- Usuario SMTP.
- Contraseña SMTP o app password.
- Correo remitente.
- Nombre remitente.

## Datos de empresa que conviene actualizar en BD

En `empresa_config` cambia:

- razón social,
- nombre comercial,
- RUC,
- dirección matriz,
- teléfono,
- email,
- logo URL,
- ambiente.

Ejemplo:

```sql
UPDATE empresa_config
SET razon_social = 'TU EMPRESA S.A.S.',
    nombre_comercial = 'ElectroMax',
    ruc = '0999999999001',
    direccion_matriz = 'Babahoyo, Los Ríos, Ecuador',
    telefono = '04-000-0000',
    email = 'facturacion@tudominio.com',
    logo_url = 'assets/electromax_logo_pdf.jpg',
    ambiente = 'SIMULACION'
WHERE id = 1;
```

---

## Instalación guiada de PHPMailer

Para evitar errores, este paquete trae:

```text
instalar_phpmailer_windows.bat
instalar_phpmailer_si_tengo_composer.bat
verificar_phpmailer.php
README_INSTALAR_PHPMAILER.md
```

En XAMPP/Windows ejecuta primero:

```bat
instalar_phpmailer_windows.bat
```

Luego abre:

```text
http://localhost/electro2/verificar_phpmailer.php
```
