# CafeQR — Backend Laravel

Este paquete contiene **solo los archivos propios de CafeQR** (rutas, modelos,
controladores, migraciones, seeders y vistas). No incluye el esqueleto de
Laravel (`vendor/`, `bootstrap/`, `config/`, `artisan`, etc.) porque eso lo
genera Composer en el momento de instalar — así el paquete pesa poco y no hay
que descargar nada de más de aquí.

**No se usa Node/npm en ningún paso.** El CSS es un archivo estático
(`public/css/app.css`) enlazado directo, sin Vite ni build.

## 1. Requisitos

- PHP 8.2 o superior
- Composer
- MySQL (o SQLite, más simple para probar en clase)

## 2. Crear el proyecto base

```bash
composer create-project laravel/laravel cafeqr
cd cafeqr
```

## 3. Copiar los archivos de este paquete

Copia (sobrescribiendo) estas carpetas del paquete `cafeqr-laravel/` dentro
del proyecto recién creado:

```
routes/web.php          → cafeqr/routes/web.php
app/Models/*             → cafeqr/app/Models/
app/Http/Controllers/*   → cafeqr/app/Http/Controllers/  (incluye CuponController)
app/Http/Requests/*      → cafeqr/app/Http/Requests/
app/Http/Middleware/*    → cafeqr/app/Http/Middleware/
app/Services/*           → cafeqr/app/Services/
database/migrations/*    → cafeqr/database/migrations/
database/seeders/*       → cafeqr/database/seeders/
resources/views/*        → cafeqr/resources/views/
public/css/app.css       → cafeqr/public/css/app.css
```

## 4. Instalar el paquete de código QR

```bash
composer require simplesoftwareio/simple-qrcode
```

## 5. Registrar el middleware "admin"

**Laravel 11 o 12** — en `bootstrap/app.php`, dentro de `->withMiddleware(...)`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['admin' => \App\Http\Middleware\EsAdministrador::class]);
})
```

**Laravel 10** — en `app/Http/Kernel.php`, agrega dentro de `$middlewareAliases`:

```php
'admin' => \App\Http\Middleware\EsAdministrador::class,
```

## 6. Configurar la base de datos

Copia `.env.example` a `.env` (ya lo trae Laravel) y edita las variables
`DB_*`. Para probar rápido en clase, lo más simple es SQLite:

```bash
touch database/database.sqlite
```

Y en `.env`: `DB_CONNECTION=sqlite` (y comenta las demás variables `DB_*`).

## 7. Migrar y sembrar los datos

```bash
php artisan key:generate
php artisan migrate --seed
```

Esto crea las tablas y siembra: 4 categorías, 34 productos, los estados del
pedido, el método de pago QR, la cuenta de cobro de ejemplo, los cupones
`UNIVALLE10` y `CAFEQR20`, y la **cuenta de administrador**.

## 8. Levantar el servidor

```bash
php artisan serve
```

Abrir `http://127.0.0.1:8000`.

## 9. Cuentas para probar

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@cafeqr.edu` | `admin123` |
| Estudiante | (regístrate desde `/registro` con cualquier correo) | la que elijas |

A diferencia del prototipo de frontend (donde cualquier correo "entraba"
sin registrarse), aquí la autenticación es real: cada estudiante debe
**registrarse una vez** antes de poder iniciar sesión.

## Notas para la exposición en clase

- El flujo completo (HU-05, HU-09, HU-10, HU-11 a HU-15) funciona de
  extremo a extremo con base de datos real.
- El envío del código por correo/SMS y la recuperación de contraseña están
  **simulados** (marcados con `// TODO` en el código) — no envían nada real
  todavía, solo muestran el mensaje de confirmación.
- El QR se genera con `simplesoftwareio/simple-qrcode`; codifica un texto de
  referencia del pago, no una transacción bancaria real (no hay pasarela de
  pago conectada).
