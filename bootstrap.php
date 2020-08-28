<?php

define("DIRECT_OBJECT_REF_SHIELD", TRUE);
include __DIR__.'/vendor/autoload.php';
include __DIR__.'/config/config.php';
include __DIR__.'/Kernel/CoreDB.php';

$host = \CoreDB::baseHost();
define("BASE_URL", HTTP."://".$host.SITE_ROOT);
session_start();

use Src\Entity\Variable;
use Src\Entity\User;
use CoreDB\Kernel\Router;

//Locating for installing system
if(empty(\CoreDB::database()::getTableList()) || !Variable::getByKey("version")){
    define("VERSION", 0);
    if($_SERVER["REQUEST_URI"] != SITE_ROOT."/admin/manage/update".(isset($_SESSION["install_key"]) ? "?key=".$_SESSION["install_key"] : "")){
        $_SESSION["install_key"] = hash("SHA256", date("Y-m-d H:i:s"));
        \CoreDB::goTo(BASE_URL."/admin/manage/update?key=".$_SESSION["install_key"]);
    }
} else {
    define("VERSION", Variable::getByKey("version")->value);
    User::get_current_core_user();
}
date_default_timezone_set(TIMEZONE);

$uri = trim(str_replace(BASE_URL, "", HTTP."://".$host.$_SERVER["REQUEST_URI"]),"/");
$uri = explode("/", preg_replace("/\?.*/", "", $uri));

$router = new Router($uri);
$router->route();
