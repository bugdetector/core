<?php

namespace Src\Views;

use Src\Theme\View;

class ViewGroup extends View
{
    public $fields = [];
    public $tag_name;
    private bool $addClassToChildren = false;

    public function __construct(string $tag_name, string $wrapper_class)
    {
        $this->tag_name = $tag_name;
        $this->addClass($wrapper_class);
    }

    public static function create(string $tag_name, string $wrapper_class): ViewGroup
    {
        return new ViewGroup($tag_name, $wrapper_class);
    }

    public function getTemplateFile(): string
    {
        return "view_group.twig";
    }

    public function setTagName(string $tag_name): ViewGroup
    {
        $this->tag_name = $tag_name;
        return $this;
    }

    public function addClassToChildren(bool $addClassToChildren): ViewGroup
    {
        $this->addClassToChildren = $addClassToChildren;
        return $this;
    }

    public function addClass(string $class_name): ViewGroup
    {
        if (!$this->addClassToChildren) {
            parent::addClass($class_name);
        } else {
            foreach ($this->fields as &$field) {
                $field->addClass($class_name);
            }
        }
        return $this;
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
