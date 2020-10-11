<?php

namespace Src\Views;

use Src\Theme\View;

class Image extends View
{
    public string $src;
    public string $alt;
    public bool $showAlt;

    public static function create(string $src, string $alt, bool $showAlt = false): Image
    {
        $image = new Image();
        $image->src = $src;
        $image->alt = $alt;
        $image->showAlt = $showAlt;
        return $image;
    }

    public function getTemplateFile(): string
    {
        return "image.twig";
    }
}
