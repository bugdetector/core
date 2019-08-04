<?php
define("DIRECT_OBJECT_REF_SHIELD", TRUE);

require './kernel/router/Router.php';
include './core-config.php';
require "./kernel/database/CoreDB.php";
include './kernel/Page.class.php';
include './kernel/ServicePage.class.php';
include './core-globals.php';
include_dir("Entity");
include_dir("lib");
include_dir("src");

define("SITE_ROOT", substr(str_replace(basename(__FILE__), "", $_SERVER["SCRIPT_NAME"]), 0, -1 ) );
define("BASE_URL", HTTP."://".$_SERVER["HTTP_HOST"].SITE_ROOT);
session_start();

date_default_timezone_set(TIMEZONE);

if(isset($_SESSION[BASE_URL."-UID"])){
    $current_user = User::getUserById($_SESSION[BASE_URL."-UID"]);
}elseif(isset($_COOKIE["session-token"])){
    $jwt = JWT::createFromString($_COOKIE["session-token"]);
    $current_user = User::getUserById($jwt->getPayload()->ID);
    $_SESSION[BASE_URL."-UID"] = $current_user->ID;
}else{
    $current_user = User::getUserByUsername("guest");
}

$uri = trim(str_replace(BASE_URL, "", HTTP."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]),"/");
$uri = explode("/", preg_replace("/\?.*/", "", $uri));

$router = new Router($uri);
$router->route();
