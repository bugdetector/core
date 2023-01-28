<?php

namespace Src\Entity;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;

/**
 * Object relation with table state
 * @author mbakiyucel
 */

class State extends Model
{
    /**
    * @var ShortText $key
    * State key.
    */
    public ShortText $key;
    /**
    * @var Text $value
    * Value.
    */
    public Text $value;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "state";
    }

    public static function create($key): State
    {
        $state = new State();
        $state->key->setValue($key);
        return $state;
    }

    public static function getByKey(string $key): ?State
    {
        return self::get(["key" => $key]);
    }
}
