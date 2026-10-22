<?php

namespace App\Http\Controllers;

use App\Services\Notificador;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as ReglaPassword;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function mostrarSolicitud(): View
    {
        return view('auth.forgot-password');
    }

    public function enviarEnlace(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']], [
            'email.required' => 'Escribe tu correo.',
            'email.email' => 'Escribe un correo válido.',
        ]);

        $fallo = false;
        $estado = Password::sendResetLink(
            ['email' => Str::lower(trim($request->input('email')))],
            function ($usuario, string $token) use (&$fallo) {
                $url = route('password.reset', ['token' => $token, 'email' => $usuario->email]);
                $minutos = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
                $fallo = ! Notificador::recuperar($usuario, $url, $minutos);
            }
        );

        if ($estado === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' => 'Espera un minuto antes de pedir otro enlace.']);
        }

        if ($fallo) {
            return back()->withInput()->with('error', 'No pudimos enviar el correo en este momento. Intenta de nuevo en unos minutos.');
        }

        // Misma respuesta exista o no el correo, para no revelar quién está registrado.
        return back()->with('exito', 'Si ese correo está registrado, te enviamos un enlace para crear una nueva contraseña. Revisa también la carpeta de spam.');
    }

    public function mostrarRestablecer(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function restablecer(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', ReglaPassword::min(8)->letters()->numbers()],
        ], [
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.letters' => 'La contraseña debe incluir al menos una letra.',
            'password.numbers' => 'La contraseña debe incluir al menos un número.',
        ]);

        $estado = Password::reset(
            ['email' => Str::lower(trim($datos['email'])), 'password' => $datos['password'], 'password_confirmation' => $request->input('password_confirmation'), 'token' => $datos['token']],
            function ($usuario, string $password) {
                $usuario->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($usuario));
            }
        );

        if ($estado !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'El enlace venció o no es válido. Pide uno nuevo.']);
        }

        return redirect()->route('login')->with('exito', 'Listo, cambiaste tu contraseña. Ya puedes iniciar sesión.');
    }
}
