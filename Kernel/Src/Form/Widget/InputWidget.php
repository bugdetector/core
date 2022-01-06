<?php

namespace Src\Form\Widget;

use Src\Entity\File;
use Src\JWT;
use Src\Theme\View;

class InputWidget extends FormWidget
{
    public $type = "text";

    public ?File $file;

    public ?JWT $fileKey = null;

    public $fileClass = "img-thumbnail";

    public bool $isNull = true;

    public static function create(string $name): InputWidget
    {
        return new InputWidget($name);
    }

    public function setType(string $type)
    {
        $this->type = $type;
        if ($type == "checkbox") {
            \CoreDB::controller()->addJsFiles("dist/checkbox/checkbox.js");
        } elseif ($type == "file") {
            \CoreDB::controller()->addJsFiles("dist/file_input/file_input.js");
            \CoreDB::controller()->addCssFiles("dist/file_input/file_input.css");
            \CoreDB::controller()->addFrontendTranslation("close");
        }
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "input-widget.twig";
    }

    public function setValue($value)
    {
        $this->value = $value;
        if ($this->type == "checkbox") {
            if ($this->value) {
                $this->addAttribute("checked", "true");
            } else {
                $this->removeAttribute("checked");
            }
        } elseif ($this->type == "file") {
            $this->file = File::get($value);
        }
        return $this;
    }

    public function addClass(string $class_name): View
    {
        $classesToAdd = explode(" ", $class_name);
        if (
            !empty(array_merge(
                ["dateinput", "datetimeinput", "timeinput"],
                $classesToAdd
            ))
        ) {
            \CoreDB::controller()->addJsFiles("dist/datetimepicker/datetimepicker.js");
            \CoreDB::controller()->addCssFiles("dist/datetimepicker/datetimepicker.css");
        } elseif (in_array("daterangeinput", $classesToAdd)) {
            \CoreDB::controller()->addJsFiles("dist/daterangepicker/daterangepicker.js");
            \CoreDB::controller()->addCssFiles("dist/daterangepicker/daterangepicker.css");
        }
        return parent::addClass($class_name);
    }

    public function addFileKey($entityName, $id, $fieldName, $isNull = true): InputWidget
    {
        $removeKeyJwt = new JWT();
        $removeKeyJwt->setPayload([
            "entity" => $entityName,
            "id" => $id,
            "field" => $fieldName
        ]);
        $this->fileKey = $removeKeyJwt;
        $this->isNull = $isNull;
        return $this;
    }

    public function render()
    {
        if ($this->value) {
            if ($this->hasClass("dateinput")) {
                $this->value = date("d-m-Y", strtotime($this->value));
            } elseif ($this->hasClass("datetimeinput")) {
                $this->value = date("d-m-Y H:i", strtotime($this->value));
            }
        }
        return parent::render();
    }

    public function renderAttributes()
    {
        if ($this->type == "file" && $this->value) {
            $this->removeAttribute("required");
        }
        return parent::renderAttributes();
    }
}
