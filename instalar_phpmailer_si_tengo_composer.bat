@echo off
setlocal
cd /d "%~dp0"

echo ==========================================================
echo  ElectroMax - Instalar PHPMailer usando Composer global
echo ==========================================================
echo.

where composer >nul 2>nul
if errorlevel 1 (
  echo [ERROR] Composer no esta instalado globalmente.
  echo Usa instalar_phpmailer_windows.bat para instalar Composer local automaticamente.
  pause
  exit /b 1
)

composer require phpmailer/phpmailer:^6.9
if errorlevel 1 (
  echo [ERROR] No se pudo instalar PHPMailer.
  pause
  exit /b 1
)

php verificar_phpmailer.php
pause
