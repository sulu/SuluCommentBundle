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
use Sulu\Bundle\CommentBundle\Entity\CommentRepositoryInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadRepositoryInterface;

/**
 * Manager to interact with comments.
 */
class CommentManager implements CommentManagerInterface
{
    /**
     * @var ThreadRepositoryInterface
     */
    private $threadRepository;

    /**
     * @var CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * @param ThreadRepositoryInterface $threadRepository
     * @param CommentRepositoryInterface $commentRepository
     */
    public function __construct(
        ThreadRepositoryInterface $threadRepository,
        CommentRepositoryInterface $commentRepository
    ) {
        $this->threadRepository = $threadRepository;
        $this->commentRepository = $commentRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findComments($type, $entityId, $page = 1, $pageSize = null)
    {
        return $this->commentRepository->findComments($type, $entityId, $page, $pageSize);
    }

    /**
     * {@inheritdoc}
     */
    public function addComment($type, $entityId, CommentInterface $comment)
    {
        $thread = $this->threadRepository->findThread($type, $entityId);
        if (!$thread) {
            $thread = $this->threadRepository->createNew($type, $entityId);
        }

        return $thread->addComment($comment);
    }
}
