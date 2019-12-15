<?php

class UpdateQueryPreparer extends CoreDBQueryPreparer{
    private $table;
    private $fields;
    private $condition;

    public function __construct(string $table, array $fields){
        $this->table = $table;
        $this->fields = "";
        $this->params = array();
        $index = 0;
        foreach ($fields as $key => $field){
            if($field === "NULL"){
                $field = null;
            }
            $this->fields .= ($index>0 ? ", ": "")." `$key` = :$key";
            $this->params[":".$key] = $field !== null ? filter_var($field, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE) : null;
            $index++;
        }
    }


    public function getQuery() : string {
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

