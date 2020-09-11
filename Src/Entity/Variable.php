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
    public ShortText $key;
    public Text $value;
    
    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "variables";
    }

    public static function create($key) : Variable
    {
        $variable = new Variable();
        $variable->key = $key;
        return $variable;
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
