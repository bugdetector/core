<?php

class Router {
    private $page, $arguments;
    private $controller;


    public static $mainPage = "mainpage";
    public static $notFound = "not_found";
    public static $accessDenied = "access_denied";
    
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
        }elseif(!in_array($this->page, $this->getWhitelist())){
            $this->page = self::$notFound;
        }
        require "pages/{$this->page}/{$this->page}.php";
        $page = mb_convert_case($this->page, MB_CASE_TITLE)."Controller";
        $this->controller = new $page($this->arguments);
        if(!$this->controller->check_access()){
            if(!get_current_core_user()->isLoggedIn()){
                core_go_to(BASE_URL."/login?destination=/". implode("/", $this->arguments));
            }else{
                require "pages/".self::$accessDenied."/".self::$accessDenied.".php";
                $this->controller = new AccessDeniedController($this->arguments);
            }
        }
        $this->loadPage();
    }
     public function loadPage(){
        $this->controller->echoPage();
        die();
    }
    
    private function getWhitelist(){
        return array_diff(scandir("pages"), [".", ".."]);
    }
    
    public function get_arguments(){
        return $this->arguments;
    }
}

