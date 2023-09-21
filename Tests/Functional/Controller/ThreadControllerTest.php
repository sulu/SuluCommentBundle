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
use Sulu\Bundle\CommentBundle\Entity\Thread;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class ThreadControllerTest extends SuluTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createAuthenticatedClient();
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->purgeDatabase();
    }

    public function testGet()
    {
        $thread = $this->createThread();
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request('GET', '/api/threads/' . $thread->getId());

        $this->assertHttpStatusCode(200, $this->client->getResponse());
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($thread->getId(), $data['id']);
        $this->assertEquals($thread->getTitle(), $data['title']);
    }

    public function testCGetFilter()
    {
        $this->createThread('Test 1', 'page', 'Test 1');
        $this->createThread('Test 2', 'page', 'Test 2');
        $this->createThread('Test 3', 'article', 'Test 3');

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request('GET', '/api/threads?type=page');

        $this->assertHttpStatusCode(200, $this->client->getResponse());
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $data['_embedded']['threads']);
    }

    public function testPut()
    {
        $thread = $this->createThread();
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request('PUT', '/api/threads/' . $thread->getId(), ['title' => 'My new Title']);

        $this->assertHttpStatusCode(200, $this->client->getResponse());
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($thread->getId(), $data['id']);
        $this->assertEquals('My new Title', $data['title']);
    }

    public function testDelete()
    {
        $thread = $this->createThread();
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request('DELETE', '/api/threads/' . $thread->getId());

        $this->assertHttpStatusCode(204, $this->client->getResponse());

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

        $this->client->request(
            'DELETE',
            '/api/threads?ids=' . implode(
                ',',
                array_map(
                    function(ThreadInterface $thread) {
                        return $thread->getId();
                    },
                    [$threads[0], $threads[1]]
                )
            )
        );

        $this->assertHttpStatusCode(204, $this->client->getResponse());

        foreach ([$threads[0], $threads[1]] as $comment) {
            $this->assertNull($this->entityManager->find(ThreadInterface::class, $comment->getId()));
        }

        $this->assertNotNull($this->entityManager->find(ThreadInterface::class, $threads[2]->getId()));
    }

    public function testCGet()
    {
        /** @var Thread[] $threads */
        $threads = [
            $this->createThread('Test 1', 'Test1', 1),
            $this->createThread('Test 2', 'Test2', 2),
            $this->createThread('Test 3', 'Test3', 3),
        ];
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request('GET', '/api/threads?fields=id,title');

        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $result = json_decode($this->client->getResponse()->getContent(), true);
        $result = $result['_embedded']['threads'];
        for ($i = 0, $length = count($threads); $i < $length; ++$i) {
            $this->assertEquals($threads[$i]->getId(), $result[$i]['id']);
            $this->assertEquals($threads[$i]->getTitle(), $result[$i]['title']);
        }
    }

    public function testCGetTypes()
    {
        /** @var Thread[] $threads */
        $threads = [
            $this->createThread('Test 1', 'Test1', 1),
            $this->createThread('Test 2', 'Test2', 2),
            $this->createThread('Test 3', 'Test3', 3),
        ];
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request('GET', '/api/threads?fields=id,title&types=Test1,Test3');

        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $result = json_decode($this->client->getResponse()->getContent(), true);
        $result = $result['_embedded']['threads'];

        $expected = [$threads[0], $threads[2]];
        for ($i = 0, $length = count($expected); $i < $length; ++$i) {
            $this->assertEquals($expected[$i]->getId(), $result[$i]['id']);
            $this->assertEquals($expected[$i]->getTitle(), $result[$i]['title']);
        }
    }

    /**
     * Create and persists new thread.
     *
     * @param string $title
     * @param string $type
     * @param string $entityId
     *
     * @return Thread
     */
    private function createThread($title = 'Sulu is awesome', $type = 'Test', $entityId = '123-123-123')
    {
        $thread = new Thread($type, $entityId, new ArrayCollection());
        $thread->setTitle($title);
        $this->entityManager->persist($thread);

        return $thread;
    }
}
