<?php

namespace Src\Views;

use Src\Theme\View;

class TextElement extends View
{
    public $tagName = "span";
    public $text;
    public bool $raw = false;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public static function create($text): TextElement
    {
        return new TextElement($text);
    }

    public function setIsRaw(bool $raw): TextElement
    {
        $this->raw = $raw;
        return $this;
    }

    public function setTagName(string $tagName): TextElement
    {
        $this->tagName = $tagName;
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "text-element.twig";
    }
}
