<?php

define("DIRECT_OBJECT_REF_SHIELD", TRUE);

require './kernel/Router.php';
include './.config.php';
require "./kernel/database/CoreDB.php";
include './kernel/Page.class.php';
include './kernel/ServicePage.class.php';
include './kernel/Utils.php';
include './kernel/Migration.php';

define("SITE_ROOT", substr(str_replace(basename(__FILE__), "", $_SERVER["SCRIPT_NAME"]), 0, -1 ) );
define("BASE_URL", HTTP."://".$_SERVER["HTTP_HOST"].SITE_ROOT);

Utils::include_dir("kernel/form_builder");
session_start();

//Locating for installing system
if(!VERSION && empty(get_information_scheme())){
    if($_SERVER["REQUEST_URI"] != SITE_ROOT."/admin/manage/update".(isset($_SESSION["install_key"]) ? "?key=".$_SESSION["install_key"] : "")){
        $_SESSION["install_key"] = hash("SHA256", date("Y-m-d H:i:s"));
        core_go_to(BASE_URL."/admin/manage/update?key=".$_SESSION["install_key"]);
    }
} else {
    Utils::include_dir("Entity");
    Utils::include_dir("lib");
    Utils::include_dir("src");
    get_current_core_user();
}
date_default_timezone_set(TIMEZONE);

$uri = trim(str_replace(BASE_URL, "", HTTP."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]),"/");
$uri = explode("/", preg_replace("/\?.*/", "", $uri));

$router = new Router($uri);
$router->route();
