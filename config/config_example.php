<?php

define("DB_SERVER", "%db_server");
define("DB_NAME", "%db_name");
define("DB_USER", "%db_user");
define("DB_PASSWORD", "%db_password");
define("HASH_SALT", "%hash_salt");

define("TIMEZONE", "Europe/Istanbul");

define("LANGUAGE", "tr");

define("TRUSTED_HOSTS", "localhost,127.0.0.1");

/**
 * production  -> Twig cache enabled, Mails send to exact location.
 * staging     -> Twig cache enabled, Mails send to test mail address.
 * development -> Twig cache disabled, Mails send to test mail address.
 */

define("ENVIROMENT", "development");


// To configure PWA feature use the section below.

define("PWA_ENABLED", true);
define(
    "PWA_MANIFEST",
    [
        "name" => "Core DB",
        "short_name" => "C DB",
        "description" => "Best practice web development tool.",
        "start_url" => ".",
        "display" => "standalone",
        "theme_color" => "#fff",
        "background_color" => "#fff",
        "icons" => [
            [
                "src" => SITE_ROOT ."/assets/square_logo.png",
                "sizes" => "120x120",
                "type" => "image/png"
            ],
            [
                "src" => SITE_ROOT ."/assets/square_logo-512x512.png",
                "sizes" => "512x512",
                "type" => "image/png"
            ]
        ]
    ]
);
