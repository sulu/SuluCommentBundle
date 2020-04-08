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

use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ThreadEvent extends Event
{
    /**
     * @var ThreadInterface
     */
    private $thread;

    public function __construct(ThreadInterface $thread)
    {
        $this->thread = $thread;
    }

    public function getThread(): ThreadInterface
    {
        return $this->thread;
    }
}
