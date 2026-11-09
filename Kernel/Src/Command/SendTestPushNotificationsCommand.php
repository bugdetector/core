<?php

namespace Src\Command;

use CoreDB;
use Exception;
use Src\Entity\PushNotification;
use Src\Entity\PushNotificationSubscription;
use Src\Entity\State;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Lib\PushNotification\PNPayload;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "notifications:test")]
class SendTestPushNotificationsCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription(
            Translation::getTranslation("send_test_push_notifications")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $configManager = CoreDB::config();
            $stateClass = $configManager->getEntityClassName("states");
            /** @var State */
            $state = $stateClass::getByKey($this->getName()) ?: new $stateClass(null, [
                "key" => $this->getName()
            ]);
            if (!$state->value->getValue()) {
                $state->value->setValue(1);
                $state->save();
                /** @var PushNotificationSubscription[] */
                $pnSubscriptions = $configManager->getEntityClassName("push_notification_subscriptions")::getAll([]);
                $pnService = CoreDB::notification();
                $payload = new PNPayload(
                    "Test",
                    Translation::getTranslation("This is a test notification sent by %s", [
                        Variable::getByKey("site_name")->value->getValue()
                    ])
                );
                foreach ($pnSubscriptions as $pnSubscription) {
                    $payload->setURL(
                        $pnSubscription->subscription_type->getValue() ==
                         PushNotificationSubscription::SUBSCRIPTION_TYPE_SITE ?
                        BASE_URL : FRONTEND_URL
                    );
                    $pnService->push($payload, $pnSubscription);
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
}
