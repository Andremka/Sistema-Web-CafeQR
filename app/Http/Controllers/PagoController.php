<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Verificacion;
use App\Services\Notificador;
use App\Support\Qr;
use App\Support\UrlPublica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PagoController extends Controller
{
    /** Pantalla de pago: QR o efectivo, según lo que eligió el cliente. */
    public function mostrar(Request $request, Pago $pago): View|RedirectResponse
    {
        $pedido = $this->pedidoDelCliente($request, $pago);
        $pago->refrescarVencimiento();

        if ($destino = $this->destinoSiYaNoAplica($pedido, $pago)) {
            return $destino;
        }

        if ($pago->esEfectivo()) {
            return view('pagos.efectivo', ['pago' => $pago, 'pedido' => $pedido->load('detalles')]);
        }

        $modoBanco = config('cafeqr.pago_modo') === 'banco';
        $qr = null;
        $urlQr = null;
        $qrBancoDisponible = false;

        if ($modoBanco) {
            $qrBancoDisponible = is_file(public_path(config('cafeqr.qr_cobro')));
        } elseif ($pago->estado === 'pendiente') {
            $urlQr = UrlPublica::base($request).route('pagar.escanear', $pago->referencia, false);
            $qr = Qr::dataUri($urlQr);
        }

        return view('pagos.qr', [
            'pago' => $pago,
            'pedido' => $pedido->load('detalles'),
            'modoBanco' => $modoBanco,
            'qr' => $qr,
            'urlQr' => $urlQr,
            'qrBancoDisponible' => $qrBancoDisponible,
            'enLocalhost' => UrlPublica::esLocal($request->getHost()),
        ]);
    }

    /** La pantalla de pago lo consulta cada 3 segundos. */
    public function estado(Request $request, Pago $pago): JsonResponse
    {
        $pedido = $this->pedidoDelCliente($request, $pago);
        $pago->refrescarVencimiento();

        $siguiente = null;
        if (in_array($pedido->estado, [Pedido::PAGADO, Pedido::EN_PREPARACION, Pedido::LISTO, Pedido::ENTREGADO], true)) {
            $siguiente = route('pedido.exito', $pedido);
        } elseif ($pedido->estado === Pedido::CANCELADO) {
            $siguiente = route('pedidos.historial');
        } elseif ($pago->estado === 'recibido') {
            $siguiente = route('pago.codigo', $pago);
        }

        return response()->json(['estado' => $pago->estado, 'siguiente' => $siguiente]);
    }

    /** QR vencido o rechazado: genera un pago nuevo para el mismo pedido. */
    public function reintentar(Request $request, Pago $pago): RedirectResponse
    {
        $pedido = $this->pedidoDelCliente($request, $pago);

        if ($pedido->estado !== Pedido::PENDIENTE_PAGO) {
            return redirect()->route('pedido.actual');
        }

        $nuevo = Pago::iniciarPara($pedido, $pago->metodo);

        return redirect()->route('pago.show', $nuevo)->with('exito', 'Generamos un nuevo pago para tu pedido.');
    }

    /** Modo banco: el cliente avisa que ya pagó; la cafetería lo confirma en Caja. */
    public function yaPague(Request $request, Pago $pago): RedirectResponse
    {
        $this->pedidoDelCliente($request, $pago);
        $pago->refrescarVencimiento();

        if ($pago->estado === 'pendiente' && config('cafeqr.pago_modo') === 'banco') {
            $pago->marcarPorVerificar();
        }

        return redirect()->route('pago.show', $pago);
    }

    /** Modo demo: botón para simular el pago desde la misma computadora. */
    public function simular(Request $request, Pago $pago): RedirectResponse
    {
        $this->pedidoDelCliente($request, $pago);
        $pago->refrescarVencimiento();

        if (config('cafeqr.pago_modo') !== 'demo' || $pago->estado !== 'pendiente' || $pago->esEfectivo()) {
            return redirect()->route('pago.show', $pago);
        }

        $enviado = $pago->marcarRecibido();

        return redirect()->route('pago.codigo', $pago)->with($this->avisoCorreo($enviado, $pago));
    }

    public function codigo(Request $request, Pago $pago): View|RedirectResponse
    {
        $pedido = $this->pedidoDelCliente($request, $pago);

        if ($pago->estado !== 'recibido') {
            return redirect()->route('pago.show', $pago);
        }

        return view('pagos.codigo', [
            'pago' => $pago,
            'pedido' => $pedido,
            'correo' => $this->ocultarCorreo($pedido->user->email),
            'intentos' => Verificacion::intentosRestantes($pago),
            'modoPrueba' => Notificador::modoPrueba(),
        ]);
    }

    public function comprobarCodigo(Request $request, Pago $pago): RedirectResponse
    {
        $pedido = $this->pedidoDelCliente($request, $pago);

        if ($pago->estado !== 'recibido') {
            return redirect()->route('pago.show', $pago);
        }

        $request->merge(['codigo' => preg_replace('/\s+/', '', (string) $request->input('codigo'))]);
        $request->validate(['codigo' => ['required', 'digits:6']], [
            'codigo.required' => 'Escribe el código de 6 dígitos que te enviamos.',
            'codigo.digits' => 'El código tiene exactamente 6 números.',
        ]);

        $resultado = Verificacion::comprobar($pago, (string) $request->input('codigo'));

        if ($resultado === 'ok') {
            if (! $pago->completar()) {
                return redirect()->route('pedidos.historial')->with('error', 'El pedido ya no está disponible para pagar.');
            }

            return redirect()->route('pedido.exito', $pedido)->with('exito', '¡Pago confirmado!');
        }

        $mensaje = match ($resultado) {
            'incorrecto' => 'Código incorrecto. Te quedan '.Verificacion::intentosRestantes($pago).' intentos.',
            'vencido' => 'El código venció. Pide uno nuevo con "Reenviar código".',
            'bloqueado' => 'Demasiados intentos fallidos. Pide un código nuevo con "Reenviar código".',
            default => 'No hay un código activo. Pide uno nuevo con "Reenviar código".',
        };

        return back()->withErrors(['codigo' => $mensaje]);
    }

    public function reenviarCodigo(Request $request, Pago $pago): RedirectResponse
    {
        $this->pedidoDelCliente($request, $pago);

        if ($pago->estado !== 'recibido') {
            return redirect()->route('pago.show', $pago);
        }

        $ultimo = Verificacion::where('pago_id', $pago->id)->latest('id')->first();
        if ($ultimo && $ultimo->created_at->diffInSeconds(now()) < 45) {
            return back()->with('error', 'Espera unos segundos antes de pedir otro código.');
        }

        $enviado = Verificacion::enviarA($pago->load('pedido.user'));

        return back()->with($this->avisoCorreo($enviado, $pago, 'Te enviamos un código nuevo.'));
    }

    /* ---------- Páginas públicas del QR de prueba (se abren desde el celular) ---------- */

    public function escanear(string $referencia): View
    {
        $pago = $this->pagoDemo($referencia);
        $pago->refrescarVencimiento();

        return view('pagos.escanear', ['pago' => $pago->load('pedido.user'), 'pedido' => $pago->pedido]);
    }

    public function ejecutar(string $referencia): RedirectResponse
    {
        $pago = $this->pagoDemo($referencia);
        $pago->refrescarVencimiento();

        if ($pago->estado === 'pendiente') {
            $pago->marcarRecibido();
        }

        return redirect()->route('pagar.escanear', $referencia);
    }

    /* ---------- Ayudas ---------- */

    private function pagoDemo(string $referencia): Pago
    {
        abort_unless(config('cafeqr.pago_modo') === 'demo', 404);

        return Pago::where('referencia', $referencia)->where('metodo', Pago::METODO_QR)->firstOrFail();
    }

    private function pedidoDelCliente(Request $request, Pago $pago): Pedido
    {
        $pedido = $pago->pedido;
        abort_unless($pedido->user_id === $request->user()->id, 403);

        return $pedido;
    }

    private function destinoSiYaNoAplica(Pedido $pedido, Pago $pago): ?RedirectResponse
    {
        if (in_array($pedido->estado, [Pedido::PAGADO, Pedido::EN_PREPARACION, Pedido::LISTO, Pedido::ENTREGADO], true)) {
            return redirect()->route('pedido.exito', $pedido);
        }
        if ($pedido->estado === Pedido::CANCELADO) {
            return redirect()->route('pedidos.historial')->with('error', 'Ese pedido fue cancelado.');
        }
        if (in_array($pago->estado, ['cancelado', 'completado'], true) || $pedido->estado !== Pedido::PENDIENTE_PAGO) {
            return redirect()->route('pedido.actual');
        }
        if ($pago->estado === 'recibido') {
            return redirect()->route('pago.codigo', $pago);
        }

        return null;
    }

    private function avisoCorreo(bool $enviado, Pago $pago, string $base = 'Pago recibido.'): array
    {
        if (! $enviado) {
            return ['error' => $base.' Pero no pudimos enviar el código a tu correo. Pulsa "Reenviar código" o avisa en la cafetería.'];
        }
        if (Notificador::modoPrueba()) {
            return ['exito' => $base.' (Modo prueba: el correo no sale a internet, el código está en storage/logs/laravel.log.)'];
        }

        return ['exito' => $base.' Revisa tu correo: te enviamos un código de 6 dígitos.'];
    }

    private function ocultarCorreo(string $correo): string
    {
        [$usuario, $dominio] = array_pad(explode('@', $correo, 2), 2, '');

        return Str::substr($usuario, 0, 2).str_repeat('*', max(2, Str::length($usuario) - 2)).'@'.$dominio;
    }
}
