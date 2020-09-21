<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;

class Time extends DataTypeAbstract
{

    /**
     * @inheritdoc
     */
    public static function getText(): string
    {
        return Translation::getTranslation("time");
    }

    /**
     * @inheritdoc
     */
    public function getWidget(): FormWidget
    {
        $rand_id = random_int(0, 100);
        $widget = InputWidget::create("")
        ->setValue($this->value)
        ->setDescription(Translation::getTranslation($this->comment))
        ->addClass("datetimeinput datetimepicker-input")
        ->addAttribute("id", $rand_id)
        ->addAttribute("data-target", "#" . $rand_id)
        ->addAttribute("data-toggle", "datetimepicker")
        ->addAttribute("autocomplete", "off");
        if(!$this->isNull){
            $widget->addAttribute("required", "true");
        }
        return $widget;
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget() : FormWidget
    {
        return InputWidget::create("")
        ->addClass("daterangeinput");
    }
}
