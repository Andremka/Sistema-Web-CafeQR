@echo off
chcp 65001 >nul
title CafeQR
cd /d "%~dp0"
echo ============================================
echo   CafeQR - iniciando servidor
echo ============================================
echo.
echo Para que el celular lea el QR, debe estar en el MISMO WiFi que esta PC.
echo Abre la pagina en la PC con la direccion que sale abajo (IP de tu red):
echo.
ipconfig | findstr /i "IPv4"
echo.
echo   Ejemplo: http://192.168.1.50:8000
echo.
echo Si el celular no abre el QR: permite el puerto 8000 en el Firewall
echo (ejecuta permitir-firewall.bat como administrador, una sola vez).
echo Para cerrar el servidor: Ctrl + C
echo.
php artisan optimize:clear >nul 2>&1
php artisan serve --host=0.0.0.0 --port=8000
pause
