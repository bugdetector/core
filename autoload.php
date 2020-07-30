<?php

define("DIRECT_OBJECT_REF_SHIELD", TRUE);

include __DIR__.'/.config.php';
require __DIR__.'/kernel/Router.php';
require __DIR__."/kernel/database/CoreDB.php";
include __DIR__.'/kernel/View.php';
include __DIR__.'/kernel/Page.php';
include __DIR__.'/kernel/ServicePage.php';
include __DIR__.'/kernel/Utils.php';
include __DIR__.'/kernel/Migration.php';

$host = Utils::base_host();
define("BASE_URL", HTTP."://".$host.SITE_ROOT);

Utils::include_dir(__DIR__."/src", true);
Utils::include_dir(__DIR__."/views", true);
session_start();

//Locating for installing system
if(empty(CoreDB::get_information_scheme()) || !Variable::getByKey("version")){
    define("VERSION", 0);
    if($_SERVER["REQUEST_URI"] != SITE_ROOT."/admin/manage/update".(isset($_SESSION["install_key"]) ? "?key=".$_SESSION["install_key"] : "")){
        $_SESSION["install_key"] = hash("SHA256", date("Y-m-d H:i:s"));
        Utils::core_go_to(BASE_URL."/admin/manage/update?key=".$_SESSION["install_key"]);
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
