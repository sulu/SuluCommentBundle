<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Entity;

use Sulu\Component\Persistence\Repository\ORM\EntityRepository;

/**
 * Repository for querying comments.
 */
class CommentRepository extends EntityRepository implements CommentRepositoryInterface
{
    /**
     * @inheritdoc}
     */
    public function findComments($type, $entityId, $page = 1, $pageSize = null)
    {
        $query = $this->createQueryBuilder('c')
            ->join('c.thread', 't')
            ->leftJoin('c.creator', 'creator')
            ->leftJoin('c.changer', 'changer')
            ->where('t.type = :type AND t.entityId = :entityId')
            ->setParameter('type', $type)
            ->setParameter('entityId', $entityId)
            ->orderBy('c.created', 'DESC')
            ->getQuery();

        if ($pageSize) {
            $query->setMaxResults($pageSize);
            $query->setFirstResult(($page - 1) * $pageSize);
        }

        return $query->getResult();
    }

    /**
     * Persists comment.
     *
     * @param CommentInterface $comment
     */
    public function persist(CommentInterface $comment)
    {
        $this->getEntityManager()->persist($comment);
    }
}
