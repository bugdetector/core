<?php

use CoreDB\Kernel\Database\DatabaseInstallationException;
use Src\Controller\InstallController;

include __DIR__.'/vendor/autoload.php';
include __DIR__.'/Kernel/CoreDB.php';
define("IS_CLI", php_sapi_name() === 'cli');

try{
    if(is_file( __DIR__.'/config/config.php')){
        include __DIR__.'/config/config.php';
        define("CONFIGURATON_LOADED", true);
    }else{
        define("CONFIGURATON_LOADED", false);
    }
    if(!IS_CLI){
        $host = \CoreDB::baseHost();
        if(defined("TIMEZONE")){
            date_default_timezone_set(TIMEZONE);
        }
        define("BASE_URL", $_SERVER["REQUEST_SCHEME"]."://".$host.SITE_ROOT);
        $headers = getallheaders();
        if(@$headers["Authorization"]){
            $sessionId = str_replace("Bearer ", "", $headers["Authorization"]);
            if(strlen($sessionId) > 26){
                $sessionId = md5($sessionId);
            }
            session_id($sessionId);
        }
        session_start();
        CoreDB\Kernel\Router::getInstance()->route();
    }
}catch(DatabaseInstallationException $ex){
    if(!CONFIGURATON_LOADED){
        CoreDB::goTo(InstallController::getUrl());
    }else{
        echo $ex->getMessage();
    }
}

