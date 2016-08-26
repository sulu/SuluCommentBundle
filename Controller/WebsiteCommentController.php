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
     * @param Request $request
     *
     * @return Response
     */
    public function cgetCommentsAction($threadId, Request $request)
    {
        list($type, $entityId) = $this->getThreadIdParts($threadId);

        $commentManager = $this->get('sulu_comment.manager');
        $comments = $commentManager->findComments($type, $entityId);

        if ($request->getRequestFormat() === 'json') {
            return $this->handleView($this->view($comments));
        }

        return $this->render(
            $this->getTemplate($type, 'comments'),
            [
                'template' => $this->getTemplate($type, 'comment'),
                'comments' => $comments,
                'threadId' => $threadId,
            ]
        );
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
        if (array_key_exists('created', $data)
            || array_key_exists('creator', $data)
            || array_key_exists('changed', $data)
            || array_key_exists('changer', $data)
        ) {
            return new Response(null, 400);
        }

        $this->get('sulu_http_cache.handler.url')->invalidatePath(
            $this->generateUrl('get_threads_comments', ['threadId' => $threadId, '_format' => 'html'])
        );
        $this->get('sulu_http_cache.handler.url')->invalidatePath(
            $this->generateUrl('get_threads_comments', ['threadId' => $threadId])
        );

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

        if ($request->getRequestFormat() === 'json') {
            return $this->handleView($this->view($comment));
        }

        return $this->render(
            $this->getTemplate($type, 'comment'),
            [
                'comment' => $comment,
                'threadId' => $threadId,
            ]
        );
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

    /**
     * Returns template by type.
     *
     * @param string $type
     * @param string $templateType comment or comments
     *
     * @return string
     */
    private function getTemplate($type, $templateType)
    {
        $types = $this->getParameter('sulu_comment.types');
        $defaults = $this->getParameter('sulu_comment.default_templates');

        if (array_key_exists($type, $types)) {
            return $types[$type]['templates'][$templateType];
        }

        return $defaults[$templateType];
    }
}
