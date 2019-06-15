<?php

class DeleteQueryPreparer extends CoreDBQueryPreparer{
    private $table;
    private $condition;


    public function __construct($table){
        $this->table = $table;
    }

    public function getQuery() {
        return "DELETE FROM `".$this->table."` ".
        $this->getCondition();
    }
    
    public function condition(string $condition, array $params = []){
        $this->condition = $condition;
        $this->params = $params;
        return $this;
    }
    
    private function getCondition(){
        return $this->condition ? "WHERE ".$this->condition : "";
    }

}

