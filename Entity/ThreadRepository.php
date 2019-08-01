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
 * Repository for querying comments.
 */
class ThreadRepository extends EntityRepository implements ThreadRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createNew($type, $entityId)
    {
        $className = $this->getClassName();

        $thread = new $className($type, $entityId);
        $this->getEntityManager()->persist($thread);

        return $thread;
    }

    /**
     * {@inheritdoc}
     */
    public function findThreadById($id)
    {
        return $this->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findThreadsByIds($ids)
    {
        $query = $this->createQueryBuilder('t')
            ->leftJoin('t.comments', 'c')
            ->leftJoin('t.creator', 'creator')
            ->leftJoin('t.changer', 'changer')
            ->where('t.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findThread($type, $entityId)
    {
        return $this->findOneBy(['type' => $type, 'entityId' => $entityId]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ThreadInterface $thread)
    {
        $this->getEntityManager()->remove($thread);
    }
}
