<?php
define("DIRECT_OBJECT_REF_SHIELD", TRUE);

require './kernel/router/Router.php';
include './core-config.php';
require "./kernel/database/CoreDB.php";
include './core-globals.php';
include_dir("Entity");
include_dir("lib");

define("SITE_ROOT", substr(str_replace(basename(__FILE__), "", $_SERVER["SCRIPT_NAME"]), 0, -1 ) );
define("BASE_URL", HTTP."://".$_SERVER["HTTP_HOST"].SITE_ROOT);
session_start();

date_default_timezone_set(TIMEZONE);

$current_user = isset($_SESSION[BASE_URL."-UID"]) ? User::getUserById($_SESSION[BASE_URL."-UID"]) : new User();

$uri = trim(str_replace(SITE_ROOT, "", $_SERVER["REQUEST_URI"]),"/");
$uri = explode("/", preg_replace("/\?.*/", "", $uri));

$router = new Router($uri);
$router->route();
