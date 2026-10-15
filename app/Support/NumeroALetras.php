<?php

namespace App\Support;

class NumeroALetras
{
    private const UNIDADES = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
        'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE',
        'VEINTE', 'VEINTIÚN', 'VEINTIDÓS', 'VEINTITRÉS', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISÉIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];

    private const DECENAS = [3 => 'TREINTA', 4 => 'CUARENTA', 5 => 'CINCUENTA', 6 => 'SESENTA', 7 => 'SETENTA', 8 => 'OCHENTA', 9 => 'NOVENTA'];

    private const CENTENAS = [1 => 'CIENTO', 2 => 'DOSCIENTOS', 3 => 'TRESCIENTOS', 4 => 'CUATROCIENTOS', 5 => 'QUINIENTOS',
        6 => 'SEISCIENTOS', 7 => 'SETECIENTOS', 8 => 'OCHOCIENTOS', 9 => 'NOVECIENTOS'];

    /** Ejemplo: 23.5 => "VEINTITRÉS 50/100 BOLIVIANOS" */
    public static function convertir(float $monto): string
    {
        $monto = round($monto, 2);
        $entero = (int) floor($monto);
        $centavos = (int) round(($monto - $entero) * 100);

        $texto = $entero === 0 ? 'CERO' : self::miles($entero);

        return sprintf('%s %02d/100 BOLIVIANOS', $texto, $centavos);
    }

    private static function miles(int $n): string
    {
        if ($n >= 1000000) {
            return (string) $n;
        }
        if ($n >= 1000) {
            $mil = intdiv($n, 1000);
            $resto = $n % 1000;
            $prefijo = $mil === 1 ? 'MIL' : self::centenas($mil).' MIL';

            return trim($prefijo.' '.($resto ? self::centenas($resto) : ''));
        }

        return self::centenas($n);
    }

    private static function centenas(int $n): string
    {
        if ($n === 100) {
            return 'CIEN';
        }

        $c = intdiv($n, 100);
        $resto = $n % 100;
        $texto = $c ? self::CENTENAS[$c] : '';

        if ($resto === 0) {
            return trim($texto);
        }

        return trim($texto.' '.self::decenas($resto));
    }

    private static function decenas(int $n): string
    {
        if ($n < 30) {
            return self::UNIDADES[$n];
        }

        $d = intdiv($n, 10);
        $u = $n % 10;

        return self::DECENAS[$d].($u ? ' Y '.self::UNIDADES[$u] : '');
    }
}
