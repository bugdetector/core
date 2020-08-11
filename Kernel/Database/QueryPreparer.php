<?php

namespace CoreDB\Kernel\Database;


use PDOStatement;

abstract class QueryPreparer
{
    protected $params = [];
    
    abstract public function getQuery() : string;

    public function params(array $params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    public function getParams() : array
    {
        return $this->params;
    }
    
    public function execute() : PDOStatement
    {
        return \CoreDB::database()->execute($this);
    }
}
