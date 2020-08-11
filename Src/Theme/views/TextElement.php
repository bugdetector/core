<?php

namespace Src\Theme\Views;

use Src\Theme\View;

class TextElement extends View
{
    public $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public static function create($text) : TextElement{
        return new TextElement($text);
    }

    public function getTemplateFile(): string
    {
        return "text-element.twig";
    }

    public function render()
    {
        return $this->text;
    }
}
