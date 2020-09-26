<?php 

namespace Src\Views;

use Src\Theme\View;

class Image extends View
{
    public $src;
    public $alt;

    public static function create(string $src, string $alt) : Image
    {
        $image = new Image();
        $image->src = $src;
        $image->alt = $alt;
        return $image;
    }

    public function getTemplateFile() : string
    {
        return "image.twig";
    }

}
