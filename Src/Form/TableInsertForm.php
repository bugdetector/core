<?php

namespace Src\Form;

use CoreDB;
use CoreDB\Kernel\TableMapper;
use Exception;
use Src\Controller\Admin\TableController;
use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;

class TableInsertForm extends Form
{
    public string $method = "POST";

    protected TableMapper $object;

    public function __construct(TableMapper $object)
    {
        parent::__construct();
        $this->object = $object;
        $this->setEnctype("multipart/form-data");
        \CoreDB::controller()->addJsFiles("src/js/insert.js");

        foreach ($this->object->getFormFields($this->object->table) as $column_name => $field) {
            $this->addField($field);
        }
        $this->addField(
            InputWidget::create("save")
            ->setValue(Translation::getTranslation("save"))
            ->setType("submit")
            ->addClass("btn btn-primary mt-2")
        );
        if ($this->object->ID) {
            $this->addField(
                InputWidget::create("")
                ->setValue(Translation::getTranslation("delete"))
                ->setType("button")
                ->addClass("btn btn-danger mt-2")
                ->addClass("remove_accept")
            );
            $this->addField(
                InputWidget::create("delete")
                ->setType("submit")
                ->addClass("btn btn-danger mt-2")
                ->addAttribute("hidden", "true")
            );
        }

        \CoreDB::controller()->addJsFiles("src/js/table_search_form.js");
    }

    public function getFormId(): string
    {
        return "table_insert_form";
    }

    protected function restoreValues(){
        foreach ($this->object->toArray() as $field_name => $field) {
            if($this->fields["{$this->object->table}[{$field_name}]"] instanceof FormWidget){
                $this->fields["{$this->object->table}[{$field_name}]"]->setValue(strval($field)); 
            }
        }
    }

    public function validate() : bool
    {
        return true;
    }

    public function submit()
    {
        try{
            if (isset($this->request["save"])) {
                $success_message = $this->object->ID ? "update_success" : "insert_success";
                if (isset($this->request[$this->object->table])) {
                    $this->object->map($this->request[$this->object->table]);
                }
                $this->object->save();
                if (isset($_FILES[$this->object->table])) {
                    $this->object->includeFiles($_FILES[$this->object->table]);
                }
                $this->setMessage(Translation::getTranslation($success_message));
                $this->submitSuccess();
            } elseif (isset($this->request["delete"])) {
                $this->object->delete();
                $this->setMessage(Translation::getTranslation("record_removed"));
                $this->deleteSuccess();
            }
        }catch(Exception $ex){
            $this->setError("", $ex->getMessage());
        }
    }

    protected function submitSuccess(){
        \CoreDB::goTo(CoreDB::controller()->getUrl()."{$this->object->table}/{$this->object->ID}");
    }

    protected function deleteSuccess() :string
    {
        \CoreDB::goTo(TableController::getUrl()."{$this->object->table}");
    }
}
