<?php

class SelectQueryPreparer extends CoreDBQueryPreparer{
    private $tables;
    private $fields;
    private $condition;
    private $orderBy;
    private $groupBy;
    private $limit, $offset;
    private $quote;
    private $distinct = "";
    private $having;

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

    public function getQuery() : string {
        return "SELECT ".$this->distinct.
                $this->get_fields()." FROM ".
                $this->getTables()." ".
                $this->getCondition()." ".
                $this->getGroupBy()." ".
                $this->getHaving()." ".
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
            $tables.= (isset($table["join"]) ? $table["join"]." JOIN " : " ").
            $table[tableName].($table[alias] ? " AS ".$table[alias] : " ")
            .(isset($table["on"]) && $table["on"] ? " ON ".$table["on"]." " : " ");
            $index++;
        }
        return $tables;
    }
    
    public function join(string $table_name, string $alias = "", string $on = "" ,string $join = "INNER"){
        array_push($this->tables, array(
            tableName => $table_name,
            alias => $alias,
            "join" => $join,
            "on" => $on
        ));
        return $this;
    }

    public function leftjoin(string $table_name, string $alias = "", string $on = "" ){
        $this->join($table_name, $alias, $on, "LEFT");
        return $this;
    }

    public function rightjoin(string $table_name, string $alias = "", string $on = ""){
        $this->join($table_name, $alias, $on,"RIGHT");
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
    
    public function select_with_function(array $functions){
        if(!$this->fields) {
            $this->fields = array();
        }
        foreach ($functions as $function){
            array_push($this->fields, $function);
        }
        return $this;
    }

    public function unset_fields(){
        unset($this->fields);
        $this->fields = array();
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

    public function groupBy(string $groupBy){
        $this->groupBy = $groupBy;
        return $this;
    }

    private function getGroupBy(){
        return $this->groupBy ? "GROUP BY $this->groupBy" : "";
    }

    public function having(string $having){
        $this->having = $having;
    }

    private function getHaving(){
        return $this->having ? "HAVING $this->having" : "";
    }
    
    public function condition(string $condition, array $params = NULL, $connect = "AND"){
        $this->condition = $this->condition ? "$this->condition $connect $condition" : $condition;
        if($params){
            $this->params = empty($this->params) ? $params : array_merge($this->params,$params);
        }
        return $this;
    }
    
    private function getCondition(){
        return $this->condition ? "WHERE ".$this->condition: "";
    }
    
    public function limit(int $limit, int $offset = 0){
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }
    
    private function getLimit(){
        return $this->limit ? "LIMIT ".$this->limit.($this->offset ? " OFFSET ".$this->offset : "") : "";
    }
}