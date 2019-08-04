<?php

class AccessLog extends DBObject {
    public $table = "access_log";
    
    public $ID, $user, $ip_adress, $date, $request_uri, $referer;
    
    public function __construct() {
    }

    public function insert() {
        $this->user = get_current_core_user()->ID;
        $this->ip_adress = get_user_ip();
        $this->date = get_current_date();
        $this->request_uri = $_SERVER["REQUEST_URI"];
        $this->referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : NULL;
        parent::insert();
    }
}
