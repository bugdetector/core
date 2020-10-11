<?php

namespace Src\Views;

class Navbar extends ViewGroup
{

    public function __construct(string $tag_name, string $wrapper_class)
    {
        parent::__construct($tag_name, $wrapper_class);
    }
    
    public static function create(string $tag_name, string $wrapper_class): Navbar
    {
        return new Navbar($tag_name, $wrapper_class);
    }

    public function addNavItem(NavItem $item)
    {
        $this->addField($item);
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "navbar.twig";
    }
}
