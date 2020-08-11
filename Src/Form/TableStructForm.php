<?php

namespace Src\Form;

use CoreDB\Kernel\Database\AlterQueryPreparer;
use CoreDB\Kernel\Database\CoreDB;
use CoreDB\Kernel\Database\CreateQueryPreparer;
use CoreDB\Kernel\Messenger;

use Exception;
use Src\Entity\Cache;
use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;
use Src\Theme\Views\ColumnDefinition;

class TableStructForm extends Form
{
    public string $method = "POST";

    public string $table_name;
    public string $table_comment;
    public array $columns = [];

    public function __construct(string $table_name, string $table_comment)
    {
        parent::__construct();
        $this->table_name = $table_name;
        $this->table_comment = $table_comment;
        \CoreDB::controller()->addJsFiles("src/js/table_struct.js");
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
            if ($this->table_name) {
                $fields = isset($this->request["fields"]) ? $this->request["fields"] : [];
                $db = \CoreDB::database()::getInstance();
                try {
                    $db->beginTransaction();
                    
                    $db->query("ALTER TABLE `{$this->table_name}` COMMENT = '{$this->request["table_comment"]}';");
                    foreach ($fields as $field) {
                        (new AlterQueryPreparer($this->table_name))->addField($field)->execute();
                    }
                    Cache::clear();
                    $db->commit();
                    \CoreDB::messenger()->createMessage(Translation::getTranslation("change_success"), Messenger::SUCCESS);
                } catch (Exception $ex) {
                    \CoreDB::messenger()->createMessage($ex->getMessage());
                }
                \CoreDB::goTo(BASE_URL . "/admin/table/struct/{$this->table_name}");
            } else {
                $table_name =  preg_replace("/[^a-z1-9_]+/", "", $this->request["table_name"]);
                $table_comment = htmlspecialchars($this->request["table_comment"]);
                if (in_array($table_name, \CoreDB::database()::getTableList())) {
                    \CoreDB::messenger(Translation::getTranslation("table_exits"));
                } else {
                    try {
                        $fields = $this->request["fields"];
                        (new CreateQueryPreparer($table_name))->setFields($fields)->setComment($table_comment)->execute();
                        Cache::clear();
                    } catch (Exception $ex) {
                        \CoreDB::messenger()->createMessage($ex->getMessage());
                    }
                    \CoreDB::messenger()->createMessage(Translation::getTranslation("table_create_success"), Messenger::SUCCESS);
                    \CoreDB::goTo(BASE_URL . "/admin/table/struct/$table_name");
                }
            }
        }
    }

    public function processForm()
    {
        $this->addField(
            InputWidget::create("table_name")
                ->setValue($this->table_name)
                ->setLabel(Translation::getTranslation("table_name"))
                ->addAttribute("autocomplete", "off")
                ->addAttribute("required", "true")
                ->setDescription(Translation::getTranslation("available_characters", ["a-z, _, 1-9"]))
        );
        if($this->table_name){
            $this->fields["table_name"]->addAttribute("disabled", "true");
        }
        $this->addField(
            InputWidget::create("table_comment")
                ->setValue($this->table_comment)
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

        parent::processForm();

        if ($this->table_name) {
            foreach (\CoreDB::database()::getTableDescription($this->table_name) as $index => $description) {
                $this->columns[] = new ColumnDefinition("fields[{$index}]", $this->table_name ,$description);
            }
        }
    }
}
