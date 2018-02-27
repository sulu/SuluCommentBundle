<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Entity;

use Sulu\Component\Persistence\Model\AuditableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @var ThreadInterface
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

        if ($this->isPublished()) {
            $thread->increaseCommentCount();
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
     * {@inheritdoc}
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * {@inheritdoc}
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
        if (!$this->getCreator()) {
            return '';
        }

        return $this->getCreator()->getFullName();
    }

    /**
     * {@inheritdoc}
     */
    public function getChangerFullName()
    {
        if (!$this->getChanger()) {
            return '';
        }

        return $this->getChanger()->getFullName();
    }
}
