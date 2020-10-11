<?php

namespace CoreDB\Kernel\Database\DataType;

use CoreDB;
use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\SelectWidget;

class TableReference extends DataTypeAbstract
{

    public string $reference_table = "";
    /**
     * @inheritdoc
     */
    public static function getText(): string
    {
        return Translation::getTranslation("reference_table");
    }

    /**
     * @inheritdoc
     */
    public function getWidget(): FormWidget
    {
        $entries = CoreDB::database()
        ->select($this->reference_table)
        ->orderBy("ID")
        ->execute()->fetchAll(\PDO::FETCH_NUM);
        $options = [];
        foreach ($entries as $entry) {
            $options[$entry[0]] = $entry[1];
        }

        $widget = SelectWidget::create("")
            ->setValue($this->value)
            ->setOptions($options)
            ->setDescription(Translation::getTranslation($this->comment))
            ->addClass("autocomplete")
            ->addAttribute("data-reference-table", $this->reference_table)
            ->addAttribute("data-reference-column", $this->column_name);
        if (!$this->isNull) {
            /**
             * @var SelectWidget $widget
             */
            $widget->setNullElement("")
            ->addAttribute("required", "true");
        }
        return $widget;
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget(): ?FormWidget
    {
        /**
         * @var SelectWidget
         */
        $widget = $this->getWidget();
        $widget->setNullElement(Translation::getTranslation("all"))
        ->removeAttribute("required");
        return $widget;
    }

    public function setValue($value)
    {
        $valueAvailable = \CoreDB::database()->select($this->reference_table)
        ->condition("ID", $value)
        ->execute()->rowCount();
        if ($valueAvailable) {
            $this->value = $value;
        } else {
            $this->value = "";
        }
    }

    /**
     * @inheritdoc
     */
    public function equals(DataTypeAbstract $dataType): bool
    {
        return parent::equals($dataType) &&
            (isset($dataType->reference_table) ? $dataType->reference_table == $this->reference_table : false);
    }
}
