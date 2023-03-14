<?php

namespace Sulu\Bundle\CommentBundle\Events;

use Symfony\Contracts\EventDispatcher\Event;

interface CommentEventCollectorInterface
{
    public function collect(Event $commentEvent, string $commentEventName): void;

    public function clear(): void;

    public function dispatch(): void;
}
