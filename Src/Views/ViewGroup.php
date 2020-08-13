<?php

namespace Src\Views;

use Src\Theme\View;

class ViewGroup extends View
{
    public $fields = [];
    public $tag_name;

    public function __construct(string $tag_name, string $wrapper_class)
    {
        $this->tag_name = $tag_name;
        $this->addClass($wrapper_class);
    }

    public static function create(string $tag_name, string $wrapper_class) : ViewGroup
    {
        return new ViewGroup($tag_name, $wrapper_class);
    }

    public function getTemplateFile(): string
    {
        return "view_group.twig";
    }

    public function addField(View $field, $offset = 0)
    {
        if (!$offset) {
            $this->fields[] = $field;
        } else {
            array_splice($this->fields, $offset, 1, [$field, $this->fields[$offset]]);
        }
        return $this;
    }
}
