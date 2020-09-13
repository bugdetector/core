<?php
namespace Src\Entity;

use CoreDB\Kernel\TableMapper;

class DBObject extends TableMapper
{
    public static $table;
    
    public function __construct(string $table)
    {
        self::$table = $table;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return self::$table;
    }

    /**
     * @inheritdoc
     */
    public static function get(array $filter, string $table_name = null){
        self::$table = $table_name;
        return static::find($filter, static::getTableName());
    }

    /**
     * @inheritdoc
     */
    public static function getAll(array $filter, string $table_name = null) : array{
        self::$table = $table_name;
        return static::findAll($filter, static::getTableName());
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
                    "old_value" => $this->{$key}->getValue(),
                    "new_value" => $value
                ];
            }
            $this->$key->setValue($value);
        }
    }
}
