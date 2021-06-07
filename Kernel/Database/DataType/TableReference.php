<?php

namespace CoreDB\Kernel\Database\DataType;

use CoreDB;
use CoreDB\Kernel\Database\TableDefinition;
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
        $referenceTableColumns = TableDefinition::getDefinition(
            $this->reference_table
        )->fields;
        $firstColumnName = array_keys($referenceTableColumns)[1];
        $entryQuery = CoreDB::database()
        ->select($this->reference_table, "rt")
        ->select("rt", ["ID", $firstColumnName])
        ->orderBy("ID")
        ->limit(20);
        if ($this->value) {
            $entryQuery->condition("rt.ID", $this->value);
        }
        $entries = $entryQuery
        ->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $options = [];
        foreach ($entries as $entry) {
            $options[$entry["ID"]] = $entry[$firstColumnName];
        }

        $widget = SelectWidget::create("")
            ->setValue($this->value)
            ->setOptions($options)
            ->setAutoComplete($this->reference_table, $firstColumnName)
            ->setDescription(Translation::getTranslation($this->comment))
            ->addClass("autocomplete")
            ->addAttribute("data-live-search", "true");
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
