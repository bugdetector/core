<?php

namespace Src\Form\Widget;

use Src\Entity\File;

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
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "input-widget.twig";
    }

    public function setValue(string $value)
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
}
