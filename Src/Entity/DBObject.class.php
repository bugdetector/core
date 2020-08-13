<?php
namespace Src\Entity;

use CoreDB\Kernel\TableMapper;

class DBObject extends TableMapper
{
    public $table;

    public $ID;

    /**
     * @param array $filter 
     *  Filter on key value pairs
     * @param string $table 
     *  Table name
     * @return DBObject
     */
    public static function get(array $filter, string $table = null) : DBObject
    {
        return parent::find($filter, $table);
    }

    public static function getAll(array $filter, string $table = null) : array
    {
        return parent::findAll($filter, $table);
    }

    public static function clear($table = null){
        parent::clearTable($table);
    }

    /**
    * Set fields of object using an array with same keys
    * @param array $array
    *  Containing field values to set
    */
    public function map(array $array)
    {
        $this->changed_fields = [];
        foreach ($array as $key => $value) {
            if (isset($this->{$key}) && $this->{$key} != $value) {
                $this->changed_fields[$key] = [
                    "old_value" => $this->{$key},
                    "new_value" => $value
                ];
            }
            $this->$key = $value;
        }
    }
}
