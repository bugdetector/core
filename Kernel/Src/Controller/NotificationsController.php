<?php

namespace Src\Controller;

use CoreDB;
use CoreDB\Kernel\ServiceController;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Lib\PushNotification\PNPayload;

class NotificationsController extends ServiceController
{
    public function enabled()
    {
        return defined("NOTIFICATIONS_ENABLED") ? NOTIFICATIONS_ENABLED : false;
    }

    public function vapidKey()
    {
        return defined("PUBLIC_VAPID_KEY") ? PUBLIC_VAPID_KEY : "";
    }

    public function saveSubscription()
    {
        $data = $_POST;
        $data["user"] = CoreDB::currentUser()->ID->getValue();
        $data["expirationTime"] = $data["expirationTime"] ? date("Y-m-d H:i:s", $data["expirationTime"]) : null;

        $subscription = CoreDB::config()->getEntityInstance("push_notification_subscriptions");
        $subscription->map($data);
        $subscription->save();
        $payload = new PNPayload(
            Translation::getTranslation("push_notification_welcome_title", [
                Variable::getByKey("site_name")->value->getValue()
            ]),
            Translation::getTranslation("notification_welcome_text"),
            BASE_URL . "/assets/logo.png"
        );
        $payload->setTag('news', true);
        $payload->setURL(BASE_URL);
        \CoreDB::notification()->push($payload, $subscription);
    }
}
