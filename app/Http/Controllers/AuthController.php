<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Autentica al usuario. Si es la cuenta admin@cafeqr.edu, Laravel lo
     * redirige al panel de administración en vez del menú de compra.
     */
    public function login(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credenciales, true)) {
            return back()
                ->withErrors(['email' => 'Correo o contraseña incorrectos.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return Auth::user()->esAdministrador()
            ? redirect()->route('admin.index')
            : redirect()->route('menu.index');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $usuario = User::create([
            ...$datos,
            'password' => Hash::make($datos['password']),
        ]);

        Auth::login($usuario);

        return redirect()->route('menu.index')->with('exito', 'Cuenta creada. ¡Bienvenido a CafeQR!');
    }

    public function showForgot(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Simula el envío de un enlace de recuperación de contraseña.
     * TODO: conectar Illuminate\Auth\Passwords\PasswordBroker con un
     * proveedor de correo real (SMTP) antes de pasar a producción.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        return redirect()
            ->route('login')
            ->with('exito', "Enlace de recuperación enviado a {$request->email}");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
