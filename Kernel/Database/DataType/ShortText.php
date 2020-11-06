<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;

class ShortText extends DataTypeAbstract
{
    public $length;
    /**
     * @inheritdoc
     */
    public static function getText(): string
    {
        return Translation::getTranslation("short_text");
    }

    /**
     * @inheritdoc
     */
    public function getWidget(): FormWidget
    {
        $widget = InputWidget::create("")
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
        return $this->getWidget()->removeAttribute("required");
    }

    /**
     * @inheritdoc
     */
    public function equals(DataTypeAbstract $dataType): bool
    {
        return parent::equals($dataType) &&
            (isset($dataType->length) ? $dataType->length == $this->length : false);
    }
}
