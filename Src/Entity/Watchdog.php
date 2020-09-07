<?php
namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\TableMapper;

class Watchdog extends TableMapper
{
    const TABLE = "watchdog";
    public ShortText $event;
    public ShortText $value;
    public ShortText $ip;
    

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @inheritdoc
     */
    public static function get(array $filter) : ?Variable
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
    
    public static function log(string $event, string $value)
    {
        $watchdog = new Watchdog(self::TABLE);
        $watchdog->event->setValue($event);
        $watchdog->value->setValue($value);
        $watchdog->ip->setValue(User::get_user_ip());
        $watchdog->save();
    }
}
