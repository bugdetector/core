<?php

namespace Src\Entity;

use CoreDB;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;

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

    public function getResultHeaders(bool $translateLabel = true): array
    {
        return [
            "",
            "ID" => "ID",
            "user" => Translation::getTranslation("user"),
            "expirationTime" => Translation::getTranslation("expirationTime"),
            "created_at" => Translation::getTranslation("created_at"),
            "last_updated" => Translation::getTranslation("last_updated"),
        ];
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $userClass = CoreDB::config()->getEntityClassName("users");
        return CoreDB::database()->select(static::getTableName(), "pns")
            ->join($userClass::getTableName(), "u", "u.ID = pns.user")
            ->select("pns", [
                "ID as edit_actions",
                "ID"
            ])->selectWithFunction([
                "CONCAT(u.username, ' - ', u.name, ' ', u.surname) as userinfo",
            ])->select("pns", [
                "expirationTime",
                "created_at",
                "last_updated"
            ]);
    }
}
