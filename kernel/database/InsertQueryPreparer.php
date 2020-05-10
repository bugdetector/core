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
        $index = 0;
        foreach ($this->fields as $key => $field){
            if($field === "NULL" || !$field){
                $field = null;
            }else{
                $field = Utils::cleanXSS($field);
            }
            $fields.= ($index>0 ? ", `" : "`").$key."`";
            $values.= ($index>0 ? ", " : "")." ? ";
            $this->params[] = $field;
            $index++;
        }
        
        return $fields.") ".$values.")";
    }

}

