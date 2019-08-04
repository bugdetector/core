<?php

class XMLsitemapController extends Page{
    public $nodes = [];


    public function echoPage() {
        $this->preprocessPage();
        $this->echoContent();
    }
    
    protected function preprocessPage() {
        $nodes[] = "/"; //mainpage
        
        $server_name = $_SERVER["SERVER_NAME"];
        $server_name = strpos($server_name, "www.") == 0 ? str_replace("www.", "", $server_name): $server_name;
        $request_uri = $_SERVER["REQUEST_URI"];

        $content = db_select(self::TABLE_NAME,"c")
                ->select("c",["title","created"])
                ->join("available_sites","avail")
                ->condition("avail.ID = c.site_name AND avail.site_name = :site_name", 
                        [":site_name" => $server_name] )
                ->execute()->fetch(PDO::FETCH_ASSOC);
                
    }

    protected function echoContent() {
        
    }

}

