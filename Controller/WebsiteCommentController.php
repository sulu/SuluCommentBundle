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

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Bundle\CommentBundle\Entity\Comment;
use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\CommentRepositoryInterface;
use Sulu\Bundle\CommentBundle\Form\Type\CommentType;
use Sulu\Bundle\CommentBundle\Manager\CommentManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @RouteResource("thread")
 */
class WebsiteCommentController extends AbstractRestController implements ClassResourceInterface
{
    /**
     * @var CommentManagerInterface
     */
    private $commentManager;

    /**
     * @var CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $commentClass;

    /**
     * @var array
     */
    private $commentTypes;

    /**
     * @var array
     */
    private $commentDefaultTemplates;

    /**
     * @var array
     */
    private $commentSerializationGroups;

    /**
     * @var bool
     */
    private $enableNestedCommentsDefault;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        CommentManagerInterface $commentManager,
        CommentRepositoryInterface $commentRepository,
        FormFactoryInterface $formFactory,
        Environment $twig,
        EntityManagerInterface $entityManager,
        string $commentClass,
        array $commentTypes,
        array $commentDefaultTemplates,
        array $commentSerializationGroups,
        bool $enableNestedCommentsDefault
    ) {
        parent::__construct($viewHandler);

        $this->commentManager = $commentManager;
        $this->commentRepository = $commentRepository;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->commentClass = $commentClass;
        $this->commentTypes = $commentTypes;
        $this->commentDefaultTemplates = $commentDefaultTemplates;
        $this->commentSerializationGroups = $commentSerializationGroups;
        $this->enableNestedCommentsDefault = $enableNestedCommentsDefault;
    }

    /**
     * Returns list of comments for given thread.
     */
    public function cgetCommentsAction(string $threadId, Request $request): Response
    {
        list($type, $entityId) = $this->getThreadIdParts($threadId);

        $limit = $request->query->getInt('limit') ?? 10;
        $offset = $request->query->getInt('offset') ?? 0;

        $pageSize = $request->get('pageSize') ?? 10;
        if ($pageSize) {
            @\trigger_deprecation('sulu/comment-bundle', '2.x', 'The usage of the "pageSize" parameter is deprecated.
        Please use "limit" and "offset instead.');
            $limit = $pageSize;
        }

        $page = $request->get('page') ?? null;
        if ($page) {
            @\trigger_deprecation('sulu/comment-bundle', '2.x', 'The usage of the "page" parameter is deprecated.
            Please use "limit" and "offset instead.');

            $offset = ($page - 1) * $limit;
        }

        $referrer = $request->get('referrer');

        $comments = $this->commentManager->findPublishedComments(
            $type,
            $entityId,
            $limit,
            $offset
        );

        $totalComments = $this->commentManager->countPublishedComments($type, $entityId);

        if ('json' === $request->getRequestFormat()) {
            return $this->handleView($this->view($comments));
        }

        $response = new Response();
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);

        $form = $this->formFactory->create(
            CommentType::class,
            null,
            [
                'data_class' => $this->commentClass,
                'threadId' => $threadId,
                'referrer' => $referrer,
            ]
        );

        $contentData = array_merge(
            [
                'form' => $form->createView(),
                'nestedComments' => $this->getNestedCommentsEnabled($type),
                'commentTemplate' => $this->getTemplate($type, 'comment'),
                'commentsTemplate' => $this->getTemplate($type, 'comments'),
                'comments' => $comments,
                'threadId' => $threadId,
                'referrer' => $referrer,
                'totalComments' => $totalComments,
                'page' => $page ?: 1,
                'pageSize' => $page ? $pageSize : null,
            ],
            $this->getAdditionalContentData($request)
        );

        $response->setContent(
            $this->twig->render(
                $this->getTemplate($type, 'comments'),
                $contentData
            )
        );

        return $response;
    }

    /**
     * @return string[]
     */
    protected function getAdditionalContentData(Request $request): array
    {
        return [];
    }

    public function cgetCountAction(string $threadId, Request $request): Response
    {
        list($type, $entityId) = $this->getThreadIdParts($threadId);

        $totalComments = $this->commentManager->countPublishedComments($type, $entityId);

        if ('json' === $request->getRequestFormat()) {
            return $this->handleView($this->view($totalComments));
        }

        $response = new Response();
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);

        $response->setContent(
            $this->twig->render(
                $this->getTemplate($type, 'count'),
                [
                    'totalComments' => $totalComments,
                ]
            )
        );

        return $response;
    }

    /**
     * Create new comment for given thread.
     * If the thread does not exists a new will be created.
     */
    public function postCommentsAction(string $threadId, Request $request): Response
    {
        list($type, $entityId) = $this->getThreadIdParts($threadId);

        /** @var CommentInterface $comment */
        $comment = $this->commentRepository->createNew();

        if ($parent = $request->get('parent')) {
            $comment->setParent($this->commentRepository->findCommentById($parent));
        }

        $form = $this->formFactory->create(
            CommentType::class,
            $comment,
            [
                'data_class' => $this->commentClass,
                'threadId' => $threadId,
            ]
        );

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new Response(null, 400);
        }

        $comment = $form->getData();

        $this->commentManager->addComment($type, $entityId, $comment, $request->get('threadTitle'));
        $this->entityManager->flush();

        if ($referrer = $request->query->get('referrer')) {
            return new RedirectResponse($referrer);
        }

        if ('json' === $request->getRequestFormat()) {
            return $this->handleView($this->view($comment));
        }

        return new Response(
            $this->twig->render(
                $this->getTemplate($type, 'comment'),
                [
                    'comment' => $comment,
                    'threadId' => $threadId,
                ]
            )
        );
    }

    /**
     * @Post("/threads/{threadId}/comments/{commentId}")
     */
    public function putCommentAction(string $threadId, string $commentId, Request $request): Response
    {
        list($type, $entityId) = $this->getThreadIdParts($threadId);

        $message = $request->request->get('message');

        /** @var Comment $comment */
        $comment = $this->commentRepository->findCommentById((int) $commentId);
        $comment->setMessage($message);
        $this->entityManager->flush();

        if ($referrer = $request->query->get('referrer')) {
            return new RedirectResponse($referrer);
        }

        if ('json' === $request->getRequestFormat()) {
            return $this->handleView($this->view($comment));
        }

        return new Response(
            $this->twig->render(
                $this->getTemplate($type, 'comment'),
                [
                    'comment' => $comment,
                    'threadId' => $threadId,
                ]
            )
        );
    }

    public function deleteCommentAction(string $threadId, string $commentId, Request $request): Response
    {
        /** @var Comment $comment */
        $comment = $this->commentRepository->findCommentById(intval($commentId));

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        if ($referrer = $request->query->get('referrer')) {
            return new RedirectResponse($referrer);
        }

        if ('json' === $request->getRequestFormat()) {
            return $this->handleView($this->view());
        }

        return new Response();
    }

    protected function view($data = null, $statusCode = null, array $headers = [])
    {
        $view = parent::view($data, $statusCode, $headers);

        $context = new Context();
        $context->setGroups($this->commentSerializationGroups);
        $view->setContext($context);

        return $view;
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
        if (array_key_exists($type, $this->commentTypes)) {
            return $this->commentTypes[$type]['templates'][$templateType];
        }

        return $this->commentDefaultTemplates[$templateType];
    }

    private function getNestedCommentsEnabled(string $type): bool
    {
        if (array_key_exists($type, $this->commentTypes)) {
            return $this->commentTypes[$type]['nested_comments'];
        }

        return $this->enableNestedCommentsDefault;
    }
}
