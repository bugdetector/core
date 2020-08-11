<?php

namespace CoreDB\Kernel;

interface MesengerInterface {
    const ERROR = 0;
    const WARNING = 1;
    const INFO = 2;
    const SUCCESS = 3;

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
    public function getMessages() : array;

    /**
     * Clear all messages
     */
    public function clearMessages() : void;
}