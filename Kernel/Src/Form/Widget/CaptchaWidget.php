<?php

namespace Src\Form\Widget;

use Src\Form\Widget\FormWidget;

class CaptchaWidget extends FormWidget
{
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->addClass("captcha-input");
        $this->addAttribute("style", "height: 50px");
        \CoreDB::controller()->addJsFiles("assets/js/components/captcha.js");
    }
    public static function create(string $name): CaptchaWidget
    {
        return new CaptchaWidget($name);
    }

    public function getTemplateFile(): string
    {
        return "captcha-widget.twig";
    }
}
