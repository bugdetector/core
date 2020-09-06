<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;

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
        return InputWidget::create("")
        ->setType("checkbox")
        ->setDescription(Translation::getTranslation($this->comment))
        ->removeClass("form-control");
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget() : FormWidget
    {
        return $this->getWidget();
    }
}
