<?php

namespace Src\Form\Widget;

use Src\JWT;
use Src\Theme\View;

class TextareaWidget extends FormWidget
{
    public ?JWT $fileKey = null;
    public bool $isNull = true;

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
        if (in_array("html-editor", explode(" ", $class_name))) {
            $controller = \CoreDB::controller();
            $controller->addJsFiles([
                "base_theme/assets/plugins/custom/tinymce/tinymce.bundle.js",
                "assets/js/components/html-editor.js"
            ]);
        }
        return parent::addClass($class_name);
    }

    public function addFileKey($entityName, $id, $fieldName, $isNull = true): TextareaWidget
    {
        $this->fileKey = new JWT();
        $this->fileKey->setPayload([
            "entity" => $entityName,
            "id" => $id,
            "field" => $fieldName
        ]);
        $this->isNull = $isNull;
        return $this;
    }
}
