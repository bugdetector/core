<?php
/**
 * Object relation with table variables
 * @author murat
 */

class Variable extends DBObject{
    const TABLE = VARIABLES;
    public $ID;
    public $key;
    public $value;

    public function __construct($key = null)
    {
        $this->key = $key;
        parent::__construct(self::TABLE);
    }

    /**
     * @Override
     */
    public static function get(array $filter, string $table = self::TABLE){
        return parent::get($filter, self::TABLE);
    }

    /**
     * @Override
     */
    public static function getAll(array $filter, string $table = self::TABLE) : array
    {
        return parent::getAll($filter, $table);
    }

    public static function getByKey(string $key){
        try{
            return self::get(["key" => $key]);
        }catch(Exception $ex){
            return NULL;
        }
    }
}