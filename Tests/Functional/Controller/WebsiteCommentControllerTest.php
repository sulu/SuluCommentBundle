<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Functional\Controller;

use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

/**
 * Functional test-cases for website api.
 */
class WebsiteCommentControllerTest extends SuluTestCase
{
    protected function setUp()
    {
        $this->purgeDatabase();
    }

    public function providePostData()
    {
        return [
            [],
            ['article', '123-123-123'],
        ];
    }

    /**
     * @dataProvider providePostData
     */
    public function testPostComment(
        $type = 'blog',
        $entityId = '1',
        $message = 'Sulu is awesome',
        $threadTitle = 'Test Thread'
    ) {
        $client = $this->createWebsiteClient();
        $client->request(
            'POST',
            '_api/threads/' . $type . '-' . $entityId . '/comments',
            ['message' => $message, 'threadTitle' => $threadTitle]
        );

        $this->assertHttpStatusCode(200, $client->getResponse());

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(CommentInterface::STATE_PUBLISHED, $response['state']);
        $this->assertEquals($message, $response['message']);
        $this->assertEquals($threadTitle, $response['thread']['title']);

        $thread = $this->getContainer()->get('sulu.repository.thread')->findThread($type, $entityId);
        $this->assertEquals($type, $thread->getType());
        $this->assertEquals($entityId, $thread->getEntityId());
        $this->assertEquals(1, $thread->getCommentCount());
        $this->assertCount(1, $thread->getComments());

        return $thread;
    }

    public function testPostCommentMultiple($type = 'blog', $entityId = '1')
    {
        $thread1 = $this->testPostComment($type, $entityId);
        $thread2 = $this->testPostComment($type, $entityId);

        $this->assertEquals($thread1->getId(), $thread2->getId());

        return $thread1;
    }

    public function testPostCommentDifferent($type = 'blog', $entityId = '1')
    {
        $thread1 = $this->testPostComment($type, $entityId);
        $thread2 = $this->testPostComment('article', '123-123-123');

        $this->assertNotEquals($thread1->getId(), $thread2->getId());
    }

    public function testGetComments($type = 'blog', $entityId = '1')
    {
        $this->testPostComment($type, $entityId);
        $this->testPostComment($type, $entityId, 'My new Comment');
        $this->testPostComment('article', '123-123-123');

        $client = $this->createWebsiteClient();
        $client->request(
            'GET',
            '_api/threads/' . $type . '-' . $entityId . '/comments'
        );

        $this->assertHttpStatusCode(200, $client->getResponse());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(2, $response);
        $this->assertEquals(CommentInterface::STATE_PUBLISHED, $response[0]['state']);
        $this->assertEquals('Sulu is awesome', $response[0]['message']);
        $this->assertEquals(CommentInterface::STATE_PUBLISHED, $response[1]['state']);
        $this->assertEquals('My new Comment', $response[1]['message']);
    }
}
