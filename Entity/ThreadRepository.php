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

        return new $className($type, $entityId);
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
    public function findThread($type, $entityId)
    {
        return $this->findOneBy(['type' => $type, 'entityId' => $entityId]);
    }
}
