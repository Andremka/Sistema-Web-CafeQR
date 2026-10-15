<?php

namespace App\Support;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Qr
{
    /**
     * Devuelve el QR como imagen SVG lista para usar en <img src="...">.
     * Módulos negros sobre fondo blanco y con margen, para que cualquier celular lo lea.
     */
    public static function dataUri(string $texto): string
    {
        $opciones = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => true,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'quietzoneSize' => 4,
            'drawLightModules' => true,
            'connectPaths' => true,
        ]);

        return (new QRCode($opciones))->render($texto);
    }
}
