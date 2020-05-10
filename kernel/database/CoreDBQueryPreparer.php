<?php

define("tableName", "tableName");
define("alias", "alias");



abstract class CoreDBQueryPreparer {
    protected $params = [];
    
    abstract public function getQuery() : string;

    public function params(array $params){
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    public function getParams() : array{
        return $this->params;
    }
    
    public function execute() : PDOStatement{
        return CoreDB::getInstance()->execute($this);
    }
}

