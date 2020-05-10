<?php

abstract class View{
    protected $classes = [];
    protected $attributes = [];

    abstract function render();

    public static function create($value){
        $class_name = get_called_class();
        return new $class_name($value);
    }

    public function addClass(string $class_name){
        $this->classes[] = $class_name;
        return $this;
    }

    public function removeClass(string $class_name){
        unset($this->classes[array_search($class_name, $this->classes)]);
        return $this;
    }
    
    public function addAttribute(string $attribute_name, string $attribute_value){
        $this->attributes[$attribute_name] = $attribute_value;
        return $this;
    }

    public function removeAttribute(string $attribute_name){
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

    public function __toString()
    {
        return $this->render() ? : "";
    }

}