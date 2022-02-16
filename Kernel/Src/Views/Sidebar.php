<?php

namespace Src\Views;

use Src\Entity\Sidebar as EntitySidebar;
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
        return EntitySidebar::getSidebarElements($parent);
    }
}
