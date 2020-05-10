<?php

abstract class FieldControl extends View {
    protected $name;
    protected $value;
    protected $label;
    protected $append = "";

    public function __construct(string $name)
    {
        $this->setName($name);
        $this->addClass("form-control");
    }
    
    public function setName(string $name){
        $this->name = $name;
        return $this;
    }

    public function setValue(string $value){
        $this->value = $value;
        return $this;
    }

    public function setLabel(string $label){
        $this->label = $label;
        return $this;
    }

    abstract public function render();

    public static function createFromOption(array $option){
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
    
}
