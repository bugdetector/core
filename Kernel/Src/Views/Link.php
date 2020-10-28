<?php

namespace Src\Views;

use Src\Theme\View;

class Link extends View
{
    public $url;
    public $label;

    public static function create($url, $label): Link
    {
        $link = new Link();
        $link->url = $url;
        $link->label = $label;
        return $link;
    }

    public function getTemplateFile(): string
    {
        return "link.twig";
    }
}
