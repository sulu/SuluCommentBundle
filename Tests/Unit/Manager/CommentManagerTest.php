<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Tests\Unit\Manager;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\CommentRepositoryInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadRepositoryInterface;
use Sulu\Bundle\CommentBundle\Events\CommentEvent;
use Sulu\Bundle\CommentBundle\Events\CommentEventCollector;
use Sulu\Bundle\CommentBundle\Events\Events;
use Sulu\Bundle\CommentBundle\Events\ThreadEvent;
use Sulu\Bundle\CommentBundle\Manager\CommentManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommentManagerTest extends TestCase
{
    use ProphecyTrait;

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

    protected function setUp(): void
    {
        $this->threadRepository = $this->prophesize(ThreadRepositoryInterface::class);
        $this->commentRepository = $this->prophesize(CommentRepositoryInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->commentEventCollector = new CommentEventCollector($this->dispatcher->reveal());

        $this->thread = $this->prophesize(ThreadInterface::class);
        $this->thread->getType()->willReturn('test');
        $this->thread->getEntityId()->willReturn('123-123-123');
        $this->comment = $this->prophesize(CommentInterface::class);

        $this->commentManager = new CommentManager(
            $this->threadRepository->reveal(),
            $this->commentRepository->reveal(),
            $this->dispatcher->reveal(),
            $this->commentEventCollector
        );
    }

    public function testFindComments($type = 'article', $entityId = '123-123-123')
    {
        $this->commentRepository->findComments($type, $entityId, 10, 0)->shouldBeCalled()->willReturn([]);

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

        $this->dispatcher->dispatch(Argument::type(CommentEvent::class), Events::PRE_PERSIST_EVENT)
            ->shouldBeCalledTimes(1)
            ->will(
                function($args) use ($commentRepository, $comment, $dispatcher, $thread) {
                    $thread->addComment($comment->reveal())->willReturn($thread->reveal());
                    $commentRepository->persist($comment->reveal())
                        ->shouldBeCalledTimes(1)
                        ->will(
                            function() use ($dispatcher) {
                                $dispatcher->dispatch(
                                    Argument::type(CommentEvent::class),
                                    Events::POST_PERSIST_EVENT
                                )->shouldBeCalledTimes(1);
                            }
                        );

                    // return the event
                    return $args[0];
                }
            );

        $thread->setTitle(Argument::any())->shouldNotBeCalled();
        $thread = $this->commentManager->addComment($type, $entityId, $this->comment->reveal());
        $this->commentEventCollector->dispatch(); // simulate flush of the comments

        $this->assertEquals($this->thread->reveal(), $thread);
    }

    public function testAddCommentWithThreadTitle($type = 'article', $entityId = '123-123-123')
    {
        $this->threadRepository->findThread($type, $entityId)->willReturn($this->thread->reveal());

        $commentRepository = $this->commentRepository;
        $comment = $this->comment;
        $dispatcher = $this->dispatcher;
        $thread = $this->thread;

        $this->dispatcher->dispatch(Argument::type(CommentEvent::class), Events::PRE_PERSIST_EVENT)
            ->shouldBeCalledTimes(1)
            ->will(
                function($args) use ($commentRepository, $comment, $dispatcher, $thread) {
                    $thread->addComment($comment->reveal())->willReturn($thread->reveal());
                    $commentRepository->persist($comment->reveal())
                        ->shouldBeCalledTimes(1)
                        ->will(
                            function() use ($dispatcher) {
                                $dispatcher->dispatch(
                                    Argument::type(CommentEvent::class),
                                    Events::POST_PERSIST_EVENT
                                )->shouldBeCalledTimes(1);
                            }
                        );

                    // return the event
                    return $args[0];
                }
            );

        $thread->setTitle('Test')->shouldBeCalled();
        $thread = $this->commentManager->addComment($type, $entityId, $this->comment->reveal(), 'Test');
        $this->commentEventCollector->dispatch(); // simulate flush of the comments

        $this->assertEquals($this->thread->reveal(), $thread);
    }

    public function testUpdate()
    {
        $comment = $this->comment;
        $comment->getThread()->willReturn($this->thread->reveal());

        $this->dispatcher->dispatch(
            Argument::that(
                function(CommentEvent $event) use ($comment) {
                    return $event->getComment() === $comment->reveal();
                }
            ),
            Events::PRE_UPDATE_EVENT
        )->shouldBeCalled()
        ->willReturn(new CommentEvent('', '', $this->comment->reveal(), $this->thread->reveal()));

        $this->assertEquals($comment->reveal(), $this->commentManager->update($comment->reveal()));
        $this->commentEventCollector->dispatch(); // simulate flush of the comments
    }

    public function testUpdateThread()
    {
        $thread = $this->thread;

        $this->dispatcher->dispatch(
            Argument::that(
                function(ThreadEvent $event) use ($thread) {
                    return $event->getThread() === $thread->reveal();
                }
            ),
            Events::THREAD_PRE_UPDATE_EVENT
        )->shouldBeCalled()
        ->willReturn(new ThreadEvent($this->thread->reveal()));

        $this->assertEquals($thread->reveal(), $this->commentManager->updateThread($thread->reveal()));
        $this->commentEventCollector->dispatch(); // simulate flush of the comments
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
                Argument::that(
                    function(CommentEvent $event) use ($comment) {
                        return $event->getComment() === $comment->reveal();
                    }
                ),
                Events::PRE_DELETE_EVENT
            )->will(
                function($args) use ($comment, $dispatcher) {
                    $dispatcher->dispatch(
                        Argument::that(
                            function(CommentEvent $event) use ($comment) {
                                return $event->getComment() === $comment->reveal();
                            }
                        ),
                        Events::POST_DELETE_EVENT
                    )->shouldBeCalledTimes(1);

                    // return the event
                    return $args[0];
                }
            )->shouldBeCalledTimes(1);

            $commentsReveal[] = $comment->reveal();
        }

        $this->commentRepository->findCommentsByIds([1, 2, 3])->willReturn($commentsReveal);

        $this->commentManager->delete([1, 2, 3]);
        $this->commentEventCollector->dispatch(); // simulate flush of the comments
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
                Argument::that(
                    function(CommentEvent $event) use ($comment) {
                        return $event->getComment() === $comment->reveal();
                    }
                ),
                Events::PRE_DELETE_EVENT
            )->will(
                function($args) use ($comment, $dispatcher) {
                    $dispatcher->dispatch(
                        Argument::that(
                            function(CommentEvent $event) use ($comment) {
                                return $event->getComment() === $comment->reveal();
                            }
                        ),
                        Events::POST_DELETE_EVENT
                    )->shouldBeCalledTimes(1);

                    // return the event
                    return $args[0];
                }
            )->shouldBeCalledTimes(1);

            $commentsReveal[] = $comment->reveal();
        }

        $this->commentRepository->findCommentsByIds([1])->willReturn($commentsReveal);

        $this->commentManager->delete([1]);
        $this->commentEventCollector->dispatch(); // simulate flush of the comments
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
                Argument::that(
                    function(ThreadEvent $event) use ($thread) {
                        return $event->getThread() === $thread->reveal();
                    }
                ),
                Events::THREAD_PRE_DELETE_EVENT
            )->will(
                function($args) use ($thread, $dispatcher) {
                    $dispatcher->dispatch(
                        Argument::that(
                            function(ThreadEvent $event) use ($thread) {
                                return $event->getThread() === $thread->reveal();
                            }
                        ),
                        Events::THREAD_POST_DELETE_EVENT
                    )->shouldBeCalledTimes(1);

                    // return the event
                    return $args[0];
                }
            )->shouldBeCalledTimes(1);

            $threadsReveal[] = $thread->reveal();
        }

        $this->threadRepository->findThreadsByIds([1, 2, 3])->willReturn($threadsReveal);

        $this->commentManager->deleteThreads([1, 2, 3]);
        $this->commentEventCollector->dispatch(); // simulate flush of the comments
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
                Argument::that(
                    function(ThreadEvent $event) use ($thread) {
                        return $event->getThread() === $thread->reveal();
                    }
                ),
                Events::THREAD_PRE_DELETE_EVENT
            )->will(
                function($args) use ($thread, $dispatcher) {
                    $dispatcher->dispatch(
                        Argument::that(
                            function(ThreadEvent $event) use ($thread) {
                                return $event->getThread() === $thread->reveal();
                            }
                        ),
                        Events::THREAD_POST_DELETE_EVENT
                    )->shouldBeCalledTimes(1);

                    // return the event
                    return $args[0];
                }
            )->shouldBeCalledTimes(1);

            $threadsReveal[] = $thread->reveal();
        }

        $this->threadRepository->findThreadsByIds([1])->willReturn($threadsReveal);

        $this->commentManager->deleteThreads([1]);
        $this->commentEventCollector->dispatch(); // simulate flush of the comments
    }

    public function testPublish()
    {
        $this->comment->isPublished()->willReturn(false);
        $this->comment->publish()->shouldBeCalled();
        $this->comment->getThread()->willReturn($this->thread->reveal());

        $comment = $this->comment;
        $this->dispatcher->dispatch(
            Argument::that(
                function(CommentEvent $event) use ($comment) {
                    return $event->getComment() === $comment->reveal();
                }
            ),
            Events::PUBLISH_EVENT
        )->shouldBeCalledTimes(1)
        ->willReturn(new CommentEvent('', '', $this->comment->reveal(), $this->thread->reveal()));

        $this->assertEquals($this->comment->reveal(), $this->commentManager->publish($this->comment->reveal()));
        $this->commentEventCollector->dispatch(); // simulate flush of the comments
    }

    public function testPublishIsAlreadyPublished()
    {
        $this->comment->isPublished()->willReturn(true);
        $this->comment->publish()->shouldNotBeCalled();

        $comment = $this->comment;
        $this->dispatcher->dispatch(
            Argument::that(
                function(CommentEvent $event) use ($comment) {
                    return $event->getComment() === $comment->reveal();
                }
            ),
            Events::PUBLISH_EVENT
        )->shouldNotBeCalled()
        ->willReturn(new CommentEvent('', '', $this->comment->reveal(), $this->thread->reveal()));

        $this->assertEquals($this->comment->reveal(), $this->commentManager->publish($this->comment->reveal()));
        $this->commentEventCollector->dispatch(); // simulate flush of the comments
    }

    public function testUnpublish()
    {
        $this->comment->isPublished()->willReturn(true);
        $this->comment->unpublish()->shouldBeCalled();
        $this->comment->getThread()->willReturn($this->thread->reveal());

        $comment = $this->comment;
        $this->dispatcher->dispatch(
            Argument::that(
                function(CommentEvent $event) use ($comment) {
                    return $event->getComment() === $comment->reveal();
                }
            ),
            Events::UNPUBLISH_EVENT
        )->shouldBeCalledTimes(1)
        ->willReturn(new CommentEvent('', '', $this->comment->reveal(), $this->thread->reveal()));

        $this->assertEquals($this->comment->reveal(), $this->commentManager->unpublish($this->comment->reveal()));
        $this->commentEventCollector->dispatch(); // simulate flush of the comments
    }

    public function testUnpublishIsAlreadyUnpublished()
    {
        $this->comment->isPublished()->willReturn(false);
        $this->comment->unpublish()->shouldNotBeCalled();

        $comment = $this->comment;
        $this->dispatcher->dispatch(
            Argument::that(
                function(CommentEvent $event) use ($comment) {
                    return $event->getComment() === $comment->reveal();
                }
            ),
            Events::UNPUBLISH_EVENT
        )->shouldNotBeCalled()
        ->willReturn(new CommentEvent('', '', $this->comment->reveal(), $this->thread->reveal()));

        $this->assertEquals($this->comment->reveal(), $this->commentManager->unpublish($this->comment->reveal()));
        $this->commentEventCollector->dispatch(); // simulate flush of the comments
    }
}
