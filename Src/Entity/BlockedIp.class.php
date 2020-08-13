<?php

namespace Src\Entity;

use CoreDB\Kernel\TableMapper;

/**
 * Object relation with table blocked_ips
 * @author murat
 */

class BlockedIp extends TableMapper
{
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
    public static function get(array $filter) : ?BlockedIp
    {
        return parent::find($filter, self::TABLE);
    }

    /**
     * @Override
     */
    public static function getAll(array $filter) : array
    {
        return parent::findAll($filter, self::TABLE);
    }

    public static function clear(){
        parent::clearTable(self::TABLE);
    }
}
