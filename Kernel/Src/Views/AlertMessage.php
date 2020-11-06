<?php

namespace Src\Views;

use Src\Entity\Translation;
use Src\Theme\View;

class AlertMessage extends View
{
    public const MESSAGE_TYPE_DANGER = "alert-danger";
    public const MESSAGE_TYPE_INFO = "alert-info";
    public const MESSAGE_TYPE_SUCCESS = "alert-success";
    public const MESSAGE_TYPE_WARNING = "alert-warning";

    public $message;
    public $type;

    public static function create(string $message, string $type = self::MESSAGE_TYPE_DANGER): AlertMessage
    {
        $alert_message = new AlertMessage();
        $alert_message->message = $message;
        $alert_message->type = $type;
        return $alert_message;
    }

    public function getTemplateFile(): string
    {
        return "alert_message.twig";
    }

    public function getMessageHeader()
    {
        $headers = [
            self::MESSAGE_TYPE_INFO => "info",
            self::MESSAGE_TYPE_DANGER => "error",
            self::MESSAGE_TYPE_WARNING => "warning",
            self::MESSAGE_TYPE_SUCCESS => null
        ];
        return Translation::getTranslation($headers[$this->type]);
    }
}
