<?php

namespace Views;

namespace Src\Views;

use Src\Theme\View;

class Pagination extends View
{
    public $page;
    public $total_count;
    public $query_link;

    public function __construct($page)
    {
        $this->page = $page;
        $params = $_GET;
        unset($params["page"]);
        $this->query_link = \CoreDB::requestUrl() . "?" . http_build_query(array_filter($params));
    }

    public function getTemplateFile(): string
    {
        return "pagination.twig";
    }

    public function getPageCount()
    {
        return intval($this->total_count / 100) + 1;
    }
}
