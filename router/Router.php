<?php

class Router {
    private $page, $arguments;
    
    public static $mainPage = "login";
    public static $notFound = "not_found";
    
    private static $instance = NULL;


    public function __construct(array $uri){
        self::$instance = $this;
        $this->page = count($uri) > 0? $uri[0] : "";
        $this->arguments = count($uri)>1 ? array_slice($uri, 1) : [];
    }

    /**
     * 
     * @return Router
     */
    public static function getInstance(){
        if(!self::$instance){
            die("Invalid access");
        }
        return self::$instance;
    }
    
    public function route(){
        if(!$this->page){
            $this->page = self::$mainPage;
        }
        $content = new Content();
        if($content->isExists()){
            $content->echoContent();
        }elseif(in_array($this->page, $this->getWhitelist())){
            $this->loadPage($this->page);
        }else{
            $this->loadPage(self::$notFound);
        }
    }
     public function loadPage(string $page){
        require "pages/$page/$page.php";
        $page = new Controller($this->arguments);
        $page->echoPage();
    }
    
    private function getWhitelist(){
        return array_diff(scandir("pages"), [".", ".."]);
    }
    
    public function get_arguments(){
        return $this->arguments;
    }
}

