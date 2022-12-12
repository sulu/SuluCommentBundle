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

    public function findCommentById(int $id): ?CommentInterface;

    public function persist(CommentInterface $comment): void;

    public function delete(CommentInterface $comment): void;
}
