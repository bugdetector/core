<?php

namespace Src\Entity;

use CoreDB\Kernel\TableMapper;

/**
 * Object relation with table reset_password_queue
 * @author murat
 */

class ResetPassword extends TableMapper
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
     * @inheritdoc
     */
    public static function get(array $filter) : ?ResetPassword
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
