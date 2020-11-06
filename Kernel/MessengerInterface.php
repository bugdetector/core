<?php

namespace CoreDB\Kernel;

interface MessengerInterface
{
    public const ERROR = 0;
    public const WARNING = 1;
    public const INFO = 2;
    public const SUCCESS = 3;

    /**
     * Must be instantiate for only one
     * @return self
     * Instance
     */
    public static function getInstance();

    /**
     * Create message
     *
     * @param string $message
     * Message text
     * @param int $type
     * Message Type
     */
    public function createMessage(string $message, int $type = self::ERROR): void;

    /**
     * Returns messages
     * @return array
     *  Messages
     */
    public function getMessages(): array;

    /**
     * Clear all messages
     */
    public function clearMessages(): void;
}
