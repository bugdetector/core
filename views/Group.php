<?php

class Group extends View {
    private $wrapper_class;
    private $fields = [];
    private $tag_name = "div";

    public function __construct(string $wrapper_class) {
        $this->wrapper_class = $wrapper_class;
        $this->classes = [];
    }

    public function setTagName(string $tag_name){
        $this->tag_name = $tag_name;
        return $this;
    }

    public function addField(View $field, $offset = 0){
        if(!$offset){
        $this->fields[] = $field;
        }else{
            array_splice($this->fields, $offset, 1, [$field, $this->fields[$offset]]);
        }
        return $this;
    }

    public function render(){
        echo "<{$this->tag_name} class='$this->wrapper_class ".$this->renderClasses()." ' ".$this->renderAttributes().">";
        foreach($this->fields as $field){
            echo $field;
        }
        echo "</{$this->tag_name}>";
    }

}