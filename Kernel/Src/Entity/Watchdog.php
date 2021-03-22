<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Model;

class Watchdog extends Model
{
    public ShortText $event;
    public ShortText $value;
    public ShortText $ip;
    
    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "watchdog";
    }

    public static function log(string $event, string $value)
    {
        $watchdog = new Watchdog(self::getTableName());
        $watchdog->event->setValue($event);
        $watchdog->value->setValue($value);
        $watchdog->ip->setValue(User::getUserIp());
        $watchdog->save();
    }
}
