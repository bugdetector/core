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
 * production -> Twig cache enabled
 *
 * development -> Twig cache disabled
 */

define("ENVIROMENT", "development");