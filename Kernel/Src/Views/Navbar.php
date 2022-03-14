<?php

namespace Src\Views;

use CoreDB;
use Src\Entity\Translation;
use Src\Theme\View;

class Navbar extends View
{
    public array $fields = [];

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

    public function getNavbarElements($parent = null)
    {
        $navbarClass = CoreDB::config()->getEntityClassName("navbar");
        return $navbarClass::getNavbarElements($parent);
    }
}
