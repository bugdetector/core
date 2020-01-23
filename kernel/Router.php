<?php

class Router {
    private $arguments;
    private $controller;


    public static $mainPage = "mainpage";
    public static $notFound = "not_found";
    public static $accessDenied = "access_denied";
    
    private static $instance = NULL;


    public function __construct(array $uri){
        self::$instance = $this;
        $this->arguments = $uri;
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
    
    public function route($route = NULL){
        if($route){
            $current_arguments = explode("/", $route);
        }else{
            if(!$this->arguments[0]){
                $this->arguments[0] = self::$mainPage;
            }
            $current_arguments = $this->arguments;
        }
        
        $dir = "pages/";
        $controller_name = "";
        foreach ($current_arguments as $page) {
            $dir = $dir.$page;
            $controller_file = $dir."/{$page}.php";
            if(is_dir($dir) && file_exists($controller_file) && in_array($page, $this->getWhitelist($dir."/.."))){
                include $controller_file;
                $controller_name .= mb_convert_case($page, MB_CASE_TITLE);
                $dir .= "/";
                array_shift($current_arguments);
            }
        }
        
        if(!$controller_name){
            include 'pages/'.self::$notFound."/".self::$notFound.".php";
            $controller_name = self::$notFound;
        }
        $page = $controller_name."Controller";
        $this->controller = new $page($current_arguments);
        if(!$this->controller->check_access()){
            if(!User::get_current_core_user()->isLoggedIn()){
                Utils::core_go_to(BASE_URL."/login?destination=/". implode("/", $this->arguments));
            }else{
                require "pages/".self::$accessDenied."/".self::$accessDenied.".php";
                $this->controller = new Access_DeniedController($this->arguments);
            }
        }
        $this->loadPage();
    }
     public function loadPage(){
        $this->controller->echoPage();
        die();
    }
    
    private function getWhitelist($dir){
        return array_diff(scandir($dir), [".", ".."]);
    }
    
    public function get_arguments(){
        return $this->arguments;
    }
}

