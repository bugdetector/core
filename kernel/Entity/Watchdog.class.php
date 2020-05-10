<?php


class Watchdog extends DBObject{
    const TABLE = "watchdog";
    public $event;
    public $value;
    public $ip;
    public $created_at;
    public $last_updated;
    
    public static function log(string $event, string $value){
        $watchdog = new Watchdog(self::TABLE);
        $watchdog->event = $event;
        $watchdog->value = $value;
        $watchdog->ip = User::get_user_ip();
        $watchdog->save();
    }
}
