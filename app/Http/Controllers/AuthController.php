<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function mostrarRegistro(): View
    {
        return view('auth.register');
    }

    /** Cualquiera que se registre aquí es CLIENTE. Empleados y administrador se crean aparte. */
    public function registrar(RegistroRequest $request): RedirectResponse
    {
        $usuario = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'telefono' => $request->input('telefono'),
            'password' => $request->input('password'),
            'rol' => User::ROL_CLIENTE,
        ]);

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('exito', '¡Bienvenido a CafeQR, '.$usuario->name.'! Ya puedes hacer tu pedido.');
    }

    public function mostrarLogin(): View
    {
        return view('auth.login');
    }

    public function entrar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Escribe tu correo.',
            'email.email' => 'Escribe un correo válido.',
            'password.required' => 'Escribe tu contraseña.',
        ]);

        $correo = Str::lower(trim($datos['email']));
        $clave = 'login:'.$correo.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $segundos = RateLimiter::availableIn($clave);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Demasiados intentos. Espera '.$segundos.' segundos y vuelve a intentar.']);
        }

        if (! Auth::attempt(['email' => $correo, 'password' => $datos['password']], $request->boolean('recordar'))) {
            RateLimiter::hit($clave, 60);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'El correo o la contraseña no son correctos.']);
        }

        RateLimiter::clear($clave);
        $request->session()->regenerate();

        return redirect()->intended(route($request->user()->rutaInicio()));
    }

    public function salir(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('exito', 'Cerraste sesión. ¡Hasta pronto!');
    }
}
