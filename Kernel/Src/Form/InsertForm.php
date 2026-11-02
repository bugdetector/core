<?php

namespace Src\Form;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\TranslatableInterface;
use Exception;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Entity\TreeEntityAbstract;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\TextareaWidget;
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
        )->addAttribute("style", "z-index: 2;");
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
        if ($this->object instanceof TranslatableInterface) {
            $this->setupTranslatableFields();
            $controller = \CoreDB::controller();
            $controller->addCssFiles('assets/css/components/translatable_form.css');
            $controller->addJsFiles('assets/js/forms/translatable_form.js');
        }
        $controller = \CoreDB::controller();
        $controller->addJsFiles("assets/js/forms/insert_form.js");
        $controller->addFrontendTranslation("record_remove_accept");
        $controller->addFrontendTranslation("record_remove_accept_field");
    }

    public function getFormId(): string
    {
        return "table_insert_form";
    }

    public function getTemplateFile(): string
    {
        return "insert-form.twig";
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
        if (
            $this->object instanceof TranslatableInterface &&
            isset($this->request['translations']) &&
            is_array($this->request['translations'])
        ) {
            foreach ($this->request['translations'] as $lang => $fields) {
                if (!is_array($fields)) {
                    continue;
                }
                foreach ($fields as $field => $val) {
                    $key = "translations[{$lang}][{$field}]";
                    if (isset($this->fields[$key]) && $this->fields[$key] instanceof FormWidget) {
                        $this->fields[$key]->setValue((string) $val);
                    }
                }
            }
        }
    }

    public function validate(): bool
    {
        if (isset($this->request[$this->formName])) {
            if (
                $this->object instanceof TreeEntityAbstract &&
                property_exists($this->object, "parent") &&
                isset($_GET["parent"])
            ) {
                $this->request[$this->formName]["parent"] = $_GET["parent"];
            }
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
        \CoreDB::goTo(BASE_URL . \CoreDB::requestUrl() . "/..");
    }

    public function isTranslatable(): bool
    {
        return $this->object instanceof TranslatableInterface;
    }

    protected function setupTranslatableFields(): void
    {
        $translatableFields = $this->object::getTranslatableFields();
        if (empty($translatableFields)) {
            return;
        }

        $defaultLang = Translation::getDefaultLanguage();
        $formName = $this->formName;
        $reorderedFields = [];

        foreach ($this->fields as $key => $field) {
            $reorderedFields[$key] = $field;

            $matchedField = null;
            foreach ($translatableFields as $transField) {
                if ($key === "{$formName}[{$transField}]" || $key === $transField) {
                    $matchedField = $transField;
                    break;
                }
            }

            if ($matchedField !== null && $field instanceof FormWidget) {
                $field->addAttribute('data-trans-lang', $defaultLang);

                foreach (Translation::getAvailableLanguageList() as $lang) {
                    if ($lang === $defaultLang) {
                        continue;
                    }
                    $inputName = "translations[{$lang}][{$matchedField}]";
                    $val = $this->object->getTranslatedField($matchedField, $lang, false);

                    if ($field instanceof TextareaWidget) {
                        $subWidget = TextareaWidget::create($inputName)
                            ->setValue($val)
                            ->setLabel($field->label);
                        if ($field->hasClass('html-editor')) {
                            $subWidget->addClass('html-editor');
                        }
                        if ($field->fileKey) {
                            $subWidget->fileKey = $field->fileKey;
                        }
                    } else {
                        $subWidget = InputWidget::create($inputName)
                            ->setType('text')
                            ->setValue($val)
                            ->setLabel($field->label);
                    }

                    if ($field->description) {
                        $subWidget->setDescription($field->description);
                    }

                    $subWidget->addAttribute('data-trans-lang', $lang);

                    if (Translation::isRtl($lang)) {
                        $subWidget->addAttribute('dir', 'rtl');
                    }

                    $reorderedFields[$inputName] = $subWidget;
                }
            }
        }

        $this->fields = $reorderedFields;
    }
}
