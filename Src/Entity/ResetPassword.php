<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\TableMapper;

/**
 * Object relation with table reset_password_queue
 * @author murat
 */

class ResetPassword extends TableMapper
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
