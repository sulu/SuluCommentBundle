<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Manager;

use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\CommentRepositoryInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadRepositoryInterface;
use Sulu\Bundle\CommentBundle\Events\CommentEvent;
use Sulu\Bundle\CommentBundle\Events\Events;
use Sulu\Bundle\CommentBundle\Events\ThreadEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param ThreadRepositoryInterface $threadRepository
     * @param CommentRepositoryInterface $commentRepository
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ThreadRepositoryInterface $threadRepository,
        CommentRepositoryInterface $commentRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->threadRepository = $threadRepository;
        $this->commentRepository = $commentRepository;
        $this->dispatcher = $dispatcher;
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
    public function findPublishedComments($type, $entityId, $page = 1, $pageSize = null)
    {
        return $this->commentRepository->findPublishedComments($type, $entityId, $page, $pageSize);
    }

    /**
     * {@inheritdoc}
     */
    public function addComment($type, $entityId, CommentInterface $comment, $threadTitle = null)
    {
        $thread = $this->threadRepository->findThread($type, $entityId);
        if (!$thread) {
            $thread = $this->threadRepository->createNew($type, $entityId);
        }

        if ($threadTitle) {
            $thread->setTitle($threadTitle);
        }

        $this->dispatcher->dispatch(Events::PRE_PERSIST_EVENT, new CommentEvent($type, $entityId, $comment, $thread));
        $this->commentRepository->persist($comment);
        $thread = $thread->addComment($comment);
        $this->dispatcher->dispatch(Events::POST_PERSIST_EVENT, new CommentEvent($type, $entityId, $comment, $thread));

        return $thread;
    }

    /**
     * {@inheritdoc}
     */
    public function update(CommentInterface $comment)
    {
        $thread = $comment->getThread();
        $this->dispatcher->dispatch(
            Events::PRE_UPDATE_EVENT,
            new CommentEvent($thread->getType(), $thread->getEntityId(), $comment, $thread)
        );

        return $comment;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $comments = $this->commentRepository->findCommentsByIds($ids);
        foreach ($comments as $comment) {
            $this->deleteComment($comment);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateThread(ThreadInterface $thread)
    {
        $this->dispatcher->dispatch(Events::THREAD_PRE_UPDATE_EVENT, new ThreadEvent($thread));

        return $thread;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteThreads($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $threads = $this->threadRepository->findThreadsByIds($ids);
        foreach ($threads as $thread) {
            $this->deleteThread($thread);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function publish(CommentInterface $comment)
    {
        if ($comment->isPublished()) {
            return $comment;
        }

        $comment->publish();

        $thread = $comment->getThread();
        $this->dispatcher->dispatch(
            Events::PUBLISH_EVENT,
            new CommentEvent($thread->getType(), $thread->getEntityId(), $comment, $thread)
        );

        return $comment;
    }

    /**
     * {@inheritdoc}
     */
    public function unpublish(CommentInterface $comment)
    {
        if (!$comment->isPublished()) {
            return $comment;
        }

        $comment->unpublish();

        $thread = $comment->getThread();
        $this->dispatcher->dispatch(
            Events::UNPUBLISH_EVENT,
            new CommentEvent($thread->getType(), $thread->getEntityId(), $comment, $thread)
        );

        return $comment;
    }

    /**
     * Delete comment and raise pre/post events.
     *
     * @param CommentInterface $comment
     */
    private function deleteComment(CommentInterface $comment)
    {
        $thread = $comment->getThread();
        $preEvent = new CommentEvent($thread->getType(), $thread->getEntityId(), $comment, $thread);
        $this->dispatcher->dispatch(Events::PRE_DELETE_EVENT, $preEvent);

        $thread->removeComment($comment);
        $this->commentRepository->delete($comment);

        $postEvent = new CommentEvent($thread->getType(), $thread->getEntityId(), $comment, $thread);
        $this->dispatcher->dispatch(Events::POST_DELETE_EVENT, $postEvent);
    }

    /**
     * Delete thread and raise pre/post events.
     *
     * @param ThreadInterface $thread
     */
    private function deleteThread(ThreadInterface $thread)
    {
        $this->dispatcher->dispatch(Events::THREAD_PRE_DELETE_EVENT, new ThreadEvent($thread));

        $this->threadRepository->delete($thread);

        $this->dispatcher->dispatch(Events::THREAD_POST_DELETE_EVENT, new ThreadEvent($thread));
    }
}
