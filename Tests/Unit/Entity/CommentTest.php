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

use Sulu\Bundle\CommentBundle\Entity\Comment;
use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\Thread;

class CommentTest extends \PHPUnit_Framework_TestCase
{
    public function testPublish()
    {
        $thread = new Thread('test', 1);
        $comment = new Comment(CommentInterface::STATE_UNPUBLISHED, $thread);

        $this->assertEquals(CommentInterface::STATE_UNPUBLISHED, $comment->getState());
        $this->assertFalse($comment->isPublished());
        $this->assertEquals(0, $thread->getCommentCount());

        $comment->publish();
        $this->assertEquals(CommentInterface::STATE_PUBLISHED, $comment->getState());
        $this->assertTrue($comment->isPublished());
        $this->assertEquals(1, $thread->getCommentCount());

        $comment->unpublish();
        $this->assertEquals(CommentInterface::STATE_UNPUBLISHED, $comment->getState());
        $this->assertFalse($comment->isPublished());
        $this->assertEquals(0, $thread->getCommentCount());
    }
}
