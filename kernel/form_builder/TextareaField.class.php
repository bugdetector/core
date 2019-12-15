<?php

class TextareaField extends FieldControl{
    public function renderField(): string {
        return (isset($this->label) ? "<label>{$this->label}</label>" : "").
        "<textarea class='".$this->renderClasses()."' name='$this->name' ".$this->renderAttributes().">$this->value</textarea>";
    }

}
