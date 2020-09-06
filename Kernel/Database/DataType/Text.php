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
        return TextareaWidget::create("")->setDescription($this->comment);
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget() : FormWidget
    {
        return InputWidget::create("");
    }
}
