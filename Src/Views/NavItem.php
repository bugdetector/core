<?php

namespace Src\Views;

use Src\Theme\View;

class NavItem extends ViewGroup
{
    public string $collapse_id = "";
    public array $collapsed_items = [];

    public function __construct(
        string $icon_class,
        string $label,
        string $href = '#',
        bool $is_active = false
    ) {
        parent::__construct("li", "nav-item");
        if($is_active){
            $this->addClass("active");
        }
        $this->addField(
            ViewGroup::create("a", "nav-link")
            ->addAttribute("href", $href)
            ->addField(ViewGroup::create("i", $icon_class))
            ->addField(TextElement::create($label)->setTagName("span"))
        );
        $this->collapse_id = str_replace(" ", "_", mb_strtolower($label));
    }

    public static function create(
        string $icon_class,
        string $label,
        string $href = '#',
        bool $is_active = false
    ) : NavItem {
        return new NavItem($icon_class, $label, $href, $is_active);
    }

    public function addCollapsedItem(View $item, bool $is_active = false, bool $collase_header = false) : NavItem
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
            if($is_active){
                $item->addClass("active");
            }
        }
        $this->fields[1]->fields[0]->addField($item);
        return $this;
    }
}
