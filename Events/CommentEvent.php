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
 * Event-arguments for comment events.
 */
class CommentEvent extends Event
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $entityId;

    /**
     * @var CommentInterface
     */
    private $comment;

    /**
     * @var ThreadInterface
     */
    private $thread;

    /**
     * @param string $type
     * @param string $entityId
     * @param CommentInterface $comment
     * @param ThreadInterface $thread
     */
    public function __construct($type, $entityId, CommentInterface $comment, ThreadInterface $thread)
    {
        $this->type = $type;
        $this->entityId = $entityId;
        $this->comment = $comment;
        $this->thread = $thread;
    }

    /**
     * Returns type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns entity-id.
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
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
