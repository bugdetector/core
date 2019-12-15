<?php

class InsertQueryPreparer extends CoreDBQueryPreparer{
    private $table;
    private $fields;
    public function __construct($table, array $fields){
        $this->table = $table;
        $this->fields = $fields;
    }

    public function getQuery() : string {
        return "INSERT INTO `".
                $this->table."` ".
                $this->getBackQuery();
    }
    
    private function getBackQuery(){
        $fields = "( ";
        $values = "VALUES (";
        $this->params = [];
        $param_count = count($this->fields);
        $index = 0;
        foreach ($this->fields as $key => $field){
            if($field === "NULL"){
                $field = null;
            }
            $fields.= ($index>0 ? ", `" : "`").$key."`";
            $values.= ($index>0 ? ", " : "")." ? ";
            $this->params[] = $field !== null ?  filter_var($field, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE) : null;
            $index++;
        }
        
        return $fields.") ".$values.")";
    }

}

