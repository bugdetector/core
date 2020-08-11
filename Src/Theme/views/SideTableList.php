<?php

namespace Src\Theme\Views;

use CoreDB\Kernel\Database\CoreDB;

use Src\Theme\View;

class SideTableList extends View
{
    public $information_scheme;
    public $active_table;
    public function __construct($active_table = "")
    {
        $this->active_table = $active_table;
        $this->information_scheme = \CoreDB::database()::getTableList();
    }

    public function getTemplateFile(): string
    {
        return "side_table_list.twig";
    }
}
