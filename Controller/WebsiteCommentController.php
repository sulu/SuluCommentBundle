<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Controller;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\CommentRepositoryInterface;
use Sulu\Bundle\CommentBundle\Form\Type\CommentType;
use Sulu\Bundle\CommentBundle\Manager\CommentManagerInterface;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @RouteResource("thread")
 * @NamePrefix("sulu_comment.")
 */
class WebsiteCommentController extends RestController implements ClassResourceInterface
{
    /**
     * Returns list of comments for given thread.
     */
    public function cgetCommentsAction(string $threadId, Request $request): Response
    {
        list($type, $entityId) = $this->getThreadIdParts($threadId);

        $page = $request->get('page');
        $referrer = $request->get('referrer');

        $commentManager = $this->get('sulu_comment.manager');
        $comments = $commentManager->findPublishedComments(
            $type,
            $entityId,
            $page ?: 1,
            $page ? 20 : null
        );

        if ('json' === $request->getRequestFormat()) {
            return $this->handleView($this->view($comments));
        }

        $response = new Response();
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);

        $form = $this->createForm(
            CommentType::class,
            null,
            [
                'data_class' => $this->getParameter('sulu.model.comment.class'),
                'threadId' => $threadId,
                'referrer' => $referrer,
            ]
        );

        return $this->render(
            $this->getTemplate($type, 'comments'),
            [
                'form' => $form->createView(),
                'template' => $this->getTemplate($type, 'comment'),
                'formTemplate' => $this->getTemplate($type, 'form'),
                'comments' => $comments,
                'threadId' => $threadId,
                'referrer' => $referrer,
            ],
            $response
        );
    }

    /**
     * Create new comment for given thread.
     * If the thread does not exists a new will be created.
     */
    public function postCommentsAction(string $threadId, Request $request): Response
    {
        list($type, $entityId) = $this->getThreadIdParts($threadId);

        /** @var CommentRepositoryInterface $repository */
        $repository = $this->get('sulu.repository.comment');

        /** @var CommentInterface $comment */
        $comment = $repository->createNew();

        $form = $this->createForm(
            CommentType::class,
            $comment,
            [
                'data_class' => $this->getParameter('sulu.model.comment.class'),
                'threadId' => $threadId,
            ]
        );

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new Response(null, 400);
        }

        $comment = $form->getData();

        /** @var CommentManagerInterface $commentManager */
        $commentManager = $this->get('sulu_comment.manager');
        $commentManager->addComment($type, $entityId, $comment, $request->get('threadTitle'));

        $this->get('doctrine.orm.entity_manager')->flush();

        if ($referrer = $request->query->get('referrer')) {
            return new RedirectResponse($referrer);
        }

        if ('json' === $request->getRequestFormat()) {
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
     * @return array list($type, $entityId)
     */
    private function getThreadIdParts(string $threadId): array
    {
        $pos = strpos($threadId, '-');
        if (false === $pos) {
            throw new \RuntimeException('Thread id is not valid.');
        }

        return [substr($threadId, 0, $pos), substr($threadId, $pos + 1)];
    }

    /**
     * Returns template by type.
     */
    private function getTemplate(string $type, string $templateType): string
    {
        $types = $this->getParameter('sulu_comment.types');
        $defaults = $this->getParameter('sulu_comment.default_templates');

        if (array_key_exists($type, $types)) {
            return $types[$type]['templates'][$templateType];
        }

        return $defaults[$templateType];
    }
}
