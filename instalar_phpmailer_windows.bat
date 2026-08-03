@echo off
setlocal
cd /d "%~dp0"

echo ==========================================================
echo  ElectroMax - Instalador seguro de PHPMailer
echo ==========================================================
echo.

where php >nul 2>nul
if errorlevel 1 (
  echo [ERROR] No se encontro PHP en el PATH.
  echo Abre XAMPP Control Panel ^> Shell y ejecuta este archivo desde ahi,
  echo o agrega C:\xampp\php al PATH de Windows.
  pause
  exit /b 1
)

if exist vendor\autoload.php (
  echo [OK] Ya existe vendor\autoload.php.
  echo Ejecutando verificacion...
  php verificar_phpmailer.php
  pause
  exit /b 0
)

echo [INFO] No existe vendor\autoload.php. Se instalara Composer localmente como composer.phar.

if not exist composer.phar (
  echo [INFO] Descargando instalador oficial de Composer...
  powershell -NoProfile -ExecutionPolicy Bypass -Command "[Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri 'https://getcomposer.org/installer' -OutFile 'composer-setup.php'"
  if errorlevel 1 (
    echo [ERROR] No se pudo descargar Composer. Revisa tu internet o firewall.
    pause
    exit /b 1
  )

  echo [INFO] Instalando composer.phar local en esta carpeta...
  php composer-setup.php --quiet
  if errorlevel 1 (
    echo [ERROR] Fallo la instalacion de Composer.
    del composer-setup.php >nul 2>nul
    pause
    exit /b 1
  )
  del composer-setup.php >nul 2>nul
)

echo [INFO] Instalando PHPMailer con Composer...
php composer.phar require phpmailer/phpmailer:^6.9 --no-interaction
if errorlevel 1 (
  echo [ERROR] Composer no pudo instalar PHPMailer.
  echo Intenta abrir CMD como administrador o revisa la conexion a internet.
  pause
  exit /b 1
)

echo.
echo [OK] PHPMailer instalado.
echo.
php verificar_phpmailer.php
pause
