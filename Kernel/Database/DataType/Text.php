<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\TextareaWidget;

class Text extends DataTypeAbstract
{

    /**
     * @inheritdoc
     */
    public static function getText(): string
    {
        return Translation::getTranslation("text");
    }

    /**
     * @inheritdoc
     */
    public function getWidget(): FormWidget
    {
        $widget = TextareaWidget::create("")
        ->setDescription(Translation::getTranslation($this->comment))
        ->setValue($this->value);
        if (!$this->isNull) {
            $widget->addAttribute("required", "true");
        }
        return $widget;
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget(): FormWidget
    {
        return InputWidget::create("");
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->value = json_encode($value);
        } else {
            $this->value = $value;
        }
    }
}
