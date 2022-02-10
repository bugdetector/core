<?php

namespace Src\Views;

use Src\Entity\Sidebar as EntitySidebar;
use Src\Theme\View;

class Sidebar extends View
{
    /** @var EntitySidebar */
    public array $items = [];

    public function __construct()
    {
        $this->items = EntitySidebar::getSidebarElements();
    }
    
    public static function create(): Sidebar
    {
        return new Sidebar();
    }

    public function getTemplateFile(): string
    {
        return "sidebar.twig";
    }

    public function setIsOpened(bool $isOpened)
    {
        if ($isOpened) {
            $this->removeClass("toggled");
        } else {
            $this->addClass("toggled");
        }
    }
}
