<?php

namespace CoreDB\Kernel;

use Src\Controller\AccessdeniedController;

class Router
{
    const MAINPAGE = "mainpage";
    const NOT_FOUND = "NotFoundController";
    const ACCESS_DENIED = "AccessdeniedController";

    /**
     * Argument supplied from route
     *
     * @var array $arguments
     * Arguments
     */
    private array $arguments;
    /**
     * Controller matched route
     *
     * @var ControllerInterface
     * Controller
     */
    private ControllerInterface $controller;
    
    private static $instance = null;


    public function __construct(array $uri)
    {
        self::$instance = $this;
        $this->arguments = $uri;
    }

    /**
     *
     * @return Router
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            die("Invalid access");
        }
        return self::$instance;
    }
    
    public function route($route = null)
    {
        if ($route) {
            $current_arguments = explode("/", $route);
        } else {
            if (!$this->arguments[0]) {
                $this->arguments[0] = self::MAINPAGE;
            }
            $current_arguments = $this->arguments;
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
        
        if (!$controller_name) {
            $controller_name = $namespace.self::NOT_FOUND;
        }
        $this->controller = new $controller_name($current_arguments);
        if (!$this->controller->checkAccess()) {
            if (!\CoreDB::currentUser()->isLoggedIn()) {
                \CoreDB::goTo(BASE_URL."/login?destination=/". implode("/", $this->arguments));
            } else {
                $this->controller = new AccessdeniedController($this->arguments);
            }
        }
        $this->controller->processPage();
        die();
    }
    
    public function get_arguments()
    {
        return $this->arguments;
    }

    public static function getController() : ControllerInterface
    {
        return self::$instance->controller;
    }
}
