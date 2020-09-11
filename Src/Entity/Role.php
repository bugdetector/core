<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\TableMapper;

/**
 * Object relation with table roles
 * @author murat
 */

class Role extends TableMapper
{
    public ShortText $role;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "roles";
    }
}
