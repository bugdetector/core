<?php
namespace Src\Entity;

use CoreDB\Kernel\TableMapper;

class Watchdog extends TableMapper
{
    const TABLE = "watchdog";
    public $ID;
    public $event;
    public $value;
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
    public static function get(array $filter) : ?Variable
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

    public static function clear()
    {
        parent::clearTable(self::TABLE);
    }
    
    public static function log(string $event, string $value)
    {
        $watchdog = new Watchdog(self::TABLE);
        $watchdog->event = $event;
        $watchdog->value = $value;
        $watchdog->ip = User::get_user_ip();
        $watchdog->save();
    }
}
