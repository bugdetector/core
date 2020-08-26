<?php

namespace Src\Form;

use CoreDB;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\Messenger;
use Exception;
use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;
use Src\Views\ColumnDefinition;

class TableStructForm extends Form
{
    public string $method = "POST";

    public TableDefinition $table_definition;
    public string $table_name;
    public string $table_comment;
    public array $columns = [];

    public function __construct(string $table_name, string $table_comment)
    {
        parent::__construct();
        \CoreDB::controller()->addJsFiles("src/js/table_struct.js");
        if(!empty($this->request)){
            $this->table_name =  preg_replace("/[^a-z1-9_]+/", "", $this->request["table_name"]);
            $this->table_comment = htmlspecialchars($this->request["table_comment"]);

            $this->table_definition = TableDefinition::getDefinition($this->table_name);
            $this->table_definition->setComment($this->table_comment);
            $fields = isset($this->request["fields"]) ? $this->request["fields"] : [];
            $dataTypes = CoreDB::database()->dataTypes();
            foreach ($fields as $field) {
                /**
                 * @var DataTypeAbstract
                 */
                $dataType = new $dataTypes[$field["field_type"]]($field["field_name"]);
                $dataType->comment = $field["comment"];
                $dataType->isUnique = boolval($field["is_unique"]);
                if($dataType instanceof ShortText){
                    $dataType->length = $field["field_length"];
                }else if($dataType instanceof TableReference){
                    $dataType->reference_table = $field["reference_table"];
                }else if($dataType instanceof EnumaratedList){
                    $dataType->values = explode(",", $field["list_values"]);
                }
                $this->table_definition->addField($dataType);
            }
        }else{
            $this->table_definition = TableDefinition::getDefinition($table_name);
            $this->table_definition->setComment($table_comment);
        }
    }

    public function getFormId(): string
    {
        return "table_struct_form";
    }

    public function getTemplateFile(): string
    {
        return "table-struct-form.twig";
    }

    public function validate(): bool
    {
        if (@preg_match("/[^a-z1-9_]+/", $this->request["table_name"])) {
            $this->setError("table_name", Translation::getTranslation("table_name") . ": " . Translation::getTranslation("available_characters", ["a-z, _, 1-9"]));
        }
        if (@preg_match("/[^\p{L}\p{N} ]+/u", $this->request["table_comment"])) {
            $this->setError("table_comment", Translation::getTranslation("table_comment") . ": " . Translation::getTranslation("available_characters", ["A-z, a-z, _, 1-9"]));
        }
        if(!isset($this->request["fields"]) || empty($this->request["fields"])){
            $this->setError("fields",Translation::getTranslation("at_least_one_column"));
        }else{
            foreach($this->request["fields"] as $field){
                if(!$field["field_name"]){
                    $this->setError("fields",Translation::getTranslation("column_name_required"));
                    break;
                }
            }
        }
        return empty($this->errors);
    }

    public function submit()
    {
        if (isset($this->request["save"])) {
            try{
                $success_message = $this->table_definition->table_exist ? "change_success" : "table_create_success";
                $this->table_definition->saveDefinition();
                CoreDB::messenger()->createMessage(Translation::getTranslation($success_message), Messenger::SUCCESS);
                CoreDB::goTo(BASE_URL."/admin/table/struct/{$this->table_name}");
            }catch(Exception $ex){
                $this->setError("table_name", $ex->getMessage());
            }
        }
    }

    public function processForm()
    {
        parent::processForm();
        $this->addField(
            InputWidget::create("table_name")
                ->setValue($this->table_definition->table_name)
                ->setLabel(Translation::getTranslation("table_name"))
                ->addAttribute("autocomplete", "off")
                ->addAttribute("required", "true")
                ->setDescription(Translation::getTranslation("available_characters", ["a-z, _, 1-9"]))
        );
        if($this->table_definition->table_name){
            $this->fields["table_name"]->addAttribute("readonly", "true");
        }
        $this->addField(
            InputWidget::create("table_comment")
                ->setValue($this->table_definition->table_comment)
                ->setLabel(Translation::getTranslation("table_comment"))
                ->addAttribute("autocomplete", "off")
                ->addAttribute("required", "true")
                ->setDescription(Translation::getTranslation("available_characters", ["A-z, a-z, _, 1-9"]))
        );

        $this->addField(
            InputWidget::create("new_field")
                ->addClass("btn btn-info mt-2 newfield")
                ->setValue(Translation::getTranslation("new_field"))
                ->setType("button")
        );
        $this->addField(
            InputWidget::create("save")
                ->addClass("btn btn-primary mt-2")
                ->setValue(Translation::getTranslation("save"))
                ->setType("submit")
        );
        $this->addField(
            InputWidget::create("drop_accept")
                ->addClass("btn btn-danger mt-2")
                ->setValue(Translation::getTranslation("drop_table"))
                ->setType("button")
                ->addClass("remove_accept")
        );
        $this->addField(
            InputWidget::create("drop")
                ->addClass("btn btn-danger mt-2")
                ->setType("submit")
                ->addAttribute("hidden", "true")
        );

        foreach ($this->table_definition->fields as $column_name => $description) {
            $column_definition = new ColumnDefinition("fields[{$column_name}]", $description);
            if(in_array($column_name, ["ID", "created_at", "last_updated"])){
                $column_definition->opened = false;
            }
            $this->columns[] = $column_definition;
        }
    }
}
