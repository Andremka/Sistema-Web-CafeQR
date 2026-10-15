<?php

namespace App\Http\Requests;

use App\Rules\CorreoInstitucional;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => preg_replace('/\s+/u', ' ', trim((string) $this->input('name'))),
            'email' => Str::lower(trim((string) $this->input('email'))),
            'telefono' => preg_replace('/[\s-]+/', '', (string) $this->input('telefono')) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            // Solo letras (con tildes y ñ), espacios, punto, apóstrofe y guion. Sin números.
            'name' => ['required', 'string', 'min:3', 'max:60', "regex:/^\\pL+(?:[\\s.'-]+\\pL+)*\\.?$/u"],
            'email' => ['required', 'string', 'email:rfc', 'max:120', 'unique:users,email', new CorreoInstitucional],
            // Celular boliviano: 8 dígitos que empiezan con 6 o 7
            'telefono' => ['nullable', 'regex:/^[67]\d{7}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 letras.',
            'name.max' => 'El nombre no puede pasar de 60 caracteres.',
            'name.regex' => 'El nombre solo puede tener letras y espacios (sin números ni símbolos).',
            'email.email' => 'Escribe un correo válido, por ejemplo: tunombre@est.univalle.edu',
            'email.unique' => 'Ese correo ya está registrado. Inicia sesión o recupera tu contraseña.',
            'email.max' => 'El correo es demasiado largo.',
            'telefono.regex' => 'El celular debe tener 8 dígitos y empezar con 6 o 7 (ejemplo: 71234567).',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.letters' => 'La contraseña debe incluir al menos una letra.',
            'password.numbers' => 'La contraseña debe incluir al menos un número.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo',
            'telefono' => 'celular',
            'password' => 'contraseña',
        ];
    }
}
