<?php

namespace Src\Form;

use CoreDB\Kernel\EntityReference;
use CoreDB\Kernel\TableMapper;
use Exception;
use Src\Controller\Admin\TableController;
use Src\Entity\DBObject;
use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;

class InsertForm extends Form
{
    public string $method = "POST";

    protected TableMapper $object;

    public function __construct(TableMapper $object)
    {
        parent::__construct();
        $this->object = $object;
        $this->setEnctype("multipart/form-data");
        
        foreach (
            $this->object->getFormFields(
                $this->object->getTableName(),
                !($object instanceof DBObject)
            ) as $column_name => $field
        ) {
            $this->addField($field);
        }
        $this->addField(
            InputWidget::create("save")
            ->setValue(Translation::getTranslation("save"))
            ->setType("submit")
            ->addClass("btn btn-primary mt-2")
        );
        if ($this->object->ID->getValue()) {
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
        \CoreDB::controller()->addJsFiles("dist/insert_form/insert_form.js");
    }

    public function getFormId(): string
    {
        return "table_insert_form";
    }

    protected function restoreValues()
    {
        foreach ($this->object->toArray() as $field_name => $field) {
            $key = $this->object->getTableName() . "[{$field_name}]";
            if (isset($this->fields[$key]) && $this->fields[$key] instanceof FormWidget) {
                $this->fields[$key]->setValue(strval($field));
            }
        }
    }

    public function validate(): bool
    {
        return true;
    }

    public function submit()
    {
        try {
            if (isset($this->request["save"])) {
                $success_message = $this->object->ID->getValue() ? "update_success" : "insert_success";
                if (isset($this->request[$this->object->getTableName()])) {
                    $this->object->map($this->request[$this->object->getTableName()]);
                }
                $this->object->save();
                foreach ($this->object as $fieldName => $field) {
                    if (
                        ($field instanceof EntityReference) &&
                        $field->connectionType == EntityReference::CONNECTION_ONE_TO_ONE &&
                        isset($this->request[$field->fieldEntityName])
                    ) {
                        $referenceClass = \CoreDB::config()->getEntityInfo($field->fieldEntityName)["class"];
                        $object = $referenceClass::get([
                            $field->foreignKey => $this->object->ID
                        ]) ?: new $referenceClass();
                        $referenceClass->{$field->foreignKey} = $this->object->ID;
                        /** @var EntityReference $field */
                        $object->map($this->request[$field->fieldEntityName]);
                        $object->save();
                    }
                }
                if (isset($_FILES[$this->object->getTableName()])) {
                    $this->object->includeFiles($_FILES[$this->object->getTableName()]);
                }
                $this->setMessage(Translation::getTranslation($success_message));
                $this->submitSuccess();
            } elseif (isset($this->request["delete"])) {
                $this->object->delete();
                $this->setMessage(Translation::getTranslation("record_removed"));
                $this->deleteSuccess();
            }
        } catch (Exception $ex) {
            $this->setError("", $ex->getMessage());
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
