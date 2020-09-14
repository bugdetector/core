<?php

namespace CoreDB\Kernel;

use Src\Controller\AccessDeniedController;
use Src\Controller\LoginController;
use Src\Controller\MainpageController;
use Src\Controller\NotFoundController;

class Router
{
    const MAINPAGE = MainpageController::class;

    /**
     * Controller matched route
     *
     * @var ControllerInterface
     * Controller
     */
    private ControllerInterface $controller;
    
    private static $instance = null;


    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     *
     * @return Router
     */
    public static function getInstance() : Router
    {
        if (!self::$instance) {
            self::$instance = new Router();
        }
        return self::$instance;
    }
    
    public function route($route = null)
    {
        if (!$route) {
            $route = trim(str_replace(BASE_URL, "", $_SERVER["REQUEST_SCHEME"]."://".\CoreDB::baseHost().$_SERVER["REQUEST_URI"]),"/");
        }
        $this->controller = $this->getControllerFromUrl($route);
        if (!$this->controller->checkAccess()) {
            if (!\CoreDB::currentUser()->isLoggedIn()) {
                \CoreDB::goTo(LoginController::getUrl(), ["destination" => \CoreDB::requestUrl()]);
            } else {
                $this->controller = new AccessDeniedController([]);
            }
        }
        $this->controller->processPage();
        die();
    }

    public function getControllerFromUrl($url){
        $current_arguments = explode("/", preg_replace("/\?.*/", "", $url));
        if (!$current_arguments[0]) {
            $mainpageClass = self::MAINPAGE;
            return new $mainpageClass($current_arguments);
        }
        $namespace = "Src\\Controller\\";
        $controller_name = null;
        foreach ($current_arguments as $page) {
            $page = mb_convert_case($page, MB_CASE_TITLE);
            $temp_controller_name = "{$namespace}{$page}Controller";

            if (class_exists($temp_controller_name)) {
                $namespace = "{$namespace}{$page}\\";
                $controller_name = $temp_controller_name;
                array_shift($current_arguments);
            } else {
                break;
            }
        }
        if(!$controller_name){
            $controller_name = NotFoundController::class;
        }
        return new $controller_name($current_arguments);
    }

    /**
     * Return URL.
     * @return string
     *  URL
     */
    public function getUrl(string $controller) : string{
        $mainPath = explode("\\", str_replace("Src\\Controller\\", "", $controller));
        $route = "/";
        foreach($mainPath as $path){
            $route .= str_replace("controller", "", mb_strtolower($path))."/";
        }
        return BASE_URL.$route;
    }

    public static function getController() : ControllerInterface
    {
        return self::$instance->controller;
    }
}
