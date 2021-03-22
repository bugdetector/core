<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Model;

/**
 * Object relation with table reset_password_queue
 * @author murat
 */

class ResetPassword extends Model
{
    public TableReference $user;
    public ShortText $key;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "reset_password_queue";
    }
}
