<?php

class SelectQueryPreparer extends CoreDBQueryPreparer{
    private $tables;
    private $fields;
    private $condition;
    private $orderBy;
    private $limit, $offset;
    private $quote;
    private $distinct = "";

    const ASC = "ASC";
    const DESC = "DESC";
    
    public function __construct($table_name, $alias, bool $quote){
        $this->tables = array();
        $this->quote = $quote;
        array_push($this->tables, array(
            tableName => $table_name,
            alias => $alias
        ));
    }

    public function getQuery() {
        return "SELECT ".$this->distinct.
                $this->get_fields()." FROM ".
                $this->getTables()." ".
                $this->getCondition()." ".
                $this->getOrderBy()." ".
                $this->getLimit();
    }
    
    /**
     * 
     * @return $this
     */
    public function distinct() {
        $this->distinct = "DISTINCT ";
        return $this;
    }


    private function getTables(){
        $tables = "";
        $index = 0;
        foreach ($this->tables as $table){
            if($this->quote) {
                $table[tableName] = "`$table[tableName]`";
            }
            $tables.= ($index>0 ? ", ": "").$table[tableName].($table[alias] ? " AS ".$table[alias] : "");
            $index++;
        }
        return $tables;
    }
    
    public function join(string $table_name, string $alias = ""){
        array_push($this->tables, array(
            tableName => $table_name,
            alias => $alias
        ));
        return $this;
    }
    
    public function select($table, array $fields){
        if(!$this->fields) {
            $this->fields = array();
        }
        foreach ($fields as $field){
            array_push($this->fields, ($table ? $table."." : "").$field);
        }
        return $this;
    }
    
    public function select_with_function($table, array $functions){
        if(!$this->fields) {
            $this->fields = array();
        }
        foreach ($functions as $function){
            array_push($this->fields, $function);
        }
        return $this;
    }
    
    private function get_fields(){
        if(!$this->fields){
            return "*";
        }
        $index = 0;
        $fields = "";
        foreach ($this->fields as $field){
            $fields.= ($index>0 ? ", ": "").$field;
            $index++;
        }
        return $fields;
    }
    
    public function orderBy(string $orderBy){
        $this->orderBy = $orderBy;
        return $this;
    }
    
    private function getOrderBy(){
        return $this->orderBy ? "ORDER BY ".$this->orderBy : "";
    }
    
    public function condition(string $condition, array $params = NULL){
        $this->condition = $condition;
        if($params){
            $this->params = $params;
        }
        return $this;
    }
    
    private function getCondition(){
        return $this->condition ? "WHERE ".$this->condition: "";
    }
    
    public function limit($limit, $offset = ""){
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }
    
    private function getLimit(){
        return $this->limit ? "LIMIT ".$this->limit.($this->offset ? " OFFSET ".$this->offset : "") : "";
    }
}

