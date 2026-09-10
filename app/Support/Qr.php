<?php

namespace App\Support;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;

class Qr
{
    /**
     * Genera un código QR como data URI (SVG) para incrustar en <img>.
     */
    public static function svgDataUri(string $content, int $size = 220): string
    {
        return (new Builder())->build(
            writer: new SvgWriter(),
            data: $content,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: $size,
            margin: 8,
            foregroundColor: new Color(13, 110, 253),
            backgroundColor: new Color(255, 255, 255)
        )->getDataUri();
    }
}