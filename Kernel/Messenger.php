<?php

namespace CoreDB\Kernel;

class Messenger implements MessengerInterface
{
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Messenger();
        }
        return self::$instance;
    }

    /**
     * @inheritdoc
     */
    public function createMessage(string $message, int $type = self::ERROR): void
    {
        if (!isset($_SESSION["messages"])) {
            $_SESSION["messages"] = [];
        }
        if (!isset($_SESSION["messages"][$type])) {
            $_SESSION["messages"][$type] = [];
        }
        $_SESSION["messages"][$type][] = $message;
    }

    public function getMessages(): array
    {
        if (!isset($_SESSION["messages"])) {
            $_SESSION["messages"] = [];
        }
        return $_SESSION["messages"];
    }

    public function clearMessages(): void
    {
        unset($_SESSION["messages"]);
    }
}
