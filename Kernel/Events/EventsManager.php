<?php

namespace CoreDB\Kernel\Events;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\EventDispatcher\Event;

class EventsManager implements EventsManagerInterface
{
    private static ?EventsManagerInterface $instance = null;

    private EventDispatcher $dispatcher;

    private function __construct()
    {
        $this->dispatcher = new EventDispatcher();
        $eventSubscribers = Yaml::parseFile(__DIR__ . "/../../config/event_subscribers.yml");
        foreach ($eventSubscribers as $subscriberClass) {
            $this->addSubscriber(new $subscriberClass());
        }
    }

    /**
     * @inheritdoc
     */
    public static function getInstance(): EventsManagerInterface
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @inheritdoc
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->addSubscriber($subscriber);
    }

    /**
     * @inheritdoc
     */
    public function dispatch(Event $event, ?string $event_name = null)
    {
        $this->dispatcher->dispatch($event, $event_name);
    }
}
