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

use Sulu\Component\Persistence\Repository\RepositoryInterface;

interface CommentRepositoryInterface extends RepositoryInterface
{
    /**
     * @return CommentInterface[]
     */
    public function findComments(string $type, string $entityId, int $page = 1, ?int $pageSize = null): array;

    /**
     * @return CommentInterface[]
     */
    public function findPublishedComments(string $type, string $entityId, int $page = 1, ?int $pageSize = null): array;

    /**
     * @param int[] $ids
     *
     * @return CommentInterface[]
     */
    public function findCommentsByIds(array $ids): array;

    /**
     * @return CommentInterface|null
     */
    public function findCommentById(int $id): ?CommentInterface;

    /**
     * @param CommentInterface $comment
     */
    public function persist(CommentInterface $comment): void;

    /**
     * @param CommentInterface $comment
     */
    public function delete(CommentInterface $comment): void;
}
