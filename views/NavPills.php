<?php

class NavPills extends Group{

    public function __construct()
    {
        parent::__construct("nav nav-pills");
    }

    public function addNavItem($label, $href = '#', $is_active = false){
        $this->addField(
            Group::create("nav-item")->addField(
                Group::create("nav-link".($is_active ? " active": ""))
                ->setTagname("a")->addAttribute("href", $href)
                ->addField(TextElement::create($label))
            )
        );
        return $this;
    }
}