<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\SelectWidget;
use Src\Form\Widget\SwitchWidget;

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
        $widget = SwitchWidget::create("")
        ->setDescription(Translation::getTranslation($this->comment))
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
