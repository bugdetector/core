<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\TableMapper;
use Exception;

/**
 * Object relation with table variables
 * @author murat
 */

class Variable extends TableMapper
{
    const TABLE = "variables";
    public ShortText $key;
    public Text $value;
    
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
     * @inheritdoc
     */
    public static function get(array $filter) : ?Variable
    {
        return parent::find($filter, self::TABLE);
    }

    /**
     * @inheritdoc
     */
    public static function getAll(array $filter) : array
    {
        return parent::findAll($filter, self::TABLE);
    }

    public static function clear()
    {
        parent::clearTable(self::TABLE);
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
