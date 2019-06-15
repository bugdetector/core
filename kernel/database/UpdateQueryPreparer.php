<?php

class UpdateQueryPreparer extends CoreDBQueryPreparer{
    private $table;
    private $fields;
    private $condition;

    public function __construct(string $table, array $fields){
        $this->table = $table;
        $this->fields = "";
        $this->params = array();
        $field_count = count($fields);
        $index = 1;
        foreach ($fields as $key => $field){
            $this->fields .= "`$key` = :$key".($index<$field_count ? ", ": "");
            $this->params[":".$key] = filter_var($field, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
            $index++;
        }
    }


    public function getQuery() {
        return "UPDATE `".$this->table.
               "` SET ".$this->fields." ".$this->getCondition();
    }
    
    public function condition($condition, $params){
        $this->condition = $condition;
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    private function getCondition(){
        return $this->condition ? "WHERE ".$this->condition : "";
    }
}

