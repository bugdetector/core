<?php

namespace Src\Form\Widget;

class InputWidget extends FormWidget
{
    public $type = "text";

    public static function create(string $name): InputWidget
    {
        return new InputWidget($name);
    }

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "input-widget.twig";
    }
}
