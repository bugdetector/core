<?php

namespace Src\Entity;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\ShortText;

/**
 * Object relation with table push_notifications
 * @author mbakiyucel
 */

class PushNotification extends Model
{
    /**
    * @var TableReference $subscription
    * Subscription reference that this push notification to be pushed.
    */
    public TableReference $subscription;
    /**
    * @var ShortText $title
    * Notification title
    */
    public ShortText $title;
    /**
    * @var ShortText $text
    * Notification text.
    */
    public ShortText $text;
    /**
    * @var ShortText $icon
    * Icon url.
    */
    public ShortText $icon;
    /**
    * @var ShortText $url
    * Url that user will redirect when clicked.
    */
    public ShortText $url;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "push_notifications";
    }
}
