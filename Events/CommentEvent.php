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

use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Symfony\Component\EventDispatcher\Event;

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

    public function __construct(string $type, string $entityId, CommentInterface $comment, ThreadInterface $thread)
    {
        $this->type = $type;
        $this->entityId = $entityId;
        $this->comment = $comment;
        $this->thread = $thread;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function getComment(): CommentInterface
    {
        return $this->comment;
    }

    public function getThread(): ThreadInterface
    {
        return $this->thread;
    }
}
