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
    const TABLE = "roles";
    public ShortText $role;

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @inheritdoc
     */
    public static function get(array $filter) : ?Role
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
}
