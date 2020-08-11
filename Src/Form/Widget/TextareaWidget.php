<?php
namespace Src\Form\Widget;

class TextareaWidget extends FormWidget
{

    public function getTemplateFile(): string
    {
        return "textarea-widget.twig";
    }
}
