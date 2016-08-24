<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Controller;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @RouteResource("thread")
 *
 * Provides a website-api for comments.
 */
class WebsiteCommentController extends RestController implements ClassResourceInterface
{
    /**
     * Returns list of comments for given thread.
     *
     * @param string $threadId
     *
     * @return Response
     */
    public function cgetCommentsAction($threadId)
    {
        list($type, $entityId) = $this->getThreadIdParts($threadId);

        $commentManager = $this->get('sulu_comment.manager');
        $comments = $commentManager->findComments($type, $entityId);

        return $this->handleView($this->view($comments));
    }

    /**
     * Create new comment for given thread.
     * If the thread does not exists a new will be created.
     *
     * @param string $threadId
     * @param Request $request
     *
     * @return Response
     */
    public function postCommentsAction($threadId, Request $request)
    {
        $data = $request->request->all();
        list($type, $entityId) = $this->getThreadIdParts($threadId);

        $serializer = $this->get('serializer');
        $comment = $serializer->deserialize(
            json_encode($data),
            $this->getParameter('sulu.model.comment.class'),
            'json'
        );

        $commentManager = $this->get('sulu_comment.manager');
        $thread = $commentManager->addComment($type, $entityId, $comment);
        $thread->setTitle($request->get('threadTitle'));

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $entityManager->persist($thread);
        $entityManager->flush();

        return $this->handleView($this->view($comment));
    }

    /**
     * Splits the thread-id into type and entity-id.
     *
     * @param string $threadId
     *
     * @return array list($type, $entityId)
     */
    private function getThreadIdParts($threadId)
    {
        $pos = strpos($threadId, '-');

        return [substr($threadId, 0, $pos), substr($threadId, $pos + 1)];
    }
}
