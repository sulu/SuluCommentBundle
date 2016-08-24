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
 * Interface for comment-repository.
 */
interface CommentRepositoryInterface
{
    /**
     * Returns comments for given thread.
     *
     * @param string $type
     * @param string $entityId
     * @param int $page
     * @param int|null $pageSize
     *
     * @return Comment[]
     */
    public function findComments($type, $entityId, $page = 1, $pageSize = null);
}
