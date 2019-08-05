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

interface ThreadRepositoryInterface
{
    public function createNew(string $type, string $entityId): ThreadInterface;

    public function findThreadById(int $id): ?ThreadInterface;

    /**
     * @param int[] $ids
     *
     * @return ThreadInterface[]
     */
    public function findThreadsByIds(array $ids): array;

    public function findThread(string $type, string $entityId): ?ThreadInterface;

    public function delete(ThreadInterface $thread): void;
}
