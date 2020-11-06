<?php

namespace Src\Form\Widget;

use Src\Theme\View;

class TextareaWidget extends FormWidget
{

    public static function create(string $name): TextareaWidget
    {
        return new TextareaWidget($name);
    }

    public function getTemplateFile(): string
    {
        return "textarea-widget.twig";
    }

    public function addClass(string $class_name): View
    {
        if (in_array("summernote", explode(" ", $class_name))) {
            \CoreDB::controller()->addJsFiles("dist/summernote/summernote.js");
            \CoreDB::controller()->addCssFiles("dist/summernote/summernote.css");
        }
        return parent::addClass($class_name);
    }
}
