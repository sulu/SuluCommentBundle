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
use Sulu\Component\Security\Authentication\UserInterface;

class Thread implements ThreadInterface, AuditableInterface
{
    use AuditableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $entityId;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $commentCount = 0;

    /**
     * @var Collection<int, CommentInterface>
     */
    protected $comments;

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
     * @param null|Collection<int, CommentInterface> $comments
     */
    public function __construct(string $type, string $entityId, Collection $comments = null, int $commentCount = 0)
    {
        $this->type = $type;
        $this->entityId = $entityId;
        $this->comments = $comments ?: new ArrayCollection();
        $this->commentCount = $commentCount;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): ThreadInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getCommentCount(): int
    {
        return $this->commentCount;
    }

    public function increaseCommentCount(): ThreadInterface
    {
        ++$this->commentCount;

        return $this;
    }

    public function decreaseCommentCount(): ThreadInterface
    {
        --$this->commentCount;

        return $this;
    }

    public function setCommentCount(int $commentCount): ThreadInterface
    {
        $this->commentCount = $commentCount;

        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(CommentInterface $comment): ThreadInterface
    {
        $this->comments->add($comment);
        $comment->setThread($this);

        if ($comment->isPublished()) {
            $this->increaseCommentCount();
        }

        return $this;
    }

    public function removeComment(CommentInterface $comment): ThreadInterface
    {
        $this->comments->removeElement($comment);

        if ($comment->isPublished()) {
            $this->decreaseCommentCount();
        }

        return $this;
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
