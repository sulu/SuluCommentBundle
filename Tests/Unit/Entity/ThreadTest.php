<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Tests\Unit\Entity;

use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\Thread;

class ThreadTest extends \PHPUnit_Framework_TestCase
{
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

    public function testAddUnPublishedComment()
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
}
