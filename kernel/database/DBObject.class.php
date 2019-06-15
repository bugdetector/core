<?php

class DBObject{
    public $table;
    
    public $ID = 0;
    public function __construct(string $table) {
        $this->table = $table;
    }

    public function getById(int $id){
        $result = db_select($this->table)->condition("ID = :id")->params(["id" => $id])->execute()->fetch(PDO::FETCH_ASSOC);
        if(is_array($result)){
            object_map($this, $result);
        }
    }
    
    public function insert(){
        $statement = db_insert($this->table, convert_object_to_array($this))->execute();
        $this->ID = CoreDB::getInstance()->lastInsertId();
        return $statement;
    }
    
    public function update(){
        return db_update($this->table, convert_object_to_array($this))->condition("ID = :id", ["id" => $this->ID])->execute();
    }
    
    public function delete(){
        $table_description = get_table_description($this->table);
        foreach ($table_description as $field) {
            if($field[1] == "tinytext"){
                $field_name = $field[0];
                remove_uploaded_file($this->table, $field_name, $this->$field_name);
            }
        }
        return db_delete($this->table)->condition(" ID = :id ", ["id" => $this->ID])->execute();
    }
    
}


