<?php

namespace Src\Controller;

use CoreDB;
use CoreDB\Kernel\ServiceController;
use Src\Entity\PushNotificationSubscription;
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
        $this->savePNSubscription(
            @$_GET["app_side"] ?
            PushNotificationSubscription::SUBSCRIPTION_TYPE_APP :
            PushNotificationSubscription::SUBSCRIPTION_TYPE_SITE,
            @$_POST["keys"]
        );
    }

    public function saveAndroidFcmSubscription()
    {
        $this->savePNSubscription(PushNotificationSubscription::SUBSCRIPTION_TYPE_ANDROID_APP, @$_POST["token"]);
    }

    public function saveIosFcmSubscription()
    {
        $this->savePNSubscription(PushNotificationSubscription::SUBSCRIPTION_TYPE_IOS_APP, @$_POST["token"]);
    }

    private function savePNSubscription($subscriptionType, $keysData)
    {
        if (!$keysData) {
            return;
        }
        $data = [];
        $data["user"] = CoreDB::currentUser()->ID->getValue();
        $data["expirationTime"] = @$_POST["expirationTime"] ? date("Y-m-d H:i:s", $_POST["expirationTime"]) : null;
        $data["subscription_type"] = $subscriptionType;
        $data["keys"] = $keysData;
        /** @var PushNotificationSubscription */
        $subscriptionClasss = CoreDB::config()->getEntityClassName("push_notification_subscriptions");
        $subscription = $subscriptionClasss::get([
            "keys" => is_array($keysData) ? json_encode($keysData) : $keysData
        ]) ?: new $subscriptionClasss();
        $subscription->map($data);
        if (!$subscription->ID->getValue()) {
            $subscription->save();
            $payload = new PNPayload(
                Translation::getTranslation("push_notification_welcome_title", [
                    Variable::getByKey("site_name")->value->getValue()
                ]),
                Translation::getTranslation("notification_welcome_text"),
                BASE_URL . "/assets/logo.png"
            );
            $payload->setURL(
                $subscription->subscription_type->getValue() == PushNotificationSubscription::SUBSCRIPTION_TYPE_SITE ?
                BASE_URL : (
                    defined("FRONTEND_URL") ? FRONTEND_URL : BASE_URL
                )
            );
            \CoreDB::notification()->push($payload, $subscription);
        } else {
            $subscription->save();
        }
    }

    public function denySubscription()
    {
        $_SESSION["PN_DENIED"] = true;
    }
}
