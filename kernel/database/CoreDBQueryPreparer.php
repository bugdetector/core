<?php

define("tableName", "tableName");
define("alias", "alias");



abstract class CoreDBQueryPreparer {
    protected $params;

  

    /**
     * 
     * @return type
     */
    
    abstract public function getQuery() : string;

    public function params(array $params){
        $this->params = $params;
        return $this;
    }
    
    public function getParams(){
        return $this->params ? $this->params : array();
    }
    
    public function execute(){
        return CoreDB::getInstance()->execute($this);
    }
}

