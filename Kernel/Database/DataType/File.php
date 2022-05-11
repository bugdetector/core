<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;

class File extends TableReference
{
    public function __construct(string $column_name)
    {
        parent::__construct($column_name);
        $this->reference_table = "files";
    }
    /**
     * @inheritdoc
     */
    public static function getText(): string
    {
        return Translation::getTranslation("file");
    }

    /**
     * @inheritdoc
     */
    public function getWidget(): FormWidget
    {
        $widget = InputWidget::create("")
        ->setType("file")
        ->setDescription(Translation::getTranslation($this->comment))
        ->setValue($this->value)
        ->addClass("p-1");
        if (!$this->isNull) {
            $widget->addAttribute("required", "true");
        }
        return $widget;
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget(): ?FormWidget
    {
        return null;
    }
}
