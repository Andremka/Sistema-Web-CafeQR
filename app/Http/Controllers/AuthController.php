<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /* ---------------------------- LOGIN ---------------------------- */

    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Autentica al usuario. El administrador va al panel; los demás al menú.
     * Máximo 5 intentos fallidos por minuto para cada correo + IP.
     */
    public function login(Request $request): RedirectResponse
    {
        $this->normalizarEmail($request);

        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], $this->mensajes());

        $clave = $credenciales['email'].'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $segundos = RateLimiter::availableIn($clave);

            return back()
                ->withErrors(['email' => "Demasiados intentos. Vuelve a intentar en {$segundos} segundos."])
                ->onlyInput('email');
        }

        if (! Auth::attempt($credenciales, $request->boolean('remember'))) {
            RateLimiter::hit($clave, 60);

            return back()
                ->withErrors(['email' => 'Correo o contraseña incorrectos.'])
                ->onlyInput('email');
        }

        RateLimiter::clear($clave);
        $request->session()->regenerate();

        return Auth::user()->esAdministrador()
            ? redirect()->route('admin.index')
            : redirect()->intended(route('menu.index'));
    }

    /* --------------------------- REGISTRO --------------------------- */

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $this->normalizarEmail($request);

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $this->mensajes());

        // El cast 'hashed' del modelo User encripta la contraseña automáticamente.
        // El rol no viene del formulario: todo registro nuevo es estudiante.
        $usuario = User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'rol' => 'estudiante',
        ]);

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('menu.index')->with('exito', 'Cuenta creada. ¡Bienvenido a CafeQR!');
    }

    /* ------------------- RECUPERACIÓN DE CONTRASEÑA ------------------- */

    public function showForgot(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $this->normalizarEmail($request);

        $request->validate([
            'email' => ['required', 'email'],
        ], $this->mensajes());

        Password::sendResetLink($request->only('email'));

        // Mensaje genérico a propósito: no revela si el correo existe o no.
        return redirect()
            ->route('login')
            ->with('exito', 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña.');
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $this->normalizarEmail($request);

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $this->mensajes());

        $estado = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $usuario, string $password) {
                $usuario->forceFill([
                    'password' => $password, // se encripta por el cast 'hashed'
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($usuario));
            }
        );

        if ($estado === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('exito', 'Contraseña actualizada. Ya puedes iniciar sesión.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'El enlace de recuperación no es válido o ya expiró. Solicita uno nuevo.']);
    }

    /* ----------------------------- LOGOUT ----------------------------- */

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /* ---------------------------- AUXILIARES ---------------------------- */

    /** Correo en minúsculas y sin espacios, para que "Ana@x.com" y "ana@x.com" sean el mismo. */
    private function normalizarEmail(Request $request): void
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
    }

    /** Mensajes de validación en español. */
    private function mensajes(): array
    {
        return [
            'name.required' => 'Ingresa tu nombre completo.',
            'name.max' => 'El nombre no puede superar los 100 caracteres.',
            'email.required' => 'Ingresa tu correo.',
            'email.email' => 'Ingresa un correo válido.',
            'email.max' => 'El correo es demasiado largo.',
            'email.unique' => 'Ese correo ya está registrado.',
            'password.required' => 'Ingresa tu contraseña.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'token.required' => 'El enlace de recuperación no es válido.',
        ];
    }
}