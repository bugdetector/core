<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CreateQueryPreparer
 *
 * @author murat
 */
class CreateQueryPreparer extends CoreDBQueryPreparer {
    private $table_name;
    private $fields = [];
    public function __construct(string $table_name){
        $this->table_name = $table_name;
    }
    public function setFields(array $fields) : CreateQueryPreparer {
        $this->fields = $fields;
        return $this;
    }

    public function addField(array $field_definition) : CreateQueryPreparer {
        /**
         * $field_definition format must be
         * $field_definition = [
         *  "field_name" => 'field', 
         *  "field_type" => 'VARCHAR', 
         *  "field_length" => 255, #optional
         *  "is_unique" => TRUE #optional
         *  "mul_table" => $table_name # for references
         * ]
         */
        $this->fields[] = $field_definition;
        return $this;
    }

    public function getQuery(): string {
        $constants = [];
        $references = [];
        
        $query = "CREATE TABLE `{$this->table_name}` ( ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY";
        foreach ($this->fields as $field){
            $field["field_name"] = preg_replace("/[^a-z1-9_]+/", "",$field["field_name"]);
            $query .= ", `".$field["field_name"]."` ";
            if($field["field_type"] === "VARCHAR"){
                $query.= "VARCHAR(".intval($field["field_length"]).")";
            }else if(in_array($field["field_type"], ["INT", "DOUBLE", "TEXT", "DATE", "DATETIME", "TIME", "TINYTEXT", "LONGTEXT"])){
                $query.= $field["field_type"];
            }else if($field["field_type"] == "MUL" && in_array($field["mul_table"], CoreDB::get_information_scheme())){
                $query.= "INT";
                array_push($references, [$field["field_name"], $field["mul_table"]]);
            }else {
                throw new Exception(_t(67));
            }
            
            if(isset($field["is_unique"]) && $field["is_unique"] == 1){
                array_push($constants, $field["field_name"]);
            }
        }
        foreach ($references as $reference){
            $query.= ", FOREIGN KEY (`$reference[0]`) REFERENCES `$reference[1]`(ID) ";
        }
        foreach ($constants as $constant){
            $query.= ", UNIQUE (`$constant`) ";
        }
        $query.= ") CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB;";
        return $query;
    }
    
    public function execute() : PDOStatement {
        $result = parent::execute();
        Migration::addMigration($this);
        return $result;
    }

}
