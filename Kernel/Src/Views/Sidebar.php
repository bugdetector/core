<?php

namespace Src\Views;

class Sidebar extends ViewGroup
{

    public function __construct(string $tag_name, string $wrapper_class)
    {
        parent::__construct($tag_name, $wrapper_class);
    }
    
    public static function create(string $tag_name, string $wrapper_class): Sidebar
    {
        return new Sidebar($tag_name, $wrapper_class);
    }

    public function addNavItem(NavItem $item)
    {
        $this->addField($item);
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "sidebar.twig";
    }

    public function setIsOpened(bool $isOpened){
        if($isOpened){
            $this->removeClass("toggled");
        }else{
            $this->addClass("toggled");
        }
    }
}
