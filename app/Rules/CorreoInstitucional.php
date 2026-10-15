<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CorreoInstitucional implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $correo = strtolower(trim((string) $value));
        $host = substr(strrchr($correo, '@') ?: '', 1);

        $dominios = array_filter(array_map(
            fn ($d) => strtolower(trim($d)),
            explode(',', (string) config('cafeqr.dominio'))
        ));

        foreach ($dominios as $dominio) {
            if ($host === $dominio || str_ends_with($host, '.'.$dominio)) {
                return;
            }
        }

        $fail('Usa tu correo institucional (por ejemplo: tunombre@est.'.($dominios[0] ?? 'univalle.edu').').');
    }
}
