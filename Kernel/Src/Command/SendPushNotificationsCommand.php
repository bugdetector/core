<?php

namespace Src\Command;

use CoreDB;
use Exception;
use Src\Entity\PushNotification;
use Src\Entity\PushNotificationSubscription;
use Src\Entity\State;
use Src\Entity\Translation;
use Src\Lib\PushNotification\PNPayload;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendPushNotificationsCommand extends Command
{
    protected static $defaultName = "notifications:send";

    protected function configure()
    {
        $this->setDescription(
            Translation::getTranslation("send_push_notifications")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $configManager = CoreDB::config();
            $stateClass = $configManager->getEntityClassName("states");
            /** @var State */
            $state = $stateClass::getByKey(static::$defaultName) ?: new $stateClass(null, [
                "key" => self::$defaultName
            ]);
            if (!$state->value->getValue()) {
                $state->value->setValue(1);
                $state->save();

                /** @var PushNotification[] */
                $pushNotifications = $configManager->getEntityClassName("push_notifications")::getAll([]);
                $pnsClass = $configManager->getEntityClassName("push_notification_subscriptions");
                $pnService = CoreDB::notification();
                foreach ($pushNotifications as $pushNotification) {
                    /** @var PushNotificationSubscription */
                    $subscription = $pnsClass::get($pushNotification->subscription->getValue());
                    if ($subscription) {
                        $payload = new PNPayload(
                            html_entity_decode($pushNotification->title),
                            html_entity_decode($pushNotification->text),
                            $pushNotification->icon
                        );
                        $payload->setImage($pushNotification->image);
                        if ($pushNotification->url->getValue()) {
                            $payload->setURL($pushNotification->url);
                        }
                        $pnService->push($payload, $subscription);
                        $pushNotification->delete();
                    }
                }
                $state->value->setValue(0);
                $state->save();
                $output->writeln(Translation::getTranslation("all_notifications_pushed"));
            }
            return Command::SUCCESS;
        } catch (Exception $ex) {
            $output->writeln($ex->getMessage());
            return Command::FAILURE;
        }
    }

    protected function clearExpiredSubscriptions()
    {
        $configManager = CoreDB::config();
        $pnsClass = $configManager->getEntityClassName("push_notification_subscriptions");
        $expiredSubscriptions = CoreDB::database()->select($pnsClass::getTableName(), "pns")
            ->condition("expirationTime", "<=", CoreDB::currentDate())
            ->select("pns", ["ID"])
            ->execute()->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($expiredSubscriptions as $subscriptionId) {
            $subscription = $pnsClass::get($subscriptionId);
            $subscription->delete();
        }
    }
}
