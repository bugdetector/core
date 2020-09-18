<?php

namespace Src\Form\Widget;

use Src\Entity\File;
use Src\Theme\View;

class InputWidget extends FormWidget
{
    public $type = "text";

    public ?File $file;

    public static function create(string $name): InputWidget
    {
        return new InputWidget($name);
    }

    public function setType(string $type)
    {
        $this->type = $type;
        if($type == "checkbox"){
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
        if($this->type == "checkbox"){
            if($this->value){
                $this->addAttribute("checked", "true");
            }else{
                $this->removeAttribute("checked");
            }
        }else if($this->type == "file"){
            $this->file = File::get(["ID" => $value]);
        }
        return $this;
    }

    public function addClass(string $class_name): View
    {
        $classesToAdd = explode(" ", $class_name);
        if(in_array("dateinput", $classesToAdd) || in_array("datetimeinput", $classesToAdd)){
            \CoreDB::controller()->addJsFiles("dist/datetimepicker/datetimepicker.js");
            \CoreDB::controller()->addCssFiles("dist/datetimepicker/datetimepicker.css");
        }else if(in_array("daterangeinput", $classesToAdd)){
            \CoreDB::controller()->addJsFiles("dist/daterangepicker/daterangepicker.js");
            \CoreDB::controller()->addCssFiles("dist/daterangepicker/daterangepicker.css",);
        }
        return parent::addClass($class_name);
    }
}
