<?php

namespace Src\Lib\PushNotification;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use Src\Entity\PushNotificationSubscription;
use Src\Entity\User;

class PushNotificationService
{
    /**
     * Push notification service singleton instance.
     * @var PushNotificationService
     */
    protected static ?PushNotificationService $instance = null;

    /**
     * Returns instance.
     * @return PushNotificationService
     */
    public static function getInstance(): PushNotificationService
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Returns PNVapid object.
     * @return PNVapid
     */
    protected function getVapid(): PNVapid
    {
        return new PNVapid(
            VAPID_SUBJECT,
            PUBLIC_VAPID_KEY,
            PRIVATE_VAPID_KEY
        );
    }

    /**
     * Push payload to subscriptions of user if exist.
     * @param PNPayload $payload
     *  Notification payload
     * @param User|int $user
     *  User entity or user id.
     */
    public function pushNotificationToUser(PNPayload $payload, User|int $user)
    {
        if (!($user instanceof User)) {
            /** @var User */
            $user = \CoreDB::config()->getEntityClassName("users")::get($user);
        }
        $subscriptions = PushNotificationSubscription::getAll(["user" => $user->ID]);
        foreach ($subscriptions as $subscription) {
            $this->push($payload, $subscription);
        }
    }

    /**
     * Push payload to subscription as push notification.
     * If subscription is not active it will be deleted.
     * @param PNPayload $payload
     *  Notification payload
     * @param PushNotificationSubscription $subscription
     *  Subscription entity
     */
    public function push(PNPayload $payload, PushNotificationSubscription $subscription)
    {

        $keys = json_decode($subscription->keys->getValue(), true);
        $pnVapid = $this->getVapid();
        $pnEncrypt = new PNEncryption($keys["p256dh"], $keys["auth"]);
        $encryptedPayload = $pnEncrypt->encrypt($payload);
        $client = new Client([
            "headers" =>
            $pnEncrypt->getHeaders($pnVapid->getHeaders($subscription->endpoint)) + [
                "Content-Length" => mb_strlen($payload, '8bit'),
                "TTL" => 2419200
            ]
        ]);
        try {
            /** @var ResponseInterface */
            $response = $client->post(
                $subscription->endpoint->getValue(),
                [
                    "body" => $encryptedPayload
                ]
            );
            if (!($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
                $subscription->delete();
            }
        } catch (ClientException $ex) {
            $subscription->delete();
        }
    }
}
