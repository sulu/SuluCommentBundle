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

use Doctrine\ORM\NoResultException;
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
     * @inheritdoc}
     */
    public function findPublishedComments($type, $entityId, $page, $pageSize)
    {
        $query = $this->createQueryBuilder('c')
            ->join('c.thread', 't')
            ->leftJoin('c.creator', 'creator')
            ->leftJoin('c.changer', 'changer')
            ->where('c.state = :state')
            ->andWhere('t.type = :type AND t.entityId = :entityId')
            ->setParameter('state', CommentInterface::STATE_PUBLISHED)
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
     * @inheritdoc}
     */
    public function findCommentsByIds($ids)
    {
        $query = $this->createQueryBuilder('c')
            ->join('c.thread', 't')
            ->leftJoin('c.creator', 'creator')
            ->leftJoin('c.changer', 'changer')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @inheritdoc}
     */
    public function findCommentById($id)
    {
        $query = $this->createQueryBuilder('c')
            ->join('c.thread', 't')
            ->leftJoin('c.creator', 'creator')
            ->leftJoin('c.changer', 'changer')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        try {
            return $query->getSingleResult();
        } catch (NoResultException $e) {
            return;
        }
    }

    /**
     * @inheritdoc}
     */
    public function persist(CommentInterface $comment)
    {
        $this->getEntityManager()->persist($comment);
    }

    /**
     * @inheritdoc}
     */
    public function delete(CommentInterface $comment)
    {
        $this->getEntityManager()->remove($comment);
    }
}
