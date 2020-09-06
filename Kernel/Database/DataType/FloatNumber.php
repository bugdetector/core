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
        return InputWidget::create("")->setType("number")->setDescription(Translation::getTranslation($this->comment))->addAttribute("step", "0.01");
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget() : FormWidget
    {
        return $this->getWidget();
    }
}
