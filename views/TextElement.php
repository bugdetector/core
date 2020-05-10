<?php

class TextElement extends View
{
    public $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function render()
    { 
        echo $this->text;
    }
}
