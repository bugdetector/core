<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\SelectWidget;

class EnumaratedList extends DataTypeAbstract
{

    public array $values = [];

    public function __construct(string $column_name)
    {
        parent::__construct($column_name);
    }

    /**
     * @inheritdoc
     */
    public static function getText(): string
    {
        return Translation::getTranslation("list");
    }

    /**
     * @inheritdoc
     */
    public function getWidget(): FormWidget
    {
        $options = [];
        foreach ($this->values as $key => $value) {
            $options[$key] = Translation::getTranslation($value);
        }
        /** @var SelectWidget */
        $widget = SelectWidget::create("")
            ->setValue($this->value)
            ->setOptions($options)
            ->setDescription(Translation::getTranslation($this->comment));
        if (!$this->isNull) {
            $widget
            ->setNullElement(null)
            ->addAttribute("required", "true");
        }
        return $widget;
    }

    public function setValue($value)
    {
        if (in_array($value, $this->values)) {
            $this->value = $value;
        } else {
            $this->value = "";
        }
    }
    /**
     * @inheritdoc
     */
    public function getSearchWidget(): ?FormWidget
    {
        return $this->getWidget()->removeAttribute("required");
    }

    /**
     * @inheritdoc
     */
    public function equals(DataTypeAbstract $dataType): bool
    {
        return parent::equals($dataType) &&
            (isset($dataType->values) ? $dataType->values === $this->values : false);
    }
}
