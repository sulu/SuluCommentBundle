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

use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event-arguments for pre and post persist event.
 */
class CommentEvent extends Event
{
    /**
     * @var CommentInterface
     */
    private $comment;

    /**
     * @var ThreadInterface
     */
    private $thread;

    /**
     * @param CommentInterface $comment
     * @param ThreadInterface $thread
     */
    public function __construct(CommentInterface $comment, ThreadInterface $thread)
    {
        $this->comment = $comment;
        $this->thread = $thread;
    }

    /**
     * Returns comment.
     *
     * @return CommentInterface
     */
    public function getComment()
    {
        return $this->comment;
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
