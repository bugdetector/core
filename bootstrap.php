<?php
include __DIR__.'/vendor/autoload.php';
include __DIR__.'/config/config.php';
include __DIR__.'/Kernel/CoreDB.php';

$host = \CoreDB::baseHost();
define("BASE_URL", $_SERVER["REQUEST_SCHEME"]."://".\CoreDB::baseHost().SITE_ROOT);
session_start();

date_default_timezone_set(TIMEZONE);

CoreDB\Kernel\Router::getInstance()->route();
