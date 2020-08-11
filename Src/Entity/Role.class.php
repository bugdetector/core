<?php

namespace Src\Entity;

/**
 * Object relation with table roles
 * @author murat
 */

class Role extends DBObject
{
    const TABLE = "roles";
    public $ID;
    public $role;
    public $created_at;
    public $last_updated;

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @Override
     */
    public static function get(array $filter, string $table = self::TABLE) : ?Role
    {
        return parent::get($filter, self::TABLE);
    }

    /**
     * @Override
     */
    public static function getAll(array $filter, string $table = self::TABLE) : array
    {
        return parent::getAll($filter, $table);
    }
}
