<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\TableMapper;

/**
 * Object relation with table logins
 * @author murat
 */

class Logins extends TableMapper
{
    const TABLE = "logins";
    public ShortText $ip_address;
    public ShortText $username;

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @inheritdoc
     */
    public static function get(array $filter) : ?Logins
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
