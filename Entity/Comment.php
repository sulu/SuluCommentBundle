<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Entity;

use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Security\Authentication\UserInterface;

/**
 * Minimum implementation for comments.
 */
class Comment implements CommentInterface, AuditableInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $state = self::STATE_PUBLISHED;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var ThreadInterface|null
     */
    protected $thread;

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var \DateTime
     */
    protected $changed;

    /**
     * @var UserInterface
     */
    protected $changer;

    /**
     * @var UserInterface
     */
    protected $creator;

    /**
     * @param int $state
     * @param ThreadInterface $thread
     */
    public function __construct($state = self::STATE_PUBLISHED, ThreadInterface $thread = null)
    {
        $this->state = $state;
        $this->thread = $thread;

        if ($this->thread && $this->isPublished()) {
            $this->thread->increaseCommentCount();
        }
    }

    /**
     * Returns id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function publish()
    {
        if (null !== $this->thread && !$this->isPublished()) {
            $this->thread->increaseCommentCount();
        }

        $this->state = self::STATE_PUBLISHED;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unpublish()
    {
        if (null !== $this->thread && $this->isPublished()) {
            $this->thread->decreaseCommentCount();
        }

        $this->state = self::STATE_UNPUBLISHED;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublished()
    {
        return self::STATE_PUBLISHED === $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getThread()
    {
        if (!$this->thread) {
            throw new \RuntimeException('No thread assigned.');
        }

        return $this->thread;
    }

    /**
     * {@inheritdoc}
     */
    public function setThread(ThreadInterface $thread)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * @return UserInterface|null
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return UserInterface|null
     */
    public function getChanger()
    {
        return $this->changer;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatorFullName()
    {
        $creator = $this->getCreator();
        if (!$creator) {
            return '';
        }

        return $creator->getFullName();
    }

    /**
     * {@inheritdoc}
     */
    public function getChangerFullName()
    {
        $changer = $this->getChanger();
        if (!$changer) {
            return '';
        }

        return $changer->getFullName();
    }
}
