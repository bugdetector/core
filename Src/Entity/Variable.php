<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\LongText;
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
    public LongText $value;
    
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
        $variable->key->setValue($key);
        return $variable;
    }

    public static function getByKey(string $key) : ?Variable
    {
        try {
            return self::get(["key" => $key]);
        } catch (Exception $ex) {
            return null;
        }
    }
}
