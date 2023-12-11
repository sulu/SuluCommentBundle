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

use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Functional test-cases for website api.
 */
class WebsiteCommentControllerTest extends SuluTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createAuthenticatedWebsiteClient();
        $this->purgeDatabase();
        $this->initPhpcr();
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
        $this->client->request(
            'POST',
            '_api/threads/' . $type . '-' . $entityId . '/comments.json',
            ['message' => $message, 'threadTitle' => $threadTitle]
        );

        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $response = \json_decode($this->client->getResponse()->getContent(), true);

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

    /**
     * @dataProvider providePostData
     */
    public function testPostCommentWithParent(
        $type = 'blog',
        $entityId = '1',
        $message = 'Sulu is awesome',
        $threadTitle = 'Test Thread'
    ) {
        /** @var ThreadInterface $thread */
        $thread = $this->postComment($type, $entityId);

        /** @var CommentInterface $parent */
        $parent = $thread->getComments()->first();

        $this->client->request(
            'POST',
            '_api/threads/' . $type . '-' . $entityId . '/comments.json?parent=' . $parent->getId(),
            ['message' => $message, 'threadTitle' => $threadTitle]
        );

        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $response = \json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(CommentInterface::STATE_PUBLISHED, $response['state']);
        $this->assertEquals($message, $response['message']);
        $this->assertEquals($threadTitle, $response['thread']['title']);
        $this->assertEquals(2, $response['thread']['commentCount']);

        $this->getContainer()->get('doctrine.orm.default_entity_manager')->clear();
        $thread = $this->getContainer()->get('sulu.repository.thread')->findThread($type, $entityId);
        $this->assertEquals($type, $thread->getType());
        $this->assertEquals($entityId, $thread->getEntityId());
        $this->assertEquals(2, $thread->getCommentCount());
        $this->assertCount(2, $thread->getComments());

        $comment1 = $thread->getComments()->first();
        $comment2 = $thread->getComments()->last();
        $this->assertEquals($parent->getId(), $comment1->getId());
        $this->assertEquals($parent->getId(), $comment2->getParent()->getId());

        return $thread;
    }

    public function testPostCommentWithReferrer(
        $type = 'blog',
        $entityId = '1',
        $message = 'Sulu is awesome',
        $threadTitle = 'Test Thread'
    ) {
        $this->client->request(
            'POST',
            '_api/threads/' . $type . '-' . $entityId . '/comments?referrer=https://sulu.io',
            ['message' => $message, 'threadTitle' => $threadTitle]
        );

        /** @var RedirectResponse $response */
        $response = $this->client->getResponse();
        $this->assertHttpStatusCode(302, $response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://sulu.io', $response->getTargetUrl());

        $thread = $this->getContainer()->get('sulu.repository.thread')->findThread($type, $entityId);
        $this->assertEquals($type, $thread->getType());
        $this->assertEquals($entityId, $thread->getEntityId());
        $this->assertEquals(1, $thread->getCommentCount());
        $this->assertCount(1, $thread->getComments());
    }

    public function providePostAuditableData()
    {
        return [
            ['created'],
            ['creator'],
            ['changed'],
            ['changer'],
        ];
    }

    /**
     * @dataProvider providePostAuditableData
     */
    public function testPostAuditable($field, $type = 'blog', $entityId = '1')
    {
        $this->client->request(
            'POST',
            '_api/threads/' . $type . '-' . $entityId . '/comments.json',
            ['message' => 'Sulu is awesome', 'threadTitle' => 'Test Thread', $field => 1]
        );

        $this->assertHttpStatusCode(400, $this->client->getResponse());
    }

    public function testPostCommentMultiple($type = 'blog', $entityId = '1')
    {
        $thread1 = $this->postComment($type, $entityId);
        $thread2 = $this->postComment($type, $entityId);

        $this->assertEquals($thread1->getId(), $thread2->getId());

        return $thread1;
    }

    public function testPostCommentDifferent($type = 'blog', $entityId = '1')
    {
        $thread1 = $this->postComment($type, $entityId);
        $thread2 = $this->postComment('article', '123-123-123');

        $this->assertNotEquals($thread1->getId(), $thread2->getId());
    }

    public function testPutComment($type = 'blog', $entityId = '1')
    {
        $thread = $this->postComment($type, $entityId);
        $comment = $thread->getComments()->first();

        $this->client->request(
            'POST',
            '_api/threads/' . $type . '-' . $entityId . '/comments/' . $comment->getId() . '.json',
            ['message' => 'New message']
        );

        $response = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('New message', $response['message']);

        $this->getEntityManager()->clear();

        $thread = $this->getContainer()->get('sulu.repository.thread')->findThread($type, $entityId);
        $this->assertEquals('New message', $thread->getComments()->first()->getMessage());
    }

    public function testPutCommentWithReferrer($type = 'blog', $entityId = '1')
    {
        $thread = $this->postComment($type, $entityId);
        $comment = $thread->getComments()->first();

        $this->client->request(
            'POST',
            '_api/threads/' . $type . '-' . $entityId . '/comments/' . $comment->getId() . '?referrer=https://sulu.io',
            ['message' => 'New message']
        );

        /** @var RedirectResponse $response */
        $response = $this->client->getResponse();
        $this->assertHttpStatusCode(302, $response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://sulu.io', $response->getTargetUrl());

        $this->getEntityManager()->clear();

        $thread = $this->getContainer()->get('sulu.repository.thread')->findThread($type, $entityId);
        $this->assertEquals('New message', $thread->getComments()->first()->getMessage());
    }

    public function testDeleteComment($type = 'blog', $entityId = '1')
    {
        $thread = $this->postComment($type, $entityId);
        $comment = $thread->getComments()->first();

        $this->client->request(
            'DELETE',
            '_api/threads/' . $type . '-' . $entityId . '/comments/' . $comment->getId() . '.json'
        );

        /* Todo: Check if this is correct. */
        $response = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('', $response);

        $this->getEntityManager()->clear();

        $thread = $this->getContainer()->get('sulu.repository.thread')->findThread($type, $entityId);
        $this->assertEquals(0, $thread->getComments()->count());
    }

    public function testDeleteCommitWithReferrer($type = 'blog', $entityId = '1')
    {
        $thread = $this->postComment($type, $entityId);
        $comment = $thread->getComments()->first();

        $this->client->request(
            'DELETE',
            '_api/threads/' . $type . '-' . $entityId . '/comments/' . $comment->getId() . '?referrer=https://sulu.io'
        );

        /** @var RedirectResponse $response */
        $response = $this->client->getResponse();
        $this->assertHttpStatusCode(302, $response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://sulu.io', $response->getTargetUrl());

        $this->getEntityManager()->clear();

        $thread = $this->getContainer()->get('sulu.repository.thread')->findThread($type, $entityId);
        $this->assertEquals(0, $thread->getComments()->count());
    }

    public function testGetComments($type = 'blog', $entityId = '1')
    {
        $this->postComment($type, $entityId);
        \sleep(1);
        $this->postComment($type, $entityId, 'My new Comment');
        $this->postComment('article', '123-123-123');

        $this->client->request(
            'GET',
            '_api/threads/' . $type . '-' . $entityId . '/comments.json'
        );

        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $response = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $response);
        $this->assertEquals(CommentInterface::STATE_PUBLISHED, $response[0]['state']);
        $this->assertEquals('My new Comment', $response[0]['message']);
        $this->assertEquals(CommentInterface::STATE_PUBLISHED, $response[1]['state']);
        $this->assertEquals('Sulu is awesome', $response[1]['message']);
    }

    public function testGetCommentsHtmlThreadTitle()
    {
        $crawler = $this->client->request(
            'GET',
            '/'
        );

        $input = $crawler->filter('input[type="hidden"]');

        $this->assertSame('threadTitle', $input->attr('id'));
        $this->assertSame('hidden', $input->attr('type'));
        $this->assertSame('This is my title', $input->attr('value'));
    }

    private function postComment(
        $type = 'blog',
        $entityId = '1',
        $message = 'Sulu is awesome',
        $threadTitle = 'Test Thread'
    ) {
        $this->client->request(
            'POST',
            '_api/threads/' . $type . '-' . $entityId . '/comments.json',
            ['message' => $message, 'threadTitle' => $threadTitle]
        );

        $thread = $this->getContainer()->get('sulu.repository.thread')->findThread($type, $entityId);

        // client has to be restarted for the next request to work
        $this->client->restart();

        return $thread;
    }
}
