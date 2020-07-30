<?php
/**
 * Object relation with table blocked_ips
 * @author murat
 */

class BlockedIp extends DBObject{
    const TABLE = "blocked_ips";
    public $ID;
    public $ip;
    public $created_at;
    public $last_updated;

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @Override
     */
    public static function get(array $filter, string $table = self::TABLE) : ?BlockedIp{
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