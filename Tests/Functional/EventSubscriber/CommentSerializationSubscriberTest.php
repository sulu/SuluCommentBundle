<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Functional\Controller;

use JMS\Serializer\SerializationContext;
use Sulu\Bundle\CommentBundle\Entity\Comment;
use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

/**
 * Functional test-cases for website api.
 */
class CommentSerializationSubscriberTest extends SuluTestCase
{
    public function testSerializeWithoutGroup()
    {
        $comment = new Comment(CommentInterface::STATE_PUBLISHED);
        $comment->setMessage('test-message');

        $jsonResult = self::getContainer()->get('jms_serializer')->serialize($comment, 'json');
        $arrayResult = \json_decode($jsonResult, true);

        $this->assertTrue($arrayResult['published']);
        $this->assertSame('test-message', $arrayResult['message']);
        $this->assertArrayNotHasKey('creatorId', $arrayResult);
    }

    public function testSerializeWithoutCreator()
    {
        $comment = new Comment(CommentInterface::STATE_PUBLISHED);
        $comment->setMessage('test-message');

        $context = SerializationContext::create()->setGroups(['Default', 'commentWithAvatar']);
        $jsonResult = self::getContainer()->get('jms_serializer')->serialize($comment, 'json', $context);
        $arrayResult = \json_decode($jsonResult, true);

        $this->assertTrue($arrayResult['published']);
        $this->assertSame('test-message', $arrayResult['message']);
        $this->assertArrayNotHasKey('creatorId', $arrayResult);
    }

    public function testSerializeWithCreator()
    {
        $testUser = self::getContainer()->get('test_user_provider')->getUser();
        $comment = new Comment(CommentInterface::STATE_PUBLISHED);
        $comment->setMessage('test-message');
        $comment->setCreator($testUser);

        $context = SerializationContext::create()->setGroups(['Default', 'commentWithAvatar']);
        $jsonResult = self::getContainer()->get('jms_serializer')->serialize($comment, 'json', $context);
        $arrayResult = \json_decode($jsonResult, true);

        $this->assertTrue($arrayResult['published']);
        $this->assertSame('test-message', $arrayResult['message']);
        $this->assertSame($testUser->getId(), $arrayResult['creatorId']);
    }
}
