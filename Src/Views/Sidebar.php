<?php

namespace Src\Views;

class Sidebar extends ViewGroup
{
    public array $items = [];

    public function __construct(string $tag_name, string $wrapper_class)
    {
        parent::__construct($tag_name, $wrapper_class);
        $this->addAttribute("id", "accordionSidebar");
    }
    
    public static function create(string $tag_name, string $wrapper_class) : Sidebar
    {
        return new Sidebar($tag_name, $wrapper_class);
    }

    public function addNavItem(NavItem $item)
    {
        $this->addField($item);
        return $this;
    }

    public function getTemplateFile() : string
    {
        return "sidebar.twig";
    }
}
