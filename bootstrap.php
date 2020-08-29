<?php
include __DIR__.'/vendor/autoload.php';
include __DIR__.'/config/config.php';
include __DIR__.'/Kernel/CoreDB.php';

$host = \CoreDB::baseHost();
define("BASE_URL", HTTP."://".$host.SITE_ROOT);
session_start();

CoreDB::currentUser();
date_default_timezone_set(TIMEZONE);

$uri = trim(str_replace(BASE_URL, "", HTTP."://".$host.$_SERVER["REQUEST_URI"]),"/");
$uri = explode("/", preg_replace("/\?.*/", "", $uri));

$router = new CoreDB\Kernel\Router($uri);
$router->route();
