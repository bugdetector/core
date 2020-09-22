<?php

use CoreDB\Kernel\Database\DatabaseInstallationException;
use Src\Controller\InstallController;

include __DIR__.'/vendor/autoload.php';
include __DIR__.'/Kernel/CoreDB.php';

try{
    if(is_file( __DIR__.'/config/config.php')){
        include __DIR__.'/config/config.php';
    }
    
    $host = \CoreDB::baseHost();
    if(defined("TIMEZONE")){
        date_default_timezone_set(TIMEZONE);
    }
    define("BASE_URL", $_SERVER["REQUEST_SCHEME"]."://".$host.SITE_ROOT);
    session_start();
    CoreDB\Kernel\Router::getInstance()->route();
}catch(DatabaseInstallationException $ex){
    CoreDB::goTo(InstallController::getUrl());
}

