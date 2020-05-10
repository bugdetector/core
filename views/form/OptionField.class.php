<?php

class OptionField extends FieldControl{
    private $selected = false;
    
    public function __construct($value, $label = '')
    {
        $this->value = $value;
        $this->label = $label;
    }

    public function setSelected(bool $selected){
        $this->selected = $selected;
        return $this;
    }

    public function render(): string
    {
        return 
        "<option ".$this->renderAttributes()." 
            value='{$this->value}' ".($this->selected ? "selected" : "").">{$this->label}
        </option>";
    }
}