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
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CommentRepository extends NestedTreeRepository implements CommentRepositoryInterface
{
    public function findComments(string $type, string $entityId, int $page = 1, ?int $pageSize = null): array
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

    public function findPublishedComments(string $type, string $entityId, int $page = 1, ?int $pageSize = null): array
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->join('c.thread', 't')
            ->leftJoin('c.creator', 'creator')
            ->leftJoin('c.changer', 'changer')
            ->where('c.state = :state')
            ->andWhere('c.parent IS NULL')
            ->andWhere('t.type = :type AND t.entityId = :entityId')
            ->setParameter('state', CommentInterface::STATE_PUBLISHED)
            ->setParameter('type', $type)
            ->setParameter('entityId', $entityId)
            ->orderBy('c.created', 'DESC');

        $query = $queryBuilder->getQuery();

        if ($pageSize) {
            $query->setMaxResults($pageSize);
            $query->setFirstResult(($page - 1) * $pageSize);
        }

        return $query->getResult();
    }

    public function findCommentsByIds(array $ids): array
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

    public function findCommentById(int $id): ?CommentInterface
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
            return null;
        }
    }

    public function persist(CommentInterface $comment): void
    {
        $this->getEntityManager()->persist($comment);
    }

    public function delete(CommentInterface $comment): void
    {
        $this->getEntityManager()->remove($comment);
    }

    public function createNew()
    {
        $className = $this->getClassName();

        return new $className();
    }
}
