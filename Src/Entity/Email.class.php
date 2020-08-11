<?php

namespace Src\Entity;

/**
 * Object relation with table emails
 * @author murat
 */

class Email extends DBObject
{
    const TABLE = "emails";
    public $ID;
    public $key;
    public $en;
    public $tr;
    public $created_at;
    public $last_updated;

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @Override
     */
    public static function get(array $filter, string $table = self::TABLE) : ?Email
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
