<?php

define("tableName", "tableName");
define("alias", "alias");



abstract class CoreDBQueryPreparer {
    protected $params;
    
    abstract public function getQuery() : string;

    public function params(array $params){
        $this->params = $params;
        return $this;
    }
    
    public function getParams() : array{
        return $this->params ? $this->params : array();
    }
    
    public function execute() : PDOStatement{
        return CoreDB::getInstance()->execute($this);
    }
}

