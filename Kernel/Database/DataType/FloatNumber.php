<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;

class FloatNumber extends DataTypeAbstract
{

    /**
     * @inheritdoc
     */
    public static function getText(): string
    {
        return Translation::getTranslation("float");
    }

    /**
     * @inheritdoc
     */
    public function getWidget(): FormWidget
    {
        $widget = InputWidget::create("")
        ->setValue(strval($this->value))
        ->setType("number")
        ->setDescription(Translation::getTranslation($this->comment))
        ->addAttribute("step", "0.01");
        if (!$this->isNull) {
            $widget->addAttribute("required", "true");
        }
        return $widget;
    }


    public function setValue($value)
    {
        $this->value = $value ? floatval($value) : "";
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget(): FormWidget
    {
        return $this->getWidget()->removeAttribute("required");
    }
}
