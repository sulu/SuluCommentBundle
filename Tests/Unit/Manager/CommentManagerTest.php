<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Tests\Unit\Manager;

use Prophecy\Argument;
use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\CommentRepositoryInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadRepositoryInterface;
use Sulu\Bundle\CommentBundle\Events\CommentEvent;
use Sulu\Bundle\CommentBundle\Events\Events;
use Sulu\Bundle\CommentBundle\Events\ThreadEvent;
use Sulu\Bundle\CommentBundle\Manager\CommentManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommentManagerTest extends \PHPUnit_Framework_TestCase
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
     * @var CommentManager
     */
    private $commentManager;

    /**
     * @var ThreadInterface
     */
    private $thread;

    /**
     * @var CommentInterface
     */
    private $comment;

    protected function setUp()
    {
        $this->threadRepository = $this->prophesize(ThreadRepositoryInterface::class);
        $this->commentRepository = $this->prophesize(CommentRepositoryInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->thread = $this->prophesize(ThreadInterface::class);
        $this->comment = $this->prophesize(CommentInterface::class);

        $this->commentManager = new CommentManager(
            $this->threadRepository->reveal(),
            $this->commentRepository->reveal(),
            $this->dispatcher->reveal()
        );
    }

    public function testFindComments($type = 'article', $entityId = '123-123-123')
    {
        $this->commentRepository->findComments($type, $entityId, 1, null)->shouldBeCalled()->willReturn([]);

        $comments = $this->commentManager->findComments($type, $entityId);
        $this->assertEquals([], $comments);
    }

    public function testAddComment($type = 'article', $entityId = '123-123-123')
    {
        $this->threadRepository->findThread($type, $entityId)->willReturn($this->thread->reveal());

        $commentRepository = $this->commentRepository;
        $comment = $this->comment;
        $dispatcher = $this->dispatcher;
        $thread = $this->thread;

        $this->dispatcher->dispatch(Events::PRE_PERSIST_EVENT, Argument::type(CommentEvent::class))
            ->shouldBeCalledTimes(1)
            ->will(
                function () use ($commentRepository, $comment, $dispatcher, $thread) {
                    $thread->addComment($comment->reveal())->willReturn($thread->reveal());
                    $commentRepository->persist($comment->reveal())
                        ->shouldBeCalledTimes(1)
                        ->will(
                            function () use ($dispatcher, $thread) {
                                $dispatcher->dispatch(
                                    Events::POST_PERSIST_EVENT,
                                    Argument::type(CommentEvent::class)
                                )->shouldBeCalledTimes(1);

                                return $thread->reveal();
                            }
                        );
                }
            );

        $thread = $this->commentManager->addComment($type, $entityId, $this->comment->reveal());

        $this->assertEquals($this->thread->reveal(), $thread);
    }

    public function testUpdate()
    {
        $comment = $this->comment;
        $comment->getThread()->willReturn($this->thread->reveal());

        $this->dispatcher->dispatch(
            Events::PRE_UPDATE_EVENT,
            Argument::that(
                function (CommentEvent $event) use ($comment) {
                    return $event->getComment() === $comment->reveal();
                }
            )
        )->shouldBeCalled();

        $this->assertEquals($comment->reveal(), $this->commentManager->update($comment->reveal()));
    }

    public function testUpdateThread()
    {
        $thread = $this->thread;

        $this->dispatcher->dispatch(
            Events::THREAD_PRE_UPDATE_EVENT,
            Argument::that(
                function (ThreadEvent $event) use ($thread) {
                    return $event->getThread() === $thread->reveal();
                }
            )
        )->shouldBeCalled();

        $this->assertEquals($thread->reveal(), $this->commentManager->updateThread($thread->reveal()));
    }

    public function testDelete()
    {
        $dispatcher = $this->dispatcher;

        $comments = [
            $this->prophesize(CommentInterface::class),
            $this->prophesize(CommentInterface::class),
            $this->prophesize(CommentInterface::class),
        ];
        $commentsReveal = [];

        $this->thread->getType()->willReturn('Test');
        $this->thread->getEntityId()->willReturn('123-123-123');

        foreach ($comments as $comment) {
            $comment->getThread()->willReturn($this->thread->reveal());
            $this->thread->removeComment($comment->reveal())->shouldBeCalled();
            $this->commentRepository->delete($comment->reveal())->shouldBeCalled();

            $this->dispatcher->dispatch(
                Events::PRE_DELETE_EVENT,
                Argument::that(
                    function (CommentEvent $event) use ($comment) {
                        return $event->getComment() === $comment->reveal();
                    }
                )
            )->will(
                function () use ($comment, $dispatcher) {
                    $dispatcher->dispatch(
                        Events::POST_DELETE_EVENT,
                        Argument::that(
                            function (CommentEvent $event) use ($comment) {
                                return $event->getComment() === $comment->reveal();
                            }
                        )
                    )->shouldBeCalledTimes(1);
                }
            )->shouldBeCalledTimes(1);

            $commentsReveal[] = $comment->reveal();
        }

        $this->commentRepository->findCommentsByIds([1, 2, 3])->willReturn($commentsReveal);

        $this->commentManager->delete([1, 2, 3]);
    }

    public function testDeleteOne()
    {
        $dispatcher = $this->dispatcher;

        $comments = [
            $this->prophesize(CommentInterface::class),
        ];
        $commentsReveal = [];

        $this->thread->getType()->willReturn('Test');
        $this->thread->getEntityId()->willReturn('123-123-123');

        foreach ($comments as $comment) {
            $comment->getThread()->willReturn($this->thread->reveal());
            $this->thread->removeComment($comment->reveal())->shouldBeCalled();
            $this->commentRepository->delete($comment->reveal())->shouldBeCalled();

            $this->dispatcher->dispatch(
                Events::PRE_DELETE_EVENT,
                Argument::that(
                    function (CommentEvent $event) use ($comment) {
                        return $event->getComment() === $comment->reveal();
                    }
                )
            )->will(
                function () use ($comment, $dispatcher) {
                    $dispatcher->dispatch(
                        Events::POST_DELETE_EVENT,
                        Argument::that(
                            function (CommentEvent $event) use ($comment) {
                                return $event->getComment() === $comment->reveal();
                            }
                        )
                    )->shouldBeCalledTimes(1);
                }
            )->shouldBeCalledTimes(1);

            $commentsReveal[] = $comment->reveal();
        }

        $this->commentRepository->findCommentsByIds([1])->willReturn($commentsReveal);

        $this->commentManager->delete(1);
    }

    public function testDeleteThread()
    {
        $dispatcher = $this->dispatcher;

        $threads = [
            $this->prophesize(ThreadInterface::class),
            $this->prophesize(ThreadInterface::class),
            $this->prophesize(ThreadInterface::class),
        ];
        $threadsReveal = [];

        foreach ($threads as $thread) {
            $this->threadRepository->delete($thread->reveal())->shouldBeCalled();

            $this->dispatcher->dispatch(
                Events::THREAD_PRE_DELETE_EVENT,
                Argument::that(
                    function (ThreadEvent $event) use ($thread) {
                        return $event->getThread() === $thread->reveal();
                    }
                )
            )->will(
                function () use ($thread, $dispatcher) {
                    $dispatcher->dispatch(
                        Events::THREAD_POST_DELETE_EVENT,
                        Argument::that(
                            function (ThreadEvent $event) use ($thread) {
                                return $event->getThread() === $thread->reveal();
                            }
                        )
                    )->shouldBeCalledTimes(1);
                }
            )->shouldBeCalledTimes(1);

            $threadsReveal[] = $thread->reveal();
        }

        $this->threadRepository->findThreadsByIds([1, 2, 3])->willReturn($threadsReveal);

        $this->commentManager->deleteThreads([1, 2, 3]);
    }

    public function testDeleteThreadOne()
    {
        $dispatcher = $this->dispatcher;

        $threads = [
            $this->prophesize(ThreadInterface::class),
        ];
        $threadsReveal = [];

        foreach ($threads as $thread) {
            $this->threadRepository->delete($thread->reveal())->shouldBeCalled();

            $this->dispatcher->dispatch(
                Events::THREAD_PRE_DELETE_EVENT,
                Argument::that(
                    function (ThreadEvent $event) use ($thread) {
                        return $event->getThread() === $thread->reveal();
                    }
                )
            )->will(
                function () use ($thread, $dispatcher) {
                    $dispatcher->dispatch(
                        Events::THREAD_POST_DELETE_EVENT,
                        Argument::that(
                            function (ThreadEvent $event) use ($thread) {
                                return $event->getThread() === $thread->reveal();
                            }
                        )
                    )->shouldBeCalledTimes(1);
                }
            )->shouldBeCalledTimes(1);

            $threadsReveal[] = $thread->reveal();
        }

        $this->threadRepository->findThreadsByIds([1])->willReturn($threadsReveal);

        $this->commentManager->deleteThreads(1);
    }

    public function testPublish()
    {
        $this->comment->isPublished()->willReturn(false);
        $this->comment->publish()->shouldBeCalled();
        $this->comment->getThread()->willReturn($this->thread->reveal());

        $comment = $this->comment;
        $this->dispatcher->dispatch(
            Events::PUBLISH_EVENT,
            Argument::that(
                function (CommentEvent $event) use ($comment) {
                    return $event->getComment() === $comment->reveal();
                }
            )
        )->shouldBeCalledTimes(1);

        $this->assertEquals($this->comment->reveal(), $this->commentManager->publish($this->comment->reveal()));
    }

    public function testPublishIsAlreadyPublished()
    {
        $this->comment->isPublished()->willReturn(true);
        $this->comment->publish()->shouldNotBeCalled();

        $comment = $this->comment;
        $this->dispatcher->dispatch(
            Events::PUBLISH_EVENT,
            Argument::that(
                function (CommentEvent $event) use ($comment) {
                    return $event->getComment() === $comment->reveal();
                }
            )
        )->shouldNotBeCalled();

        $this->assertEquals($this->comment->reveal(), $this->commentManager->publish($this->comment->reveal()));
    }

    public function testUnpublish()
    {
        $this->comment->isPublished()->willReturn(true);
        $this->comment->unpublish()->shouldBeCalled();
        $this->comment->getThread()->willReturn($this->thread->reveal());

        $comment = $this->comment;
        $this->dispatcher->dispatch(
            Events::UNPUBLISH_EVENT,
            Argument::that(
                function (CommentEvent $event) use ($comment) {
                    return $event->getComment() === $comment->reveal();
                }
            )
        )->shouldBeCalledTimes(1);

        $this->assertEquals($this->comment->reveal(), $this->commentManager->unpublish($this->comment->reveal()));
    }

    public function testUnpublishIsAlreadyUnpublished()
    {
        $this->comment->isPublished()->willReturn(false);
        $this->comment->unpublish()->shouldNotBeCalled();

        $comment = $this->comment;
        $this->dispatcher->dispatch(
            Events::UNPUBLISH_EVENT,
            Argument::that(
                function (CommentEvent $event) use ($comment) {
                    return $event->getComment() === $comment->reveal();
                }
            )
        )->shouldNotBeCalled();

        $this->assertEquals($this->comment->reveal(), $this->commentManager->unpublish($this->comment->reveal()));
    }
}
