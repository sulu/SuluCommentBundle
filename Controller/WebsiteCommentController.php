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

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\CommentBundle\Entity\Comment;
use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\CommentRepository;
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
                'nestedComments' => $this->getNestedCommentsFlag($type),
                'commentTemplate' => $this->getTemplate($type, 'comment'),
                'commentsTemplate' => $this->getTemplate($type, 'comments'),
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

        if ($parent = $request->get('parent')) {
            $comment->setParent($repository->findCommentById($parent));
        }

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
     * @Post("/threads/{threadId}/comments/{commentId}")
     */
    public function putCommentAction(string $threadId, string $commentId, Request $request): Response
    {
        list($type, $entityId) = $this->getThreadIdParts($threadId);

        /** @var CommentRepositoryInterface $repository */
        $repository = $this->get('sulu.repository.comment');
        $message = $request->request->get('message');

        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');

        /** @var CommentRepository $commentRepository */
        $commentRepository = $entityManager->getRepository(Comment::class);

        /** @var Comment $comment */
        $comment = $commentRepository->findCommentById((int)$commentId);
        $comment->setMessage($message);
        $entityManager->flush();

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

    public function deleteCommentAction(string $threadId, string $commentId, Request $request): Response
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');

        /** @var CommentRepository $commentRepository */
        $commentRepository = $entityManager->getRepository(Comment::class);
        $referrer = $request->get('referrer');
        /** @var Comment $comment */
        $comment = $commentRepository->findCommentById((int)$commentId);

        $entityManager->remove($comment);
        $entityManager->flush();

        if ($referrer = $request->query->get('referrer')) {
            return new RedirectResponse($referrer);
        }

        if ('json' === $request->getRequestFormat()) {
            return $this->handleView($this->view());
        }

        return new Response();
    }

    /**
     * Splits the thread-id into type and entity-id.
     *
     * @return string[] list($type, $entityId)
     */
    private function getThreadIdParts(string $threadId): array
    {
        $pos = strpos($threadId, '-');
        if (false === $pos) {
            throw new \RuntimeException('Thread id is not valid.');
        }

        return [substr($threadId, 0, $pos), substr($threadId, $pos + 1)];
    }

    private function getTemplate(string $type, string $templateType): string
    {
        $defaults = $this->getParameter('sulu_comment.default_templates');

        $types = $this->getParameter('sulu_comment.types');
        if (array_key_exists($type, $types)) {
            return $types[$type]['templates'][$templateType];
        }

        return $defaults[$templateType];
    }

    private function getNestedCommentsFlag(string $type): string
    {
        $default = $this->getParameter('sulu_comment.nested_comments');

        $types = $this->getParameter('sulu_comment.types');
        if (array_key_exists($type, $types)) {
            return $types[$type]['nested_comments'];
        }

        return $default;
    }
}
