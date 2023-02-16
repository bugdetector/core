<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;

/**
 * Object relation with table pn_subscriptions
 * @author mbakiyucel
 */

class PushNotificationSubscription extends Model
{
    /**
    * @var TableReference $user
    * User reference.
    */
    public TableReference $user;
    /**
    * @var ShortText $endpoint
    * Subscription endpoint.
    */
    public ShortText $endpoint;
    /**
    * @var DateTime $expirationTime
    * Timestamp converted to date time.
    */
    public DateTime $expirationTime;
    /**
    * @var Text $keys
    * Json formatted keys.
    */
    public Text $keys;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "push_notification_subscriptions";
    }

    /**
     * Extract origin from endpoint
     * @return string
     */
    public static function getOrigin($endpoint): string
    {
        return parse_url($endpoint, PHP_URL_SCHEME) . '://' . parse_url($endpoint, PHP_URL_HOST);
    }
}
