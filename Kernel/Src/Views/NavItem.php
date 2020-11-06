<?php

namespace Src\Views;

use Src\Theme\View;

class NavItem extends ViewGroup
{
    public string $collapse_id = "";
    public bool $isCollapseOpened = false;

    public function __construct(
        $icon,
        $label,
        string $href = '#',
        bool $is_active = false
    ) {
        parent::__construct("div", "nav-item");
        if ($is_active) {
            $this->addClass("active");
        }
        if (is_string($icon)) {
            $iconField = ViewGroup::create("i", $icon);
        } else {
            $iconField = $icon;
        }
        $this->addField(
            ViewGroup::create("a", "nav-link " . ($is_active ? "active" : ""))
            ->addAttribute("href", $href)
            ->addField($iconField)
            ->addField(
                $label instanceof View ? $label : TextElement::create($label)->setTagName("span")
            )
        );
        $this->collapse_id = $label instanceof View ? "" : str_replace([" ", "&", "-"], "_", mb_strtolower($label));
    }

    public static function create(
        $icon,
        $label,
        string $href = '#',
        bool $is_active = false
    ): NavItem {
        return new NavItem($icon, $label, $href, $is_active);
    }

    public function addDropdownItem(NavItem $item, bool $is_active = false, bool $dropdown_header = false): NavItem
    {
        if (!$this->hasClass("dropdown")) {
            $this->addClass("dropdown no-arrow");
            $this->fields[0]->addClass("dropdown-toggle")
            ->addAttribute("data-toggle", "dropdown")
            ->addAttribute("id", $this->collapse_id);
        }
        if (!isset($this->fields[1])) {
            $this->addField(
                ViewGroup::create("div", "dropdown-menu dropdown-menu-right shadow animated--grow-in")
            );
        }
        if ($dropdown_header) {
            $item->fields[0]->removeClass("nav-link")
            ->addClass("dropdown-header")
            ->setTagName("h6");
        } else {
            $item->fields[0]->removeClass("nav-link")
            ->addClass("dropdown-item");
            if ($is_active) {
                $item->addClass("active");
            }
        }
        $this->fields[1]->addField($item);
        return $this;
    }

    public function addCollapsedItem(View $item, bool $is_active = false, bool $collase_header = false): NavItem
    {
        if (!$this->hasClass("collapsed")) {
            $this->fields[0]->addClass("collapsed")
            ->addAttribute("data-toggle", "collapse")
            ->addAttribute("data-target", "#{$this->collapse_id}")
            ->addAttribute("aria-expanded", "true")
            ->addAttribute("aria-controls", $this->collapse_id);
        }
        if (!isset($this->fields[1])) {
            $this->addField(
                ViewGroup::create("div", "collapse")
                ->addAttribute("id", $this->collapse_id)
                ->addField(
                    ViewGroup::create("div", "bg-white py-2 collapse-inner rounded")
                )
            );
        }
        if ($collase_header) {
            $item->addClass("collapse-header");
        } else {
            $item->addClass("collapse-item");
            if ($is_active) {
                $item->addClass("active");
            }
        }
        $this->fields[1]->fields[0]->addField($item);
        return $this;
    }

    public function collapseOpened()
    {
        $this->fields[1]->addClass("show");
        $this->fields[0]->removeClass("collapsed");
        $this->isCollapseOpened = true;
    }
}
