<?php

namespace Views;

namespace Src\Views;

use CoreDB\Kernel\SearchableInterface;
use Src\Theme\View;

class Pagination extends View
{
    public $page;
    public int $limit;
    public $total_count;
    public $query_link;

    public function __construct($page, $limit = SearchableInterface::PAGE_LIMIT)
    {
        $this->page = $page;
        $this->limit = $limit;
        $params = $_GET;
        unset($params["page"]);
        $this->query_link = BASE_URL.\CoreDB::requestUrl() . "?" . http_build_query(array_filter($params));
    }

    public function getTemplateFile(): string
    {
        return "pagination.twig";
    }

    public function getPageCount()
    {
        return intval($this->total_count / $this->limit) + 1;
    }
}
