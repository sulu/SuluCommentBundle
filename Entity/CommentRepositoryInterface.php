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

/**
 * @extends RepositoryInterface<CommentInterface>
 */
interface CommentRepositoryInterface extends RepositoryInterface
{
    /**
     * @return CommentInterface[]
     */
    public function findComments(string $type, string $entityId, int $limit = 10, int $offset = 0): array;

    /**
     * @return CommentInterface[]
     */
    public function findPublishedComments(string $type, string $entityId, int $limit = 10, int $offset = 0): array;

    public function countPublishedComments(string $type, string $entityId): int;

    /**
     * @param int[] $ids
     *
     * @return CommentInterface[]
     */
    public function findCommentsByIds(array $ids): array;

    public function findCommentById(int $id): ?CommentInterface;

    public function persist(CommentInterface $comment): void;

    public function delete(CommentInterface $comment): void;
}
