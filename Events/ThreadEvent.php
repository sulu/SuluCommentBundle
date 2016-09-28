<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Events;

use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event-arguments for thread events.
 */
class ThreadEvent extends Event
{
    /**
     * @var ThreadInterface
     */
    private $thread;

    /**
     * @param ThreadInterface $thread
     */
    public function __construct(ThreadInterface $thread)
    {
        $this->thread = $thread;
    }

    /**
     * Returns thread.
     *
     * @return ThreadInterface
     */
    public function getThread()
    {
        return $this->thread;
    }
}
