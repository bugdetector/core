<?php

use Src\BaseTheme\BaseTheme;

define("DB_DRIVER", "%db_driver");
define("DB_SERVER", "%db_server");
define("DB_NAME", "%db_name");
define("DB_USER", "%db_user");
define("DB_PASSWORD", "%db_password");
define("HASH_SALT", "%hash_salt");

define("TIMEZONE", "Europe/Istanbul");

define("LANGUAGE", "tr");

define("TRUSTED_HOSTS", "localhost,localhost:8000");

/**
 * production  -> Twig cache enabled, Mails send to exact location.
 * staging     -> Twig cache enabled, Mails send to test mail address.
 * development -> Twig cache disabled, Mails send to test mail address.
 */

define("ENVIROMENT", "development");

/**
 * notify_all_users  -> All users can login by one device. Other devices sessions will thrown when another device used.
 * role_based_notify -> Defined roles using LOGIN_POLICY_ROLES will be able to login using only one device.
 * not_notify -> No restrictions available.
 */

define("LOGIN_POLICY", "not_notify");

/**
 * Give roles definitions in an array if LOGIN_POLICY role_based_one_device_login used.
 * Ex: ['Admin', 'User']
 */
define("LOGIN_POLICY_ROLES", []);

/**
 * Write a time for strtotime().
 */
define("REMEMBER_ME_TIMEOUT", "1 week");

// To configure PWA feature use the section below.
if (!IS_CLI) {
    define("PWA_ENABLED", true);
    define("NOTIFICATIONS_ENABLED", false);
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
                    "src" => SITE_ROOT . "/assets/square_logo.png",
                    "sizes" => "120x120",
                    "type" => "image/png"
                ],
                [
                    "src" => SITE_ROOT . "/assets/square_logo-512x512.png",
                    "sizes" => "512x512",
                    "type" => "image/png"
                ]
            ]
        ]
    );
}
// Frontend app url
define("FRONTEND_URL", "http://localhost:3000");
// Configure push notifications id notifications enabled otherwise no need to configure.
define("VAPID_SUBJECT", "");
define("PUBLIC_VAPID_KEY", "");
define("PRIVATE_VAPID_KEY", "");


define("THEME", BaseTheme::class);

define("HTTP_AUTH_ENABLED", false);
define("HTTP_AUTH_USERNAME", "core_user");
define("HTTP_AUTH_PASSWORD", "core_1234");