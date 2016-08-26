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
}
