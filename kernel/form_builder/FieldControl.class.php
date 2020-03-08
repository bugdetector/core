<?php

abstract class FieldControl {
    protected $name;
    protected $value;
    protected $classes = ["form-control"];
    protected $attributes = [];
    protected $label;
    protected $append = "";

    public function __construct(string $name)
    {
        $this->setName($name);
    }

    public static function create($name) : FieldControl{
        $class_name = get_called_class();
        return new $class_name($name);
    }
    
    public function setName(string $name) : FieldControl{
        $this->name = $name;
        return $this;
    }

    public function setValue(string $value) : FieldControl{
        $this->value = $value;
        return $this;
    }

    public function addClass(string $class_name) : FieldControl{
        $this->classes[] = $class_name;
        return $this;
    }

    public function removeClass(string $class_name) : FieldControl{
        unset($this->classes[array_search($class_name, $this->classes)]);
        return $this;
    }
    
    public function addAttribute(string $attribute_name, string $attribute_value) : FieldControl{
        $this->attributes[$attribute_name] = $attribute_value;
        return $this;
    }

    public function removeAttribute(string $attribute_name) : FieldControl{
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

    public function setLabel(string $label) : FieldControl{
        $this->label = $label;
        return $this;
    }

    abstract public function renderField() : string;

    public static function createFromOption(array $option) : FieldControl{
        if($option["type"] == "select"){
            $input = new SelectField($option["name"]);
            $input->setOptions($option["options"]);
            if(isset($option["null_element"])){
                $input->setNullElement($option["null_element"]);
            }
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

    public function append(string $append) : self{
        $this->append = $append;
        return $this;
    }

    public function __toString()
    {
        return $this->renderField();
    }
}
