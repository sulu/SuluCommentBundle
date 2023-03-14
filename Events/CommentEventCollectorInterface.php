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

use Symfony\Contracts\EventDispatcher\Event;

interface CommentEventCollectorInterface
{
    public function collect(Event $commentEvent, string $commentEventName): void;

    public function clear(): void;

    public function dispatch(): void;
}
