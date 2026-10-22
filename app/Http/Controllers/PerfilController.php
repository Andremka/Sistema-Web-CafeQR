<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Notificador;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function mostrar(Request $request): View
    {
        $usuario = $request->user();

        return view('perfil.show', [
            'usuario' => $usuario,
            'totalPedidos' => $usuario->pedidos()->whereNotIn('estado', ['carrito'])->count(),
        ]);
    }

    public function actualizar(Request $request): RedirectResponse
    {
        $request->merge([
            'name' => preg_replace('/\s+/u', ' ', trim((string) $request->input('name'))),
            'telefono' => preg_replace('/[\s-]+/', '', (string) $request->input('telefono')) ?: null,
        ]);

        $datos = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:60', "regex:/^\\pL+(?:[\\s.'-]+\\pL+)*\\.?$/u"],
            'telefono' => ['nullable', 'regex:/^[67]\d{7}$/'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 letras.',
            'name.regex' => 'El nombre solo puede tener letras y espacios (sin números ni símbolos).',
            'telefono.regex' => 'El celular debe tener 8 dígitos y empezar con 6 o 7.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.mimes' => 'La foto debe ser JPG, PNG o WEBP.',
            'foto.max' => 'La foto no puede pesar más de 2 MB.',
        ]);

        $usuario = $request->user();
        $usuario->name = $datos['name'];
        $usuario->telefono = $datos['telefono'] ?? null;

        if ($request->hasFile('foto')) {
            $carpeta = public_path('fotos');
            if (! is_dir($carpeta)) {
                mkdir($carpeta, 0775, true);
            }
            $archivo = $request->file('foto');
            $nombre = 'u'.$usuario->id.'_'.time().'.'.$archivo->getClientOriginalExtension();
            $archivo->move($carpeta, $nombre);

            if ($usuario->foto && is_file($carpeta.DIRECTORY_SEPARATOR.$usuario->foto)) {
                @unlink($carpeta.DIRECTORY_SEPARATOR.$usuario->foto);
            }
            $usuario->foto = $nombre;
        }

        $usuario->save();

        return back()->with('exito', 'Tus datos se guardaron.');
    }

    public function cambiarPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password_actual' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'password_actual.required' => 'Escribe tu contraseña actual.',
            'password_actual.current_password' => 'La contraseña actual no es correcta.',
            'password.confirmed' => 'Las contraseñas nuevas no coinciden.',
            'password.min' => 'La contraseña nueva debe tener al menos 8 caracteres.',
            'password.letters' => 'La contraseña nueva debe incluir al menos una letra.',
            'password.numbers' => 'La contraseña nueva debe incluir al menos un número.',
        ]);

        $request->user()->update(['password' => $request->input('password')]);

        return back()->with('exito', 'Cambiaste tu contraseña.');
    }

    /** Enlace del correo de bienvenida: marca el correo como confirmado. */
    public function verificarCorreo(Request $request, int $id, string $hash): RedirectResponse
    {
        $usuario = User::find($id);

        if (! $usuario || ! hash_equals(sha1($usuario->email), $hash)) {
            return redirect()->route('login')->with('error', 'El enlace de verificación no es válido.');
        }

        if (! $usuario->email_verified_at) {
            $usuario->forceFill(['email_verified_at' => now()])->save();
        }

        $destino = $request->user() ? route('perfil.show') : route('login');

        return redirect($destino)->with('exito', '¡Listo! Confirmaste tu correo '.$usuario->email.'.');
    }

    public function reenviarVerificacion(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        if ($usuario->email_verified_at) {
            return back()->with('exito', 'Tu correo ya está confirmado.');
        }

        if (! Notificador::bienvenida($usuario)) {
            return back()->with('error', 'No se pudo enviar el correo a '.$usuario->email.'. Avísale al administrador de la cafetería.');
        }

        return back()->with('exito', Notificador::modoPrueba()
            ? 'Modo prueba: el correo no sale a internet (MAIL_MAILER=log).'
            : 'Te reenviamos el correo a '.$usuario->email.'. Revisa también Spam o Promociones.');
    }
}
