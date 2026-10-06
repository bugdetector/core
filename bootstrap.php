<?php

use CoreDB\Kernel\Database\DatabaseInstallationException;
use CoreDB\Kernel\Environment;

include __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/Kernel/CoreDB.php';
define("IS_CLI", php_sapi_name() === 'cli');

Environment::load(__DIR__);

try {
    if (!IS_CLI) {
        $host = \CoreDB::baseHost();
        if (defined("TIMEZONE")) {
            date_default_timezone_set(TIMEZONE);
        }
        define("BASE_URL", (@$_SERVER["REQUEST_SCHEME"] ?: "http") . "://" . $host . SITE_ROOT);

        $headers = getallheaders();
        $httpAuthorizationHeader = @$_SERVER["HTTP_AUTHORIZATION"] ?: (
            @$_SERVER["REDIRECT_HTTP_AUTHORIZATION"] ?: @$_SERVER["REDIRECT_REDIRECT_HTTP_AUTHORIZATION"]
        );
        if (!@$headers["Authorization"] && $httpAuthorizationHeader) {
            $headers["Authorization"] = $httpAuthorizationHeader;
        }
        if (@$headers["Authorization"] && !isset($_COOKIE[session_name()])) {
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
    // Tables are missing: the database has not been installed yet.
    http_response_code(503);
    echo $ex->getMessage() . PHP_EOL
        . "Install the database with: php bin/console.php config:import";
}
