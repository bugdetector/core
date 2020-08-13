<?php
namespace Src\Form\Widget;

class TextareaWidget extends FormWidget
{

    public static function create(string $name) : TextareaWidget
    {
        return new TextareaWidget($name);
    }

    public function getTemplateFile(): string
    {
        return "textarea-widget.twig";
    }
}
