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

use Doctrine\Common\Collections\Collection;

interface ThreadInterface
{
    public function getId(): int;

    public function getType(): string;

    public function getEntityId(): string;

    public function getTitle(): string;

    public function setTitle(string $title): self;

    public function getCommentCount(): int;

    public function increaseCommentCount(): self;

    public function decreaseCommentCount(): self;

    public function setCommentCount(int $commentCount): self;

    /**
     * @return CommentInterface[]|Collection<int, CommentInterface>
     */
    public function getComments(): Collection;

    public function addComment(CommentInterface $comment): self;

    public function removeComment(CommentInterface $comment): self;

    public function getCreatorFullName(): string;

    public function getChangerFullName(): string;
}
