<?php

namespace Src\Views;

class NavPills extends ViewGroup
{
    public function __construct()
    {
        parent::__construct("div", "nav nav-pills");
    }

    public function addNavItem($label, $href = '#', $is_active = false)
    {
        $this->addField(
            ViewGroup::create("div", "nav-item")->addField(
                ViewGroup::create("a", "nav-link" . ($is_active ? " active" : ""))
                ->addAttribute("href", $href)
                ->addField(TextElement::create($label))
            )
        );
        return $this;
    }
}
