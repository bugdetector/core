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
        $entries = CoreDB::database()->select($this->reference_table)->orderBy("ID")->execute()->fetchAll(\PDO::FETCH_NUM);
        $options = [];
        foreach ($entries as $entry) {
            $options[$entry[0]] = $entry[1];
        }

        return SelectWidget::create("")
            ->setOptions($options)
            ->setDescription($this->comment)
            ->addClass("autocomplete")
            ->addAttribute("data-reference-table", $this->reference_table)
            ->addAttribute("data-reference-column", $this->column_name);
    }

    /**
     * @inheritdoc
     */
    public function getSearchWidget() : FormWidget
    {
        return $this->getWidget();
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
