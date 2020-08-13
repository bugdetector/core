<?php

namespace Src\Views;

use Src\Theme\View;

class CollapsableCard extends View
{
    public $title;
    public $content;
    public $id;
    public bool $opened = false;

    public function __construct($title)
    {
        $this->title = $title;
    }
    
    public static function create($title) : CollapsableCard
    {
        return new CollapsableCard($title);
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function setId($id)
    {
        $this->id = preg_replace("/[^a-z_0-9]+/", "", $id);
        return $this;
    }

    public function setOpened(bool $opened)
    {
        $this->opened = $opened;
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "collapsable_card.twig";
    }
}
