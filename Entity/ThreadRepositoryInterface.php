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

/**
 * Interface for thread-repository.
 */
interface ThreadRepositoryInterface
{
    /**
     * Create a new thread for given type and entity-id.
     *
     * @param string $type
     * @param string $entityId
     *
     * @return ThreadInterface
     */
    public function createNew($type, $entityId);

    /**
     * Returns thread for given id.
     *
     * @param int $id
     *
     * @return ThreadInterface
     */
    public function findThreadById($id);

    /**
     * Returns thread for given type and entity-id.
     *
     * @param string $type
     * @param string $entityId
     *
     * @return ThreadInterface
     */
    public function findThread($type, $entityId);
}
