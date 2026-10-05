<?php

namespace CoreDB\Kernel;

use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Loads configuration from environment variables and .env files
 * and exposes it as the global constants used across the framework.
 *
 * Load order (later wins):
 *   .env -> .env.local -> .env.{APP_ENV} -> .env.{APP_ENV}.local -> real environment variables
 */
class Environment
{
    public const DEV = "dev";
    public const STAGING = "staging";
    public const PROD = "prod";

    private static string $projectDir;

    public static function load(string $projectDir): void
    {
        self::$projectDir = $projectDir;

        // Dotenv never overrides variables it can see in $_ENV/$_SERVER.
        // PHP does not fill $_ENV by default (variables_order), so copy real
        // variables in to let docker compose / CI values win over .env files.
        $_ENV += getenv();
        (new Dotenv())->loadEnv($projectDir . "/.env");

        define("APP_ENV", self::get("APP_ENV"));
        if (!in_array(APP_ENV, [self::DEV, self::STAGING, self::PROD])) {
            throw new RuntimeException(
                "APP_ENV must be one of: " . implode(", ", [self::DEV, self::STAGING, self::PROD])
            );
        }

        define("DB_DRIVER", self::get("DB_DRIVER"));
        define("DB_SERVER", self::required("DB_SERVER"));
        define("DB_NAME", self::required("DB_NAME"));
        define("DB_USER", self::required("DB_USER"));
        define("DB_PASSWORD", self::get("DB_PASSWORD"));

        define("HASH_SALT", self::required("HASH_SALT"));
        self::assertHashSaltOverridden();

        // Consumers check these with defined(), so leave them undefined when empty.
        if ($timezone = self::get("TIMEZONE")) {
            define("TIMEZONE", $timezone);
        }
        if ($trustedHosts = self::get("TRUSTED_HOSTS")) {
            define("TRUSTED_HOSTS", $trustedHosts);
        }

        define("LANGUAGE", self::get("LANGUAGE"));
        define("FRONTEND_URL", self::get("FRONTEND_URL"));
        define("THEME", self::get("THEME"));

        define("LOGIN_POLICY", self::get("LOGIN_POLICY"));
        define("LOGIN_POLICY_ROLES", self::getList("LOGIN_POLICY_ROLES"));
        define("REMEMBER_ME_TIMEOUT", self::get("REMEMBER_ME_TIMEOUT"));

        define("HTTP_AUTH_ENABLED", self::getBool("HTTP_AUTH_ENABLED"));
        define("HTTP_AUTH_USERNAME", self::get("HTTP_AUTH_USERNAME"));
        define("HTTP_AUTH_PASSWORD", self::get("HTTP_AUTH_PASSWORD"));

        define("PWA_ENABLED", self::getBool("PWA_ENABLED"));
        define("NOTIFICATIONS_ENABLED", self::getBool("NOTIFICATIONS_ENABLED"));
        define("VAPID_SUBJECT", self::get("VAPID_SUBJECT"));
        define("PUBLIC_VAPID_KEY", self::get("PUBLIC_VAPID_KEY"));
        define("PRIVATE_VAPID_KEY", self::get("PRIVATE_VAPID_KEY"));
        define("FIREBASE_CREDENTIALS_PATH", self::getPath("FIREBASE_CREDENTIALS_PATH"));

        // SITE_ROOT is only known for web requests.
        if (defined("SITE_ROOT")) {
            define("PWA_MANIFEST", [
                "name" => self::get("PWA_NAME"),
                "short_name" => self::get("PWA_SHORT_NAME"),
                "description" => self::get("PWA_DESCRIPTION"),
                "start_url" => ".",
                "display" => "standalone",
                "theme_color" => self::get("PWA_THEME_COLOR"),
                "background_color" => self::get("PWA_BACKGROUND_COLOR"),
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
            ]);
        }
    }

    public static function isDev(): bool
    {
        return APP_ENV == self::DEV;
    }

    public static function isProd(): bool
    {
        return APP_ENV == self::PROD;
    }

    private static function get(string $key): string
    {
        return trim((string) ($_ENV[$key] ?? ""));
    }

    private static function required(string $key): string
    {
        $value = self::get($key);
        if ($value === "") {
            throw new RuntimeException("Environment variable {$key} is required. Define it in .env.local.");
        }
        return $value;
    }

    private static function getBool(string $key): bool
    {
        return filter_var(self::get($key), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Comma separated values. Ex: LOGIN_POLICY_ROLES=Admin,User
     */
    private static function getList(string $key): array
    {
        return array_values(array_filter(array_map("trim", explode(",", self::get($key)))));
    }

    /**
     * Relative paths are resolved from the project directory.
     */
    private static function getPath(string $key): string
    {
        $path = self::get($key);
        if ($path !== "" && $path[0] !== "/") {
            $path = self::$projectDir . "/" . $path;
        }
        return $path;
    }

    /**
     * HASH_SALT encrypts EncryptedModel fields and JWT tokens.
     * The value committed in .env is public, so it must never reach production.
     */
    private static function assertHashSaltOverridden(): void
    {
        if (!self::isProd()) {
            return;
        }
        $blueprint = (new Dotenv())->parse(file_get_contents(self::$projectDir . "/.env"));
        if (HASH_SALT === ($blueprint["HASH_SALT"] ?? null)) {
            throw new RuntimeException(
                "HASH_SALT still has the public value from .env. Set a secret one for production."
            );
        }
    }
}
