<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\SelectWidget;

class Checkbox extends DataTypeAbstract
{
    /**
     * @inheritdoc
     */
    public static function getText(): string
    {
        return Translation::getTranslation("checkbox");
    }

    /**
     * @inheritdoc
     */
    public function getWidget(): FormWidget
    {
        $widget = InputWidget::create("")
        ->setType("checkbox")
        ->setDescription(Translation::getTranslation($this->comment))
        ->removeClass("form-control")
        ->setValue($this->value);
        if (!$this->isNull) {
            $widget->addAttribute("required", "true");
        }
        return $widget;
    }

    public function setValue($value)
    {
        $this->value = intval($value);
    }
    /**
     * @inheritdoc
     */
    public function getSearchWidget(): FormWidget
    {
        return SelectWidget::create("")
        ->setOptions([
            1 => Translation::getTranslation("on"),
            0 => Translation::getTranslation("off")
        ]);
    }
}
