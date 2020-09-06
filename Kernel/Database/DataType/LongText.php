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
        return TextareaWidget::create("")->setDescription(Translation::getTranslation($this->comment))->addClass("summernote");
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget() : FormWidget
    {
        return InputWidget::create("");
    }
}
