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

use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<ThreadInterface>
 */
class ThreadRepository extends EntityRepository implements ThreadRepositoryInterface
{
    public function createNew(string $type, string $entityId): ThreadInterface
    {
        $className = $this->getClassName();

        $thread = new $className($type, $entityId);
        $this->getEntityManager()->persist($thread);

        return $thread;
    }

    public function findThreadById(int $id): ?ThreadInterface
    {
        /** @var ThreadInterface|null $thread */
        $thread = $this->find($id);

        return $thread;
    }

    public function findThreadsByIds(array $ids): array
    {
        $query = $this->createQueryBuilder('t')
            ->leftJoin('t.comments', 'c')
            ->leftJoin('t.creator', 'creator')
            ->leftJoin('t.changer', 'changer')
            ->where('t.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery();

        /** @var ThreadInterface[] */
        return $query->getResult();
    }

    public function findThread(string $type, string $entityId): ?ThreadInterface
    {
        /** @var ThreadInterface|null $thread */
        $thread = $this->findOneBy(['type' => $type, 'entityId' => $entityId]);

        return $thread;
    }

    public function delete(ThreadInterface $thread): void
    {
        $this->getEntityManager()->remove($thread);
    }
}
