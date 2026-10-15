@echo off
net session >nul 2>&1
if %errorlevel% neq 0 (
  echo Haz clic derecho sobre este archivo y elige "Ejecutar como administrador".
  pause
  exit /b
)
netsh advfirewall firewall add rule name="CafeQR 8000" dir=in action=allow protocol=TCP localport=8000 profile=private,public,domain
echo Listo: el puerto 8000 quedo permitido.
pause
