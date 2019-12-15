<?php

class InputGroup extends FieldControl {
    private $wrapper_class;
    private $fields;
    public function __construct(string $wrapper_class) {
        $this->wrapper_class = $wrapper_class;
    }

    public function addField(FieldControl $field) : self{
        $this->fields[] = $field;
        return $this;
    }

    public function renderField() : string{
        $render = "<div class='$this->wrapper_class' ".$this->renderAttributes().">";
        foreach($this->fields as $field){
            $render.= $field->renderField();
        }
        $render .="</div>";
        return $render;
    }

}