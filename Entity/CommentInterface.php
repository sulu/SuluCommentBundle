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

interface CommentInterface
{
    const STATE_UNPUBLISHED = 0;

    const STATE_PUBLISHED = 1;

    public function getId(): int;

    public function getState(): int;

    public function publish(): self;

    public function unpublish(): self;

    public function isPublished(): bool;

    public function getMessage(): string;

    public function setMessage($message): self;

    public function getThread(): ThreadInterface;

    public function setThread(ThreadInterface $thread): self;

    public function getCreatorFullName(): string;

    public function getChangerFullName(): string;
}
