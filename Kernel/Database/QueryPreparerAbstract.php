<?php

namespace CoreDB\Kernel\Database;

use PDOStatement;

abstract class QueryPreparerAbstract
{
    protected string $table;
    protected $params = [];
    
    abstract public function getQuery(): string;

    public function params(array $params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Adds parameter and returns available placeholder.
     * @return string
     *   Placeholder name
     */
    public function addParameter($column, $value): string
    {
        $placeholder = str_replace(".", "_", $column);
        $index = 0;
        while (isset($this->params["$placeholder"])) {
            $placeholder = "{$column}_{$index}";
            $index++;
        }
        $this->params[$placeholder] = $value;
        return $placeholder;
    }
    
    public function execute(): PDOStatement
    {
        return \CoreDB::database()->execute($this);
    }
}
