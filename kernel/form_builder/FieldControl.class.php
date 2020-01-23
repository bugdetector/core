<?php

abstract class FieldControl {
    protected $name;
    protected $value;
    protected $classes = ["form-control"];
    protected $attributes = [];
    protected $label;

    public function __construct(string $name)
    {
        $this->setName($name);
    }

    public function setName(string $name) : self{
        $this->name = $name;
        return $this;
    }

    public function setValue(string $value) : self{
        $this->value = $value;
        return $this;
    }

    public function addClass(string $class_name) : self{
        $this->classes[] = $class_name;
        return $this;
    }

    public function removeClass(string $class_name) : self{
        unset($this->classes[array_search($class_name, $this->classes)]);
        return $this;
    }
    
    public function addAttribute(string $attribute_name, string $attribute_value) : self{
        $this->attributes[$attribute_name] = $attribute_value;
        return $this;
    }

    public function removeAttribute(string $attribute_name) : self{
        unset($this->attributes[$attribute_name]);
        return $this;
    }

    public function renderClasses(){
        return implode(" ", $this->classes);
    }
    
    public function renderAttributes(){
        $render = "";
        foreach ($this->attributes as $name => $value){
            $render.= " $name='$value' ";
        }
        return $render;
    }

    public function setLabel(string $label) : self{
        $this->label = $label;
        return $this;
    }

    abstract public function renderField() : string;

    public static function createFromOption(array $option) : self{
        if($option["type"] == "select"){
            $input = new SelectField($option["name"]);
            $input->setOptions($option["options"]);
            $input->setLabel($option["label"]);
        }else{
            $input = new InputField($option["name"]);
            $input->setType($option["type"]);
            if($option["type"] == "checkbox"){
                $input->removeClass("form-control");
            }
        }
        isset($option["label"]) ? $input->setLabel($option["label"]) : "";
        isset($option["value"]) ? $input->setValue($option["value"]) : "";
        isset($option["class"]) ? $input->addClass($option["class"]) : "";
        if(isset($option["attributes"])){
            foreach($option["attributes"] as $name => $value){
                $input->addAttribute($name, $value);
            }
        }
        return $input;
    }
}
