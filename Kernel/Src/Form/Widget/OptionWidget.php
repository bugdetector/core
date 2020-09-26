<?php

namespace Src\Form\Widget;

class OptionWidget extends FormWidget
{
    public $selected = false;

    public function __construct($value, $label)
    {
        $this->value = $value;
        $this->label = $label;
    }

    public function setSelected(bool $selected)
    {
        $this->selected = $selected;
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "option-widget.twig";
    }
}
