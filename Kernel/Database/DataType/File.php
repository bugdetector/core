<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;

class File extends DataTypeAbstract
{    
    /**
     * @inheritdoc
     */
    public static function getText(): string{
        return Translation::getTranslation("file");
    }

    /**
     * @inheritdoc
     */
    public function getWidget(): FormWidget{
        return InputWidget::create("")->setType("file");
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget() : FormWidget{
        return $this->getWidget();
    }
}
