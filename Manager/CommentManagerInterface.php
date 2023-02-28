<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Manager;

use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;

interface CommentManagerInterface
{
    /**
     * @return CommentInterface[]
     */
    public function findComments(string $type, string $entityId, int $page = 1, ?int $pageSize = null): array;

    /**
     * @return CommentInterface[]
     */
    public function findPublishedComments(string $type, string $entityId, int $page = 1, ?int $pageSize = null): array;

    public function countPublishedComments(string $type, string $entityId): int;

    public function addComment(
        string $type,
        string $entityId,
        CommentInterface $comment,
        ?string $threadTitle = null
    ): ThreadInterface;

    public function update(CommentInterface $comment): CommentInterface;

    /**
     * @param int[] $ids
     */
    public function delete(array $ids): void;

    public function updateThread(ThreadInterface $thread): ThreadInterface;

    /**
     * @param int[] $ids
     */
    public function deleteThreads(array $ids): void;

    public function publish(CommentInterface $comment): CommentInterface;

    public function unpublish(CommentInterface $comment): CommentInterface;
}
