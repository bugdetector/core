<?php

namespace Src\Views;

use Src\Theme\View;

class SideEntityList extends View
{
    public $entityList;
    public $activeEntity;
    public function __construct($activeEntity = "")
    {
        $this->activeEntity = $activeEntity;
        $this->entityList = \CoreDB::config()->getEntityList();
        \CoreDB::controller()->addJsFiles("assets/js/views/side_entity_list.js");
    }

    public function getTemplateFile(): string
    {
        return "side_entity_list.twig";
    }
}
