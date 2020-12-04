<?php

namespace Src\Form\Widget;

use Src\Entity\File;
use Src\JWT;
use Src\Theme\View;

class InputWidget extends FormWidget
{
    public $type = "text";

    public ?File $file;

    public ?JWT $removeKey = null;

    public $fileClass = "img-thumbnail";

    public static function create(string $name): InputWidget
    {
        return new InputWidget($name);
    }

    public function setType(string $type)
    {
        $this->type = $type;
        if ($type == "checkbox") {
            \CoreDB::controller()->addJsFiles("dist/checkbox/checkbox.js");
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
        if (in_array("dateinput", $classesToAdd) || in_array("datetimeinput", $classesToAdd)) {
            \CoreDB::controller()->addJsFiles("dist/datetimepicker/datetimepicker.js");
            \CoreDB::controller()->addCssFiles("dist/datetimepicker/datetimepicker.css");
        } elseif (in_array("daterangeinput", $classesToAdd)) {
            \CoreDB::controller()->addJsFiles("dist/daterangepicker/daterangepicker.js");
            \CoreDB::controller()->addCssFiles("dist/daterangepicker/daterangepicker.css");
        }
        return parent::addClass($class_name);
    }

    public function addFileRemoveKey($entityName, $id, $fieldName)
    {
        $removeKeyJwt = new JWT();
        $removeKeyJwt->setPayload([
            "entity" => $entityName,
            "id" => $id,
            "field" => $fieldName
        ]);
        $this->removeKey = $removeKeyJwt;
    }
}
