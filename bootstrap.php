<?php

use CoreDB\Kernel\Database\DatabaseInstallationException;
use Src\Controller\InstallController;

include __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/Kernel/CoreDB.php';
define("IS_CLI", php_sapi_name() === 'cli');

try {
    if (is_file(__DIR__ . '/config/config.php')) {
        include __DIR__ . '/config/config.php';
        define("CONFIGURATON_LOADED", true);
    } else {
        define("CONFIGURATON_LOADED", false);
    }
    if (!IS_CLI) {
        $host = \CoreDB::baseHost();
        if (defined("TIMEZONE")) {
            date_default_timezone_set(TIMEZONE);
        }
        define("BASE_URL", $_SERVER["REQUEST_SCHEME"] . "://" . $host . SITE_ROOT);


        if (defined("HTTP_AUTH_ENABLED") && HTTP_AUTH_ENABLED) {
            if (
                @$_SERVER['PHP_AUTH_USER'] !== HTTP_AUTH_USERNAME ||
                @$_SERVER['PHP_AUTH_PW'] !== HTTP_AUTH_PASSWORD
            ) {
                header("WWW-Authenticate: Basic realm=\"Coredb Auth\"");
                header("HTTP/1.0 401 Unauthorized");
                die();
            }
        }

        $headers = getallheaders();
        if (!@$headers["Authorization"] && @$_SERVER["REDIRECT_HTTP_AUTHORIZATION"]) {
            $headers["Authorization"] = @$_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
        }
        if (@$headers["Authorization"]) {
            $sessionId = str_replace("Bearer ", "", $headers["Authorization"]);
            if (strlen($sessionId) > ini_get("session.sid_length")) {
                $sessionId = md5($sessionId);
            }
            session_id($sessionId);
        }
        session_start();
        CoreDB\Kernel\Router::getInstance()->route();
    }
} catch (DatabaseInstallationException $ex) {
    if (!CONFIGURATON_LOADED) {
        CoreDB::goTo(InstallController::getUrl());
    } else {
        echo $ex->getMessage();
    }
}
