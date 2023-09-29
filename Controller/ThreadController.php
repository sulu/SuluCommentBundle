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
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadRepositoryInterface;
use Sulu\Bundle\CommentBundle\Manager\CommentManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\FieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ThreadController extends AbstractRestController implements ClassResourceInterface
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
     * @var ThreadRepositoryInterface
     */
    private $threadRepository;

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
    private $threadClass;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        RestHelperInterface $restHelper,
        DoctrineListBuilderFactoryInterface $doctrineListBuilderFactory,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        ThreadRepositoryInterface $threadRepository,
        CommentManagerInterface $commentManager,
        EntityManagerInterface $entityManager,
        string $threadClass
    ) {
        parent::__construct($viewHandler);

        $this->restHelper = $restHelper;
        $this->doctrineListBuilderFactory = $doctrineListBuilderFactory;
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->threadRepository = $threadRepository;
        $this->commentManager = $commentManager;
        $this->entityManager = $entityManager;
        $this->threadClass = $threadClass;
    }

    public function cgetAction(Request $request): Response
    {
        $listBuilder = $this->doctrineListBuilderFactory->create($this->threadClass);

        /** @var FieldDescriptorInterface[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors('threads');
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        /** @var string $filterValue */
        foreach ($request->query->all() as $filterKey => $filterValue) {
            if (isset($fieldDescriptors[$filterKey])) {
                $listBuilder->where($fieldDescriptors[$filterKey], $filterValue);
            }
        }

        /** @var string $typeParameter */
        $typeParameter = $request->get('types');
        if ($typeParameter) {
            $listBuilder->in($fieldDescriptors['type'], array_filter(explode(',', $typeParameter)));
        }

        $items = $listBuilder->execute();
        /** @var string $route */
        $route = $request->attributes->get('_route');
        $list = new ListRepresentation(
            $items,
            'threads',
            $route,
            $request->query->all(),
            $listBuilder->getCurrentPage(),
            $listBuilder->getLimit(),
            $listBuilder->count()
        );

        return $this->handleView($this->view($list, 200));
    }

    public function getAction(int $id): Response
    {
        $thread = $this->threadRepository->findThreadById($id);
        if (!$thread) {
            throw new EntityNotFoundException(ThreadInterface::class, $id);
        }

        return $this->handleView($this->view($thread));
    }

    public function putAction(int $id, Request $request): Response
    {
        /** @var ThreadInterface|null $thread */
        $thread = $this->threadRepository->findThreadById($id);
        if (!$thread) {
            throw new EntityNotFoundException(ThreadInterface::class, $id);
        }

        /** @var string $title */
        $title = $request->request->get('title');
        $thread->setTitle($title);

        $this->commentManager->updateThread($thread);
        $this->entityManager->flush();

        return $this->handleView($this->view($thread));
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

        $this->commentManager->deleteThreads($ids);
        $this->entityManager->flush();

        return $this->handleView($this->view(null, 204));
    }

    public function deleteAction(int $id): Response
    {
        $this->commentManager->deleteThreads([$id]);
        $this->entityManager->flush();

        return $this->handleView($this->view(null, 204));
    }
}
