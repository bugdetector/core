<?php

namespace Src\Theme\Views;

use Src\Theme\View;

class Table extends View
{
    public $table_name;
    public $table_data;
    public $table_headers;
    public $orderable = false;
    public ?string $orderBy;
    public ?string $orderDirection;
    public array $filter_params;
    public function __construct(array $table_headers, array $table_data)
    {
        $this->table_data = $table_data;
        $this->table_headers = $table_headers;
        $this->classes = ["table", "table-bordered", "text-gray-900"];
    }

    public function setOrderable(bool $orderable)
    {
        $this->orderable = $orderable;
        if ($this->orderable) {
            $this->filter_params = array_filter($_GET);
            $this->orderBy = isset($this->filter_params["orderBy"]) ? $this->filter_params["orderBy"] : null;
            $this->orderDirection = isset($this->filter_params["orderDirection"]) ? $this->filter_params["orderDirection"] : "";
        }
        unset($this->filter_params["orderBy"], $this->filter_params["orderDirection"]);
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "table.twig";
    }
}
