<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida la confirmación de pago de la historia HU-10.
 * El carrito viaja en sesión; aquí solo se valida lo que llega del formulario.
 */
class ConfirmarPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Reservado para cuando se conecte una pasarela real
            // (referencia de transacción, token de la pasarela, etc.).
        ];
    }
}
