<?php
namespace Src\Entity;

use CoreDB\Kernel\Field\Text;
use ReflectionProperty;

class Watchdog extends DBObject
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
    public static function get(array $filter, string $table = self::TABLE) : ?Variable
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

    public static function log(string $event, string $value)
    {
        $watchdog = new Watchdog(self::TABLE);
        $watchdog->event = $event;
        $watchdog->value = $value;
        $watchdog->ip = User::get_user_ip();
        $watchdog->save();
    }
}
