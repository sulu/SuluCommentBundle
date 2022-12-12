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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

class Comment implements CommentInterface, AuditableInterface
{
    use AuditableTrait;

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
     * @var int
     */
    protected $lft;

    /**
     * @var int
     */
    protected $rgt;

    /**
     * @var int
     */
    protected $depth;

    /**
     * @var CommentInterface|null
     */
    protected $parent;

    /**
     * @var Collection<int, CommentInterface>
     */
    protected $children;

    public function __construct(int $state = self::STATE_PUBLISHED, ThreadInterface $thread = null)
    {
        $this->state = $state;
        $this->thread = $thread;
        $this->children = new ArrayCollection();

        if ($this->thread && $this->isPublished()) {
            $this->thread->increaseCommentCount();
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function publish(): CommentInterface
    {
        if (null !== $this->thread && !$this->isPublished()) {
            $this->thread->increaseCommentCount();
        }

        $this->state = self::STATE_PUBLISHED;

        return $this;
    }

    public function unpublish(): CommentInterface
    {
        if (null !== $this->thread && $this->isPublished()) {
            $this->thread->decreaseCommentCount();
        }

        $this->state = self::STATE_UNPUBLISHED;

        return $this;
    }

    public function isPublished(): bool
    {
        return self::STATE_PUBLISHED === $this->state;
    }

    public function getMessage(): string
    {
        return $this->message ?? '';
    }

    public function setMessage(string $message): CommentInterface
    {
        $this->message = $message;

        return $this;
    }

    public function getThread(): ThreadInterface
    {
        if (!$this->thread) {
            throw new \RuntimeException('No thread assigned.');
        }

        return $this->thread;
    }

    public function setThread(ThreadInterface $thread): CommentInterface
    {
        $this->thread = $thread;

        return $this;
    }

    public function getParent(): ?CommentInterface
    {
        return $this->parent;
    }

    public function setParent(?CommentInterface $parent = null): CommentInterface
    {
        $this->parent = $parent;

        return $this;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getPublishedChildren(): Collection
    {
        return $this->children->filter(
            function(CommentInterface $comment) {
                return $comment->isPublished();
            }
        );
    }

    public function getCreatorFullName(): string
    {
        $creator = $this->getCreator();
        if (!$creator) {
            return '';
        }

        return $creator->getFullName();
    }

    public function getChangerFullName(): string
    {
        $changer = $this->getChanger();
        if (!$changer) {
            return '';
        }

        return $changer->getFullName();
    }
}
