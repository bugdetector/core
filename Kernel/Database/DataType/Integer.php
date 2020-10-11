<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;

class Integer extends DataTypeAbstract
{

    public $length;
    /**
     * @inheritdoc
     */
    public static function getText(): string
    {
        return Translation::getTranslation("integer");
    }

    /**
     * @inheritdoc
     */
    public function getWidget(): FormWidget
    {
        $widget = InputWidget::create("")
        ->setType("number")
        ->setDescription(Translation::getTranslation($this->comment))
        ->setValue(strval($this->value));
        if (!$this->isNull) {
            $widget->addAttribute("required", "true");
        }
        return $widget;
    }

    public function setValue($value)
    {
        $this->value = $value ? intval($value) : "";
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget(): FormWidget
    {
        return $this->getWidget()->removeAttribute("required");
    }
}
