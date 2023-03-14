<?php

namespace Sulu\Bundle\CommentBundle\Events;

interface CommentEventCollectorInterface
{
    public function collect(CommentEvent $commentEvent, string $commentEventName): void;

    public function clear(): void;

    public function dispatch(): void;
}
