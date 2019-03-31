<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Content
 *
 * @author murat
 */
class Content extends DBObject {
    const TABLE_NAME = "content";

    public $ID, $title, $body, $site_name, $url_alias;
    public function __construct(){
        $server_name = $_SERVER["SERVER_NAME"];
        $server_name = strpos($server_name, "www.") == 0 ? str_replace("www.", "", $server_name): $server_name;
        $request_uri = $_SERVER["REQUEST_URI"];

        $content = db_select(self::TABLE_NAME,"c")
                ->join("available_sites","avail") 
                ->condition("(c.url_alias = :alias OR c.ID = :alias ) AND avail.ID = c.site_name AND avail.site_name = :site_name", 
                        [":alias" => $request_uri, ":site_name" => $server_name] )
                ->limit(1)
                ->execute()->fetch(PDO::FETCH_ASSOC);

        if($content){
            object_map($this, $content);
        }
    }
    
    public function isExists() : bool {
        return $this->ID != 0;
    }

    public function echoContent() {
        echo htmlspecialchars_decode($this->body);
    }
}
