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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommentBundle\Entity\Comment;
use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\Thread;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class CommentControllerTest extends SuluTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    protected function setUp()
    {
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->purgeDatabase();
    }

    public function testGet()
    {
        $thread = $this->createThread();
        $comment = $this->createComment($thread);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/comments/' . $comment->getId());

        $this->assertHttpStatusCode(200, $client->getResponse());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($comment->getId(), $data['id']);
        $this->assertEquals($comment->getMessage(), $data['message']);
    }

    public function testCGet()
    {
        $thread = $this->createThread();
        $this->createComment($thread);
        $this->createComment($thread);
        $this->createComment($thread);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/comments');

        $this->assertHttpStatusCode(200, $client->getResponse());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(3, $data['_embedded']['comments']);
    }

    public function testCGetFilter()
    {
        $thread = $this->createThread();
        $this->createComment($thread);
        $this->createComment($thread, 'Message', CommentInterface::STATE_UNPUBLISHED);
        $this->createComment($thread);

        $this->entityManager->flush();
        $this->entityManager->clear();

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/comments?state=' . CommentInterface::STATE_UNPUBLISHED);

        $this->assertHttpStatusCode(200, $client->getResponse());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $data['_embedded']['comments']);
    }

    public function testCGetTypeFilter()
    {
        $thread1 = $this->createThread('Test 1', 'test-1');
        $thread2 = $this->createThread('Test 2', 'test-2');
        $thread3 = $this->createThread('Test 3', 'test-3');
        $this->createComment($thread1);
        $this->createComment($thread2);
        $this->createComment($thread3);

        $this->entityManager->flush();
        $this->entityManager->clear();

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/comments?threadType=test-1,test-2');

        $this->assertHttpStatusCode(200, $client->getResponse());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(2, $data['_embedded']['comments']);
    }

    public function testPut()
    {
        $thread = $this->createThread();
        $comment = $this->createComment($thread);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/comments/' . $comment->getId(), ['message' => 'My new Message']);

        $this->assertHttpStatusCode(200, $client->getResponse());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($comment->getId(), $data['id']);
        $this->assertEquals('My new Message', $data['message']);
    }

    public function testDelete()
    {
        $thread = $this->createThread();
        $comment = $this->createComment($thread);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/comments/' . $comment->getId());

        $this->assertHttpStatusCode(204, $client->getResponse());

        $this->assertNull($this->entityManager->find(CommentInterface::class, $comment->getId()));
    }

    public function testCDelete()
    {
        $thread = $this->createThread();
        $comments = [
            $this->createComment($thread),
            $this->createComment($thread),
            $this->createComment($thread),
        ];
        $this->entityManager->flush();
        $this->entityManager->clear();

        $client = $this->createAuthenticatedClient();
        $client->request(
            'DELETE',
            '/api/comments?ids=' . implode(
                ',',
                array_map(
                    function(CommentInterface $comment) {
                        return $comment->getId();
                    },
                    [$comments[0], $comments[1]]
                )
            )
        );

        $this->assertHttpStatusCode(204, $client->getResponse());

        foreach ([$comments[0], $comments[1]] as $comment) {
            $this->assertNull($this->entityManager->find(CommentInterface::class, $comment->getId()));
        }

        $this->assertNotNull($this->entityManager->find(CommentInterface::class, $comments[2]->getId()));
    }

    public function testPublish()
    {
        $thread = $this->createThread();
        $comment = $this->createComment($thread, 'Sulu is awesome', CommentInterface::STATE_UNPUBLISHED);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->assertFalse($comment->isPublished());

        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/comments/' . $comment->getId() . '?action=publish');
        $this->assertHttpStatusCode(200, $client->getResponse());

        $result = $this->entityManager->find(CommentInterface::class, $comment->getId());
        $this->assertTrue($result->isPublished());
    }

    public function testUnpublish()
    {
        $thread = $this->createThread();
        $comment = $this->createComment($thread);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->assertTrue($comment->isPublished());

        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/comments/' . $comment->getId() . '?action=unpublish');
        $this->assertHttpStatusCode(200, $client->getResponse());

        $result = $this->entityManager->find(CommentInterface::class, $comment->getId());
        $this->assertFalse($result->isPublished());
    }

    private function createComment(
        ThreadInterface $thread,
        $message = 'Sulu is awesome',
        $state = CommentInterface::STATE_PUBLISHED
    ) {
        $comment = new Comment($state, $thread);
        $comment->setMessage($message);
        $thread->addComment($comment);
        $this->entityManager->persist($comment);

        return $comment;
    }

    private function createThread($title = 'Sulu is awesome', $type = 'Test', $entityId = '123-123-123')
    {
        $thread = new Thread($type, $entityId, new ArrayCollection());
        $thread->setTitle($title);
        $this->entityManager->persist($thread);

        return $thread;
    }
}
