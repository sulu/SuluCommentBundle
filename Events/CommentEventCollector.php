<?php

namespace Sulu\Bundle\CommentBundle\Events;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class CommentEventCollector implements CommentEventCollectorInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Event[]
     */
    private $eventsToBeDispatched = [];

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function collect(Event $commentEvent, string $commentEventName): void
    {
        $this->eventsToBeDispatched[] = ['event' => $commentEvent, 'eventName' => $commentEventName];
    }

    public function clear(): void
    {
        $this->eventsToBeDispatched = [];
    }

    public function dispatch(): void
    {
        $batchEvents = $this->eventsToBeDispatched;

        $this->eventsToBeDispatched = [];

        foreach ($batchEvents as $commentEvent) {
            $this->eventDispatcher->dispatch($commentEvent['event'], $commentEvent['eventName']);
        }
    }
}
