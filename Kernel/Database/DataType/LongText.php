<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\TextareaWidget;

class LongText extends DataTypeAbstract
{

    /**
     * @inheritdoc
     */
    public static function getText(): string
    {
        return Translation::getTranslation("long_text_or_html");
    }

    /**
     * @inheritdoc
     */
    public function getWidget(): FormWidget
    {
        $widget = TextareaWidget::create("")
        ->setValue($this->value)
        ->setDescription(Translation::getTranslation($this->comment))
        ->addClass("summernote");
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
