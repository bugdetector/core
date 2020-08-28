<?php

namespace CoreDB\Kernel\Database;

abstract class UpdateQueryPreparerAbstract extends QueryPreparerAbstract
{
    protected array $fields = [];

    public function __construct(string $table, array $fields)
    {
        $this->table = $table;
        $this->fields = $fields;
    }
    
    public function condition($condition, $params)
    {
        $this->condition = $condition;
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    abstract public function getCondition() : string;
}
