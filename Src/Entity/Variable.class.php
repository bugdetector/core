<?php

namespace Src\Entity;

/**
 * Object relation with table variables
 * @author murat
 */

class Variable extends DBObject
{
    const TABLE = "variables";
    public $ID;
    public $key;
    public $value;
    public $created_at;
    public $last_updated;
    
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public static function create($key) : Variable
    {
        $variable = new Variable();
        $variable->key = $key;
        return $variable;
    }

    /**
     * @Override
     */
    public static function get(array $filter, string $table = self::TABLE) : ?Variable
    {
        return parent::get($filter, self::TABLE);
    }

    /**
     * @Override
     */
    public static function getAll(array $filter, string $table = self::TABLE) : array
    {
        return parent::getAll($filter, $table);
    }

    public static function getByKey(string $key)
    {
        try {
            return self::get(["key" => $key]);
        } catch (Exception $ex) {
            return null;
        }
    }
}
