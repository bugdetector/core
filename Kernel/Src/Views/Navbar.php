<?php

namespace Src\Views;

use Src\Entity\Navbar as EntityNavbar;
use Src\Entity\Translation;
use Src\Theme\View;

class Navbar extends View
{
    /** @var EntityNavbar */
    public array $items = [];
    public array $fields = [];

    public function __construct()
    {
        $this->items = EntityNavbar::getNavbarElements();
    }

    public static function create(): Navbar
    {
        return new static();
    }

    public function addItem(View $item)
    {
        $this->fields[] = $item;
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "navbar.twig";
    }

    public function getTranslationIcons()
    {
        $translateIcons = Translation::get(["key" => "language_icon"]);
        $iconMap = [];
        foreach (Translation::getAvailableLanguageList() as $language) {
            $iconMap[$language] = $translateIcons->$language->getValue();
        }
        return $iconMap;
    }
}
