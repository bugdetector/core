<?php

namespace Src\Views;

use CoreDB;
use Src\Theme\View;

class Sidebar extends View
{

    public static function create(): Sidebar
    {
        return new Sidebar();
    }

    public function getTemplateFile(): string
    {
        return "sidebar.twig";
    }

    public static function getSidebarElements($parent = null)
    {
        $sidebarClass = CoreDB::config()->getEntityClassName("sidebar");
        return $sidebarClass::getSidebarElements($parent);
    }
}
