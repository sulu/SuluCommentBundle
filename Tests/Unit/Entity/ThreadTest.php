<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\Thread;

class ThreadTest extends TestCase
{
    use ProphecyTrait;

    public function testCommentCount()
    {
        $thread = new Thread('test', 1);
        $this->assertEquals(0, $thread->getCommentCount());

        $thread->increaseCommentCount();
        $this->assertEquals(1, $thread->getCommentCount());

        $thread->decreaseCommentCount();
        $this->assertEquals(0, $thread->getCommentCount());

        $thread->setCommentCount(5);
        $this->assertEquals(5, $thread->getCommentCount());
    }

    public function testAddPublishedComment()
    {
        $comment = $this->prophesize(CommentInterface::class);
        $thread = new Thread('test', 1);

        $comment->setThread($thread)->shouldBeCalled();
        $comment->isPublished()->willReturn(true);

        $thread->addComment($comment->reveal());
        $this->assertEquals($comment->reveal(), $thread->getComments()->first());
        $this->assertEquals(1, $thread->getCommentCount());
        $this->assertCount(1, $thread->getComments());
    }

    public function testAddUnpublishedComment()
    {
        $comment = $this->prophesize(CommentInterface::class);
        $thread = new Thread('test', 1);

        $comment->setThread($thread)->shouldBeCalled();
        $comment->isPublished()->willReturn(false);

        $thread->addComment($comment->reveal());
        $this->assertEquals($comment->reveal(), $thread->getComments()->first());
        $this->assertEquals(0, $thread->getCommentCount());
        $this->assertCount(1, $thread->getComments());
    }

    public function testRemovePublishedComment()
    {
        $comment = $this->prophesize(CommentInterface::class);
        $thread = new Thread('test', 1, new ArrayCollection([$comment->reveal()]), 1);

        $comment->isPublished()->willReturn(true);

        $thread->removeComment($comment->reveal());
        $this->assertEquals(0, $thread->getCommentCount());
        $this->assertCount(0, $thread->getComments());
    }

    public function testRemoveUnpublishedComment()
    {
        $comment1 = $this->prophesize(CommentInterface::class);
        $comment2 = $this->prophesize(CommentInterface::class);
        $thread = new Thread('test', 1, new ArrayCollection([$comment1->reveal(), $comment2->reveal()]), 1);

        $comment1->isPublished()->willReturn(false);

        $thread->removeComment($comment1->reveal());
        $this->assertEquals(1, $thread->getCommentCount());
        $this->assertCount(1, $thread->getComments());
    }
}
