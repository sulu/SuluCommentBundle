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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommentBundle\Entity\Thread;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ThreadControllerTest extends SuluTestCase
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
        $this->entityManager->flush();
        $this->entityManager->clear();

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/threads/' . $thread->getId());

        $this->assertHttpStatusCode(200, $client->getResponse());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($thread->getId(), $data['id']);
        $this->assertEquals($thread->getTitle(), $data['title']);
    }

    public function testPut()
    {
        $thread = $this->createThread();
        $this->entityManager->flush();
        $this->entityManager->clear();

        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/threads/' . $thread->getId(), ['title' => 'My new Title']);

        $this->assertHttpStatusCode(200, $client->getResponse());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($thread->getId(), $data['id']);
        $this->assertEquals('My new Title', $data['title']);
    }

    public function testDelete()
    {
        $thread = $this->createThread();
        $this->entityManager->flush();
        $this->entityManager->clear();

        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/threads/' . $thread->getId());

        $this->assertHttpStatusCode(204, $client->getResponse());

        $this->assertNull($this->entityManager->find(ThreadInterface::class, $thread->getId()));
    }

    public function testCDelete()
    {
        $threads = [
            $this->createThread('Test 1', 'Test', 1),
            $this->createThread('Test 1', 'Test', 2),
            $this->createThread('Test 1', 'Test', 3),
        ];
        $this->entityManager->flush();
        $this->entityManager->clear();

        $client = $this->createAuthenticatedClient();
        $client->request(
            'DELETE',
            '/api/threads?ids=' . implode(
                ',',
                array_map(
                    function (ThreadInterface $thread) {
                        return $thread->getId();
                    },
                    [$threads[0], $threads[1]]
                )
            )
        );

        $this->assertHttpStatusCode(204, $client->getResponse());

        foreach ([$threads[0], $threads[1]] as $comment) {
            $this->assertNull($this->entityManager->find(ThreadInterface::class, $comment->getId()));
        }

        $this->assertNotNull($this->entityManager->find(ThreadInterface::class, $threads[2]->getId()));
    }

    private function createThread($title = 'Sulu is awesome', $type = 'Test', $entityId = '123-123-123')
    {
        $thread = new Thread($type, $entityId, new ArrayCollection());
        $thread->setTitle($title);
        $this->entityManager->persist($thread);

        return $thread;
    }
}
