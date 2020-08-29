<?php

namespace Src\Views;

use Src\Theme\View;

class TextElement extends View
{
    public $tagName = "text";
    public $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public static function create($text) : TextElement
    {
        return new TextElement($text);
    }

    public function setTagName(string $tagName) : TextElement
    {
        $this->tagName = $tagName;
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "text-element.twig";
    }
}
