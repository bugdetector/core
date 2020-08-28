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
        return InputWidget::create("")->setType("number");
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget() : FormWidget
    {
        return $this->getWidget();
    }
}
