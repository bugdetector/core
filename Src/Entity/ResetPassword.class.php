<?php

namespace Src\Entity;

/**
 * Object relation with table reset_password_queue
 * @author murat
 */

class ResetPassword extends DBObject
{
    const TABLE = "reset_password_queue";
    public $ID;
    public $user;
    public $key;
    public $created_at;
    public $last_updated;

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @Override
     */
    public static function get(array $filter, string $table = self::TABLE) : ?ResetPassword
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
