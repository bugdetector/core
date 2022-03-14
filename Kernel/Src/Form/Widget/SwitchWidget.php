<?php

namespace Src\Form\Widget;

class SwitchWidget extends FormWidget
{
    public bool $isNull = true;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->removeClass("form-control");
        $this->addClass("form-check-input");
        \CoreDB::controller()->addJsFiles("assets/js/components/checkbox.js");
    }

    public static function create(string $name): SwitchWidget
    {
        return new SwitchWidget($name);
    }

    public function getTemplateFile(): string
    {
        return "switch-widget.twig";
    }

    public function setValue($value)
    {
        $this->value = $value;
        if ($this->value) {
            $this->addAttribute("checked", "true");
        } else {
            $this->removeAttribute("checked");
        }
        return $this;
    }
}
