<?php

namespace Src\Views;

use CoreDB;
use Src\Theme\ResultsViewer;

class Table extends ResultsViewer
{
    public ?string $orderBy;
    public ?string $orderDirection;
    public array $filter_params;
    public function __construct()
    {
        $this->classes = ["table", "table-striped", "table-hover", "position-relative", "gy-7", "gs-7"];
        CoreDB::controller()->addCssFiles("assets/css/components/table.css");
    }

    public function setOrderable(bool $orderable)
    {
        parent::setOrderable($orderable);
        if ($this->orderable) {
            $this->filter_params = array_filter($_GET, function ($el) {
                return $el !== null && $el !== "";
            });
            $this->orderBy = isset($this->filter_params["orderBy"]) ? $this->filter_params["orderBy"] : null;
            $this->orderDirection = isset($this->filter_params["orderDirection"]) ?
                                    $this->filter_params["orderDirection"] : "";
        }
        unset($this->filter_params["orderBy"], $this->filter_params["orderDirection"]);
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "table.twig";
    }
}
