<?php

namespace Src\Views;

use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\SelectWidget;
use Src\Form\Widget\TextareaWidget;

class ColumnDefinition extends CollapsableCard
{
    private ?DataTypeAbstract $dataType;
    private string $name;

    public function __construct(string $name, DataTypeAbstract $dataType = null)
    {
        $this->name = $name;
        $this->dataType = $dataType;

        $this->setId($this->name);
        $this->addClass("column_definition");
        $this->opened = true;

        $field_name_input = InputWidget::create("{$this->name}[field_name]");
        $field_name_input->setLabel(Translation::getTranslation("column_name"))
            ->setDescription(Translation::getTranslation("available_characters", ["a-z, _, 1-9"]))
            ->addClass("lowercase_filter column_name")
            ->addAttribute("placeholder", Translation::getTranslation("column_name"))
            ->addAttribute("autocomplete", "off")
            ->addAttribute("required", "true");

        $data_type_options = [];
        $dataTypes = \CoreDB::database()::dataTypes();
        foreach ($dataTypes as $key => $dataType) {
            /**
             * @var DataTypeAbstract $dataType
             */
            $data_type_options[$key] =  ($dataType)::getText();
        }

        $data_type_select = new SelectWidget("{$this->name}[field_type]");
        $data_type_select->setLabel(Translation::getTranslation("data_type"))
            ->setNullElement(null)
            ->setOptions($data_type_options)
            ->addAttribute("required", "true")
            ->addAttribute("data-live-search", "true")
            ->addClass("type-control");

        $reference_table_select = SelectWidget::create("{$this->name}[reference_table]");
        $reference_table_select->setLabel(Translation::getTranslation("reference"))
        ->setNullElement(Translation::getTranslation("reference_table"))
        ->setOptions(\CoreDB::database()::getTableList())
        ->addClass("reference_table")
        ->addAttribute("data-live-search", "true");

        $list_values_input = TextareaWidget::create("{$this->name}[list_values]");
        $list_values_input->setLabel(Translation::getTranslation("list_values"))
        ->setDescription(Translation::getTranslation("list_values_description"))
        ->addClass("list_values")
        ->addAttribute("placeholder", Translation::getTranslation("list_values"));

        $field_length = InputWidget::create("{$this->name}[field_length]");
        $field_length->setLabel(Translation::getTranslation("length"))
        ->setValue("255")
        ->addClass("field_length")
        ->addAttribute("placeholder", Translation::getTranslation("length"))
        ->addAttribute("required", "true");

        $is_unique_checkbox = InputWidget::create("{$this->name}[is_unique]")
        ->setType("checkbox")
        ->setLabel(Translation::getTranslation("unique"))
        ->removeClass("form-control");

        $not_empty_checkbox = InputWidget::create("{$this->name}[not_empty]")
        ->setType("checkbox")
        ->setLabel(Translation::getTranslation("not_empty"))
        ->removeClass("form-control");

        $column_comment = TextareaWidget::create("{$this->name}[comment]");
        $column_comment->addAttribute("placeholder", Translation::getTranslation("column_comment"))
        ->addClass("my-2");

        $remove_button = ViewGroup::create("a", "btn btn-danger btn-sm removefield text-white")
        ->addAttribute("href", "#")
        ->addField(
            ViewGroup::create("i", "fa fa-trash")
        )
        ->addField(TextElement::create(Translation::getTranslation("drop_column")));

        if ($this->dataType) {
            $this->title = $this->dataType->column_name;
            $field_name_input->setValue($this->dataType->column_name);
            $data_type_select->setValue(array_search(get_class($this->dataType), $dataTypes));
            $column_comment->setValue($this->dataType->comment);
            $remove_button->removeClass("removefield")
                ->addClass("dropfield");

            if ($this->dataType->isUnique) {
                $is_unique_checkbox->addAttribute("checked", "true");
            }
            if (!$this->dataType->isNull) {
                $not_empty_checkbox->addAttribute("checked", "true");
            }
            if ($this->dataType instanceof \CoreDB\Kernel\Database\DataType\TableReference) {
                $reference_table_select->setValue($this->dataType->reference_table);
            }
            if ($this->dataType instanceof \CoreDB\Kernel\Database\DataType\ShortText) {
                $field_length->setValue(strval($this->dataType->length));
            }

            if ($this->dataType instanceof \CoreDB\Kernel\Database\DataType\EnumaratedList) {
                $list_values_input->setValue(implode(",", array_keys($this->dataType->values)));
            }
        } else {
            $this->title = Translation::getTranslation("new_field");
        }

        $this->content = new ViewGroup("div", "row");
        $this->content->addField(
            ViewGroup::create("div", "col-sm-3")
                ->addField($field_name_input)
        )->addField(
            ViewGroup::create("div", "col-sm-4")
                ->addField($data_type_select)
        )->addField(
            ViewGroup::create("div", "col-sm-4 " . (!$reference_table_select->value ? "d-none" : ""))
                ->addField($reference_table_select)
        )->addField(
            ViewGroup::create("div", "col-sm-2 " .
                (!($this->dataType instanceof \CoreDB\Kernel\Database\DataType\ShortText) ? "d-none" : ""))
                ->addField($field_length)
        )->addField(
            ViewGroup::create("div", "col-sm-2")
                ->addField($is_unique_checkbox)
        )->addField(
            ViewGroup::create("div", "col-sm-2")
                ->addField($not_empty_checkbox)
        )->addField(
            ViewGroup::create("div", "col-sm-12 " . (!$list_values_input->value ? "d-none" : ""))
                ->addField($list_values_input)
        )->addField(
            ViewGroup::create("div", "col-sm-12")
                ->addField($column_comment)
        )->addField(
            ViewGroup::create("div", "col-sm-3")
                ->addField($remove_button)
        );
    }

    public static function create($name, $dataType = null): ColumnDefinition
    {
        return new ColumnDefinition($name, $dataType);
    }
}
