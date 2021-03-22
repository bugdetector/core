<?php

namespace Src\Entity;

use CoreDB\Kernel\Model;

class DynamicModel extends Model
{
    public static $table;
    
    public function __construct(string $tableName = null, array $mapData = [])
    {
        self::$table = $tableName;
        parent::__construct($tableName, $mapData);
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
    public static function get($filter, string $table_name = null)
    {
        self::$table = $table_name;
        return parent::get($filter);
    }

    /**
     * @inheritdoc
     */
    public static function getAll(array $filter, string $table_name = null): array
    {
        self::$table = $table_name;
        return static::findAll($filter, static::getTableName());
    }

    /**
    * Set fields of object using an array with same keys
    * @param array $array
    *  Containing field values to set
    */
    public function map(array $array, bool $isConstructor = false)
    {
        $this->changed_fields = [];
        foreach ($array as $key => $value) {
            if (!$isConstructor && isset($this->{$key}) && $this->{$key}->getValue() != $value) {
                $this->changed_fields[$key] = [
                    "old_value" => $this->{$key}->getValue(),
                    "new_value" => $value
                ];
            }
            $this->$key->setValue($value);
        }
    }
}
