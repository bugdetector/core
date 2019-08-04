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
define("PAGE", 1);
define("BLOG", 2);
class Content extends DBObject {
    const TABLE_NAME = "content";


    public $table = self::TABLE_NAME;
    public $ID, $title, $body, $site_name, $url_alias, $type, $created, $view_count, $image;
    
    public function __construct() {
        parent::__construct($this->table);
    }
    
    public static function getByUrlAlias(string $url_alias) {
        $instance = new self();
        $server_name = $_SERVER["SERVER_NAME"];
        $server_name = strpos($server_name, "www.") == 0 ? str_replace("www.", "", $server_name): $server_name;
        $content = db_select($instance->table,"c")
                ->select("c",["*"])
                ->join("available_sites","avail")
                ->condition("(c.url_alias = :alias OR c.ID = :alias ) AND avail.ID = c.site_name AND avail.site_name = :site_name", 
                        [":alias" => $url_alias, ":site_name" => $server_name] )
                ->execute()->fetch(PDO::FETCH_ASSOC);
        
        
        if($content){
            object_map($instance, $content);
        }
        $instance->body = htmlspecialchars_decode($instance->body);
        return $instance;
    }


    public function isExists() : bool {
        return $this->ID != 0;
    }
    
    public static function getContentList(string $filter = "", int $limit = PAGE_SIZE_LIMIT, string $orderBy = NULL) {
        $query = db_select(self::TABLE_NAME)->limit($limit);
        if($orderBy){
            $query->orderBy($orderBy);
        }
        if($filter){
            $query->condition(" title LIKE :filter OR body LIKE :filter ", [":filter" => "%$filter%"]);
        }
        return $query->execute()->fetchAll(PDO::FETCH_CLASS, __CLASS__);
    }
    
    public function getImageLink(){
        return BASE_URL."/files/uploaded/{$this->table}/image/{$this->image}";
    }
}
