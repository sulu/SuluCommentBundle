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

use Sulu\Component\Persistence\Repository\RepositoryInterface;

/**
 * Interface for comment-repository.
 */
interface CommentRepositoryInterface extends RepositoryInterface
{
    /**
     * Returns comments for given thread.
     *
     * @param string $type
     * @param string $entityId
     * @param int $page
     * @param int|null $pageSize
     *
     * @return CommentInterface[]
     */
    public function findComments($type, $entityId, $page = 1, $pageSize = null);

    /**
     * Returns published comments for given thread.
     *
     * @param string $type
     * @param string $entityId
     * @param int $page
     * @param int|null $pageSize
     *
     * @return CommentInterface[]
     */
    public function findPublishedComments($type, $entityId, $page, $pageSize);

    /**
     * Returns comments by given ids.
     *
     * @param int[] $ids
     *
     * @return CommentInterface[]
     */
    public function findCommentsByIds($ids);

    /**
     * Returns comment by given id.
     *
     * @param int $id
     *
     * @return CommentInterface
     */
    public function findCommentById($id);

    /**
     * Persists comment.
     *
     * @param CommentInterface $comment
     */
    public function persist(CommentInterface $comment);

    /**
     * Delete comment.
     *
     * @param CommentInterface $comment
     */
    public function delete(CommentInterface $comment);
}
