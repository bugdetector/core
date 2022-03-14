<?php

namespace Src\Form;

use CoreDB\Kernel\Model;
use Exception;
use Src\Controller\Admin\TableController;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\Views\ViewGroup;

class InsertForm extends Form
{
    public string $method = "POST";
    public string $formName;

    protected Model $object;

    public function __construct(Model $object)
    {
        parent::__construct();
        $this->object = $object;
        $this->formName = $this->object->entityName ?: $this->object->getTableName();
        foreach (
            $this->object->getFormFields(
                $this->formName,
                !($object instanceof DynamicModel)
            ) as $column_name => $field
        ) {
            $this->addField($field);
        }
        $submitSection = ViewGroup::create("div", "d-flex position-fixed bottom-0 end-0 mb-5 me-20");
        $submitSection->addField(
            InputWidget::create("save")
            ->setValue(Translation::getTranslation("save"))
            ->setType("submit")
            ->addClass("btn btn-primary btn-sm mt-2 me-2")
            ->removeClass("form-control")
        );
        if ($this->object->ID->getValue()) {
            $submitSection->addField(
                InputWidget::create("")
                ->setValue(Translation::getTranslation("delete"))
                ->setType("button")
                ->addClass("btn btn-danger btn-sm mt-2 me-2")
                ->addClass("remove_accept")
                ->removeClass("form-control")
            );
            $submitSection->addField(
                InputWidget::create("delete")
                ->setType("submit")
                ->addClass("btn btn-danger btn-sm mt-2")
                ->addAttribute("hidden", "true")
            );
        }
        $this->addField($submitSection);
        $controller = \CoreDB::controller();
        $controller->addJsFiles("assets/js/forms/insert_form.js");
        $controller->addFrontendTranslation("record_remove_accept");
        $controller->addFrontendTranslation("record_remove_accept_field");
    }

    public function getFormId(): string
    {
        return "table_insert_form";
    }

    protected function restoreValues()
    {
        foreach ($this->object->toArray() as $field_name => $field) {
            $key = (
                $this->object instanceof DynamicModel ?
                $this->object->getTableName() : $this->object->entityName
            ) . "[{$field_name}]";
            if (isset($this->fields[$key]) && $this->fields[$key] instanceof FormWidget) {
                $this->fields[$key]->setValue(strval($field));
            }
        }
    }

    public function validate(): bool
    {
        if (isset($this->request[$this->formName])) {
            $this->object->map($this->request[$this->formName]);
        }
        return true;
    }

    public function submit()
    {
        try {
            if (isset($this->request["save"])) {
                $success_message = $this->object->ID->getValue() ? "update_success" : "insert_success";
                $this->object->save();
                $this->setMessage(Translation::getTranslation($success_message));
                $this->submitSuccess();
            } elseif (isset($this->request["delete"])) {
                $this->object->delete();
                $this->setMessage(Translation::getTranslation("record_removed"));
                $this->deleteSuccess();
            }
        } catch (Exception $ex) {
            $this->setError("", $ex->getMessage());
            $this->restoreValues();
        }
    }

    protected function submitSuccess()
    {
        \CoreDB::goTo($this->object->editUrl());
    }

    protected function deleteSuccess(): string
    {
        \CoreDB::goTo(TableController::getUrl() . $this->object->getTableName());
    }
}
