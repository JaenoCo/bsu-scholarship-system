<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class GrantQrCodeService
{
    public function generate(string $value): string
    {
        $renderer = new ImageRenderer(new RendererStyle(256), new SvgImageBackEnd());

        return (new Writer($renderer))->writeString($value);
    }
}
