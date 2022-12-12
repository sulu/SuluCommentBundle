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
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\CommentRepositoryInterface;
use Sulu\Bundle\CommentBundle\Manager\CommentManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\FieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides an api for comments.
 */
class CommentController extends AbstractRestController implements ClassResourceInterface
{
    use RequestParametersTrait;

    /**
     * @var RestHelperInterface
     */
    private $restHelper;

    /**
     * @var DoctrineListBuilderFactoryInterface
     */
    private $doctrineListBuilderFactory;

    /**
     * @var FieldDescriptorFactoryInterface
     */
    private $fieldDescriptorFactory;

    /**
     * @var CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * @var CommentManagerInterface
     */
    private $commentManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $commentClass;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        RestHelperInterface $restHelper,
        DoctrineListBuilderFactoryInterface $doctrineListBuilderFactory,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        CommentRepositoryInterface $commentRepository,
        CommentManagerInterface $commentManager,
        EntityManagerInterface $entityManager,
        string $commentClass
    ) {
        parent::__construct($viewHandler);

        $this->restHelper = $restHelper;
        $this->doctrineListBuilderFactory = $doctrineListBuilderFactory;
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->commentRepository = $commentRepository;
        $this->commentManager = $commentManager;
        $this->entityManager = $entityManager;
        $this->commentClass = $commentClass;
    }

    public function cgetAction(Request $request): Response
    {
        $listBuilder = $this->doctrineListBuilderFactory->create($this->commentClass);

        /** @var FieldDescriptorInterface[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors('comments');
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $threadType = (string) $request->query->get('threadType', '');
        if ($threadType) {
            $listBuilder->in(
                $fieldDescriptors['threadType'],
                array_filter(explode(',', $threadType))
            );

            $request->query->remove('threadType');
        }

        foreach ($request->query->all() as $filterKey => $filterValue) {
            if (isset($fieldDescriptors[$filterKey])) {
                $listBuilder->where($fieldDescriptors[$filterKey], $filterValue);
            }
        }

        $results = $listBuilder->execute();
        $list = new ListRepresentation(
            $results,
            'comments',
            $request->attributes->get('_route'),
            $request->query->all(),
            $listBuilder->getCurrentPage(),
            $listBuilder->getLimit(),
            $listBuilder->count()
        );

        return $this->handleView($this->view($list, 200));
    }

    public function getAction(int $id): Response
    {
        $comment = $this->commentRepository->findCommentById($id);
        if (!$comment) {
            throw new EntityNotFoundException(CommentInterface::class, $id);
        }

        return $this->handleView($this->view($comment));
    }

    public function putAction(int $id, Request $request): Response
    {
        /** @var CommentInterface|null $comment */
        $comment = $this->commentRepository->findCommentById($id);
        if (!$comment) {
            throw new EntityNotFoundException(CommentInterface::class, $id);
        }

        $comment->setMessage((string) $request->request->get('message'));

        $this->commentManager->update($comment);
        $this->entityManager->flush();

        return $this->handleView($this->view($comment));
    }

    public function cdeleteAction(Request $request): Response
    {
        /** @var string $ids */
        $ids = $request->query->get('ids', '');

        /** @var int[] $ids */
        $ids = array_filter(explode(',', $ids));
        if (0 === count($ids)) {
            return $this->handleView($this->view(null, 204));
        }

        $this->commentManager->delete($ids);
        $this->entityManager->flush();

        return $this->handleView($this->view(null, 204));
    }

    public function deleteAction(int $id): Response
    {
        $this->commentManager->delete([$id]);
        $this->entityManager->flush();

        return $this->handleView($this->view(null, 204));
    }

    /**
     * trigger a action for given comment specified over action get-parameter
     * - publish: Publish a comment
     * - unpublish: Unpublish a comment.
     *
     * @Post("/comments/{id}")
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);

        $comment = $this->commentRepository->findCommentById($id);
        if (!$comment) {
            return $this->handleView($this->view(null, 404));
        }

        switch ($action) {
            case 'unpublish':
                $this->commentManager->unpublish($comment);

                break;
            case 'publish':
                $this->commentManager->publish($comment);

                break;
            default:
                throw new RestException('Unrecognized action: ' . $action);
        }

        $this->entityManager->flush();

        return $this->handleView($this->view($comment));
    }
}
