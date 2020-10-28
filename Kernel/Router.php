<?php

namespace CoreDB\Kernel;

class Router
{
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
    public static function getInstance(): Router
    {
        if (!self::$instance) {
            self::$instance = new Router();
        }
        return self::$instance;
    }
    
    public function route($route = null)
    {
        if (!$route) {
            $route = trim(\CoreDB::requestUrl(), "/");
        }
        $this->controller = $this->getControllerFromUrl($route);
        if (!$this->controller->checkAccess()) {
            if (!\CoreDB::currentUser()->isLoggedIn()) {
                \CoreDB::goTo($this->getControllerFromUrl("login")->getUrl(), ["destination" => \CoreDB::requestUrl()]);
            } else {
                $this->controller = $this->getControllerFromUrl("accessdenied");
            }
        }
        $this->controller->processPage();
        die();
    }

    public function getControllerFromUrl($url)
    {
        if (strpos($url, BASE_URL) === 0) {
            $count = 1;
            $url = str_replace(BASE_URL . "/", "", $url, $count);
        }
        $currentArguments = explode("/", preg_replace("/\?.*/", "", $url));
        if (!$currentArguments[0]) {
            $currentArguments[0] = "mainpage";
        }
        $namespaceSrc = "Src\\Controller\\";
        $namespaceApp = "App\\Controller\\";
        $srcControllerName = null;
        $appControllerName = null;
        $appControllerFound = true;
        foreach ($currentArguments as $page) {
            $page = str_replace("_", "", mb_convert_case($page, MB_CASE_TITLE));
            $tempSrcControllerName = "{$namespaceSrc}{$page}Controller";
            $tempAppControllerName = "{$namespaceApp}{$page}Controller";

            $controllerFound = false;
            if (class_exists($tempSrcControllerName)) {
                $namespaceSrc = "{$namespaceSrc}{$page}\\";
                $srcControllerName = $tempSrcControllerName;
                $controllerFound = true;
                $appControllerName = "";
            }
            if ($appControllerFound && class_exists($tempAppControllerName)) {
                $namespaceApp = "{$namespaceApp}{$page}\\";
                $appControllerName = $tempAppControllerName;
                $controllerFound = true;
            }else{
                $appControllerFound = false;
            }
            if($controllerFound){
                array_shift($currentArguments);
            }
             else {
                break;
            }
        }
        $controllerName = $appControllerName ?: $srcControllerName;
        if (!$controllerName) {
            return $this->getControllerFromUrl("notfound");
        }
        return new $controllerName($currentArguments);
    }

    /**
     * Return URL.
     * @return string
     *  URL
     */
    public function getUrl(string $controller): string
    {
        $mainPath = explode("\\", str_replace(["Src\\Controller\\", "App\\Controller\\"], "", $controller));
        $route = "/";
        foreach ($mainPath as $path) {
            $route .= str_replace("controller", "", mb_strtolower($path)) . "/";
        }
        return BASE_URL . $route;
    }

    public static function getController(): ControllerInterface
    {
        return self::$instance->controller;
    }
}
