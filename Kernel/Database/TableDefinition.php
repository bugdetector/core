<?php

namespace CoreDB\Kernel\Database;

use CoreDB;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\Integer;

class TableDefinition {
    public string $table_name;
    public string $table_comment;
    public array $fields = [];
    public bool $table_exist = false;
    
    private function __construct(string $table_name){
        $this->table_name = $table_name;
    }

    public static function getDefinition(string $table_name){
        $definition = new TableDefinition($table_name);
        if(in_array($table_name, CoreDB::database()->getTableList())){
            $definition->fields = CoreDB::database()->getTableDescription($table_name);
            $definition->table_exist = true;
        }else {
            $id_field = (new Integer("ID"));
            $id_field->isNull = false;
            $id_field->primary_key = true;
            $id_field->autoIncrement = true;
            $definition->fields["ID"] = $id_field;
            $definition->table_exist = false;
        }
        return $definition;
    }

    public function setComment(string $table_comment){
        $this->table_comment = $table_comment;
    }

    public function addField(DataTypeAbstract $data_type){
        if(in_array($data_type->column_name, ["ID", "created_at", "last_updated"])){
            return;
        }
        $this->fields[$data_type->column_name] = $data_type;
    }

    public function saveDefinition(){
        unset($this->fields["created_at"], $this->fields["last_updated"]);
        $created_at = new DateTime("created_at");
        $created_at->default = CoreDB::database()->currentTimestamp();
        $this->fields["created_at"] = $created_at;
        $last_updated = new DateTime("last_updated");
        $last_updated->default = CoreDB::database()->currentTimestampOnUpdate();
        $this->fields["last_updated"] = $last_updated;
        if($this->table_exist){
            CoreDB::database()->alter($this)->execute();
        }else{
            CoreDB::database()->create($this)->execute();
        }
    }
}
