<?php


class Watchdog extends DBObject{
    public $EVENT, $VALUE, $DATE, $IP;
    
    public static function log(string $event, string $value){
        $watchdog = new self(WATCHDOG);
        $watchdog->EVENT = $event;
        $watchdog->VALUE = $value;
        $watchdog->DATE = get_current_date();
        $watchdog->IP = get_user_ip();
        $watchdog->insert();
    }
}
