<?php
class InputField extends FieldControl{
    private $type = "text";

    public function setType(string $type) : self{
        $this->type = $type;
        return $this;
    }

    public function renderField() : string{
        return (isset($this->label) ? "<label ".(isset($this->attributes["id"]) ? "for='".$this->attributes["id"]."'" : "").">{$this->label}</label>" : "").
        "<input type='$this->type' name='$this->name' class='".$this->renderClasses()."' ".$this->renderAttributes()." value='$this->value'/>";
    }
}