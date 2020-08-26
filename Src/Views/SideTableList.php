<?php

namespace Src\Views;

use Src\Theme\View;

class SideTableList extends View
{
    public $information_scheme;
    public $active_table;
    public function __construct($active_table = "")
    {
        $this->active_table = $active_table;
        $this->information_scheme = \CoreDB::database()::getTableList();
        \CoreDB::controller()->addJsFiles("src/js/side_table_list.js");
    }

    public function getTemplateFile(): string
    {
        return "side_table_list.twig";
    }
}