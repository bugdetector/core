<?php

namespace CoreDB\Kernel\Events;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

interface EventsManagerInterface
{
    /**
     * Event Manager is singleton.
     * This method returns instance.
     */
    public static function getInstance(): EventsManagerInterface;

    /**
     * Add new subscriber to dispatcher.
     */
    public function addSubscriber(EventSubscriberInterface $subscriber);

    /**
     * Dispatch new event.
     */
    public function dispatch(Event $event, ?string $event_name = null);
}
