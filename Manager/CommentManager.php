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

    public function __construct(
        ThreadRepositoryInterface $threadRepository,
        CommentRepositoryInterface $commentRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->threadRepository = $threadRepository;
        $this->commentRepository = $commentRepository;
        $this->dispatcher = $dispatcher;
    }

    public function findComments(string $type, string $entityId, int $page = 1, ?int $pageSize = null): array
    {
        return $this->commentRepository->findComments($type, $entityId, $page, $pageSize);
    }

    public function findPublishedComments(string $type, string $entityId, int $page = 1, ?int $pageSize = null): array
    {
        return $this->commentRepository->findPublishedComments($type, $entityId, $page, $pageSize);
    }

    public function addComment(
        string $type,
        string $entityId,
        CommentInterface $comment,
        string $threadTitle = null
    ): ThreadInterface {
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

    public function update(CommentInterface $comment): CommentInterface
    {
        $thread = $comment->getThread();
        $this->dispatcher->dispatch(
            Events::PRE_UPDATE_EVENT,
            new CommentEvent($thread->getType(), $thread->getEntityId(), $comment, $thread)
        );

        return $comment;
    }

    public function delete(array $ids): void
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $comments = $this->commentRepository->findCommentsByIds($ids);
        foreach ($comments as $comment) {
            $this->deleteComment($comment);
        }
    }

    public function updateThread(ThreadInterface $thread): ThreadInterface
    {
        $this->dispatcher->dispatch(Events::THREAD_PRE_UPDATE_EVENT, new ThreadEvent($thread));

        return $thread;
    }

    public function deleteThreads(array $ids): void
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $threads = $this->threadRepository->findThreadsByIds($ids);
        foreach ($threads as $thread) {
            $this->deleteThread($thread);
        }
    }

    public function publish(CommentInterface $comment): CommentInterface
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

    public function unpublish(CommentInterface $comment): CommentInterface
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

    private function deleteComment(CommentInterface $comment): void
    {
        $thread = $comment->getThread();
        $preEvent = new CommentEvent($thread->getType(), $thread->getEntityId(), $comment, $thread);
        $this->dispatcher->dispatch(Events::PRE_DELETE_EVENT, $preEvent);

        $thread->removeComment($comment);
        $this->commentRepository->delete($comment);

        $postEvent = new CommentEvent($thread->getType(), $thread->getEntityId(), $comment, $thread);
        $this->dispatcher->dispatch(Events::POST_DELETE_EVENT, $postEvent);
    }

    private function deleteThread(ThreadInterface $thread): void
    {
        $this->dispatcher->dispatch(Events::THREAD_PRE_DELETE_EVENT, new ThreadEvent($thread));

        $this->threadRepository->delete($thread);

        $this->dispatcher->dispatch(Events::THREAD_POST_DELETE_EVENT, new ThreadEvent($thread));
    }
}
