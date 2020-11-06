<?php

namespace Src\Views;

use Src\Theme\ResultsViewer;

class Table extends ResultsViewer
{
    public ?string $orderBy;
    public ?string $orderDirection;
    public array $filter_params;
    public function __construct()
    {
        $this->classes = ["table", "table-bordered", "text-gray-900"];
    }

    public function setOrderable(bool $orderable)
    {
        parent::setOrderable($orderable);
        if ($this->orderable) {
            $this->filter_params = array_filter($_GET);
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
