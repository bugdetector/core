<?php

namespace Src\Views;

use CoreDB;
use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\OptionWidget;
use Src\Form\Widget\SelectWidget;
use Src\Form\Widget\TextareaWidget;

class ColumnDefinition extends CollapsableCard
{
    private $definition;
    private $name;
    private $table_name;

    public function __construct($name, string $table_name = null, $definition = null)
    {
        $this->name = $name;
        $this->table_name = $table_name;
        $this->definition = $definition;
        
        $this->setId($this->name);
        $this->addClass("column_definition");
        $this->opened = true;
    }

    public static function create($name, string $table_name = null, $definition = null): ColumnDefinition
    {
        return new ColumnDefinition($name, $table_name, $definition);
    }

    public function setTable(string $table)
    {
        $this->table = $table;
        return $this;
    }

    public function render()
    {
        $field_name_input = InputWidget::create("{$this->name}[field_name]")
            ->addClass("lowercase_filter column_name")
            ->addAttribute("placeholder", Translation::getTranslation("column_name"))
            ->addAttribute("autocomplete", "off")
            ->addAttribute("required", "true")
            ->setLabel(Translation::getTranslation("column_name"))
            ->setDescription(Translation::getTranslation("available_characters", ["a-z, _, 1-9"]));

        $data_type_options = [];
        foreach (\CoreDB::database()::get_supported_data_types() as $key => $value) {
            $option = new OptionWidget($key, $value["value"]);
            if($value["selected_callback"]($this->definition)["checked"]){
                $option->setSelected(true);
            }
            $data_type_options[] = $option;
        }
        $data_type_select = new SelectWidget("{$this->name}[field_type]");
        $data_type_select->setLabel(Translation::getTranslation("data_type"))
            ->setNullElement(null)
            ->addAttribute("required", "true")
            ->addClass("type-control")
            ->setOptions($data_type_options);

        $reference_table_select = SelectWidget::create("{$this->name}[reference_table]")
        ->addClass("reference_table")
        ->setLabel(Translation::getTranslation("reference_table"))
        ->setNullElement(Translation::getTranslation("reference_table"))
        ->setOptions(\CoreDB::database()::getTableList());

        $list_values_input = TextareaWidget::create("{$this->name}[list_values]")
        ->addClass("list_values")
        ->setLabel(Translation::getTranslation("list_values"))
        ->addAttribute("placeholder", Translation::getTranslation("list_values") )
        ->setDescription(Translation::getTranslation("list_values_description"));

        $field_length = InputWidget::create("{$this->name}[field_length]")
        ->addClass("field_length")
        ->setLabel(Translation::getTranslation("length_varchar"))
        ->addAttribute("placeholder", Translation::getTranslation("length_varchar"));
        
        $is_unique_checkbox = InputWidget::create("{$this->name}[is_unique]")
        ->setType("checkbox")
        ->setLabel(Translation::getTranslation("unique"))
        ->removeClass("form-control");

        if($this->table_name){
            $existing_comment = CoreDB::database()->getColumnComment($this->table_name, $this->definition["Field"]);
        }else{
            $existing_comment = "";
        }
        $column_comment = TextareaWidget::create("{$this->name}[comment]")
        ->setValue($existing_comment)
        ->addAttribute("placeholder", Translation::getTranslation("column_comment"))
        ->addClass("my-2");

        $remove_button = ViewGroup::create("a", "btn btn-danger removefield")
        ->addAttribute("href", "#")
        ->addField(
            ViewGroup::create("i", "fa fa-trash")
        )
        ->addField(TextElement::create(Translation::getTranslation("drop_column")));

        if ($this->definition) {
            $this->title = $this->definition["Field"];
            $field_name_input->setValue($this->definition["Field"])
                ->addAttribute("disabled", "true");
            $data_type_select->addAttribute("disabled", "true");
            $is_unique_checkbox->addAttribute("disabled", "true");
            $reference_table_select->addAttribute("disabled", "true");
            $list_values_input->addAttribute("disabled", "true");
            $field_length->addAttribute("disabled", "true");
            $column_comment->addAttribute("disabled", "true");
            $remove_button->removeClass("removefield")
            ->addClass("dropfield");

            if(strpos($this->definition["Key"], "UNI") !== false){
                $is_unique_checkbox->addAttribute("checked", "true");
            }
            if(strpos($this->definition["Key"], "MUL") !== false){
                $fk_description = \CoreDB::database()::getForeignKeyDescription( $this->table_name, $this->definition["Field"] );
                $reference_table_select->setValue($fk_description["REFERENCED_TABLE_NAME"]);
            }
            if(strpos($this->definition["Type"], "varchar") !== false){
                $field_length->setValue(filter_var($this->definition["Type"], FILTER_SANITIZE_NUMBER_INT));
            }

            if(strpos($this->definition["Type"], "enum") !== false){
                $options = CoreDB::database()->getEnumValues($this->table_name, $this->definition["Field"]);
                $list_values_input->setValue( implode(",", $options) );
            }
            
        }else{
            $this->title = Translation::getTranslation("new_field");
        }

        $this->content = new ViewGroup("div", "row");
        $this->content->addField(
            ViewGroup::create("div", "col-sm-3")
                ->addField($field_name_input)
        )->addField(
            ViewGroup::create("div", "col-sm-3")
                ->addField($data_type_select)
        )->addField(
            ViewGroup::create("div", "col-sm-3 ".(!$reference_table_select->value ? "d-none" : ""))
                ->addField($reference_table_select)
        )->addField(
            ViewGroup::create("div", "col-sm-3 ".(!$field_length->value ? "d-none" : ""))
                ->addField($field_length)
        )->addField(
            ViewGroup::create("div", "col-sm-3")
                ->addField($is_unique_checkbox)
        )->addField(
            ViewGroup::create("div", "col-sm-12 ".(!$list_values_input->value ? "d-none" : ""))
                ->addField($list_values_input)
        )->addField(
            ViewGroup::create("div", "col-sm-12")
                ->addField($column_comment)
        )->addField(
            ViewGroup::create("div", "col-sm-3")
                ->addField($remove_button)
        );
        parent::render();
    }
}
