<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Events;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
