<?php
/**
 * Object relation with table logins
 * @author murat
 */

class Logins extends DBObject{
    const TABLE = "logins";
    public $ID;
    public $ip_address;
    public $username;
    public $created_at;
    public $last_updated;

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @Override
     */
    public static function get(array $filter, string $table = self::TABLE) : ?Logins{
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