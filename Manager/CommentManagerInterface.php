<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Manager;

use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;

/**
 * Interface for comment-manager.
 */
interface CommentManagerInterface
{
    /**
     * Returns comments for given thread.
     *
     * @param string $type
     * @param string $entityId
     * @param int $page
     * @param int|null $pageSize
     *
     * @return ThreadInterface[]
     */
    public function findComments($type, $entityId, $page = 1, $pageSize = null);

    /**
     * Add comment and returns thread.
     *
     * @param string $type
     * @param string $entityId
     * @param CommentInterface $comment
     *
     * @return ThreadInterface
     */
    public function addComment($type, $entityId, CommentInterface $comment);
}
