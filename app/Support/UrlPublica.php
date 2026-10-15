<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Dirección base que lleva el QR de prueba.
 * Si la página se abrió con localhost / 127.0.0.1, el celular no podría abrirla,
 * así que se reemplaza por la IP de la computadora dentro de la red Wi-Fi.
 */
class UrlPublica
{
    public static function base(Request $request): string
    {
        $fija = trim((string) config('cafeqr.url_publica'));
        if ($fija !== '') {
            return rtrim($fija, '/');
        }

        $host = $request->getHost();

        if (self::esLocal($host)) {
            $ip = self::ipDeLaRed();
            if ($ip !== null) {
                $puerto = $request->getPort();
                $sufijo = in_array($puerto, [80, 443], true) ? '' : ':'.$puerto;

                return $request->getScheme().'://'.$ip.$sufijo;
            }
        }

        return rtrim($request->getSchemeAndHttpHost(), '/');
    }

    public static function esLocal(string $host): bool
    {
        return in_array(strtolower($host), ['localhost', '127.0.0.1', '::1', '[::1]', '0.0.0.0'], true);
    }

    public static function ipDeLaRed(): ?string
    {
        // Truco: abrir un "socket" UDP (no envía nada) y leer con qué IP saldría a la red.
        $socket = @stream_socket_client('udp://8.8.8.8:53', $codigo, $mensaje, 1);
        if ($socket) {
            $nombre = (string) stream_socket_get_name($socket, false);
            fclose($socket);
            $ip = explode(':', $nombre)[0] ?? '';
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && ! str_starts_with($ip, '127.')) {
                return $ip;
            }
        }

        $ip = gethostbyname((string) gethostname());
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && ! str_starts_with($ip, '127.')) {
            return $ip;
        }

        return null;
    }
}
