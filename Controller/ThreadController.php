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

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\FieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides an api for threads.
 */
class ThreadController extends RestController implements ClassResourceInterface
{
    use RequestParametersTrait;

    /**
     * Returns list of field-descriptors.
     *
     * @Get("/threads/fields")
     *
     * @return Response
     */
    public function getFieldsAction()
    {
        return $this->handleView($this->view($this->getFieldDescriptors()));
    }

    /**
     * Returns list of threads.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $restHelper = $this->get('sulu_core.doctrine_rest_helper');
        $factory = $this->get('sulu_core.doctrine_list_builder_factory');
        $listBuilder = $factory->create($this->getParameter('sulu.model.thread.class'));

        $fieldDescriptors = $this->getFieldDescriptors();
        $restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        foreach ($request->query->all() as $filterKey => $filterValue) {
            if (isset($fieldDescriptors[$filterKey])) {
                $listBuilder->where($fieldDescriptors[$filterKey], $filterValue);
            }
        }

        $typeParameter = $request->get('types');
        if ($typeParameter) {
            $listBuilder->in($fieldDescriptors['type'], array_filter(explode(',', $typeParameter)));
        }

        $items = $listBuilder->execute();
        $list = new ListRepresentation(
            $items,
            'threads',
            'get_threads',
            $request->query->all(),
            $listBuilder->getCurrentPage(),
            $listBuilder->getLimit(),
            $listBuilder->count()
        );

        return $this->handleView($this->view($list, 200));
    }

    /**
     * Returns single thread.
     *
     * @param int $id
     *
     * @return Response
     *
     * @throws EntityNotFoundException
     */
    public function getAction($id)
    {
        $thread = $this->get('sulu.repository.thread')->findThreadById($id);
        if (!$thread) {
            throw new EntityNotFoundException(ThreadInterface::class, $id);
        }

        return $this->handleView($this->view($thread));
    }

    /**
     * Update thread.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     *
     * @throws EntityNotFoundException
     */
    public function putAction($id, Request $request)
    {
        /** @var ThreadInterface $thread */
        $thread = $this->get('sulu.repository.thread')->findThreadById($id);
        if (!$thread) {
            throw new EntityNotFoundException(ThreadInterface::class, $id);
        }

        $thread->setTitle($request->request->get('title'));

        $this->get('sulu_comment.manager')->updateThread($thread);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view($thread));
    }

    /**
     * Delete thread identified by id.
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $this->get('sulu_comment.manager')->deleteThreads([$id]);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view(null, 204));
    }

    /**
     * Delete multiple threads identified by ids parameter.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cdeleteAction(Request $request)
    {
        $ids = array_filter(explode(',', $request->get('ids', '')));

        if (0 === count($ids)) {
            return $this->handleView($this->view(null, 204));
        }

        $this->get('sulu_comment.manager')->deleteThreads($ids);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view(null, 204));
    }

    /**
     * Returns array of field-descriptors.
     *
     * @return FieldDescriptorInterface[]
     */
    private function getFieldDescriptors()
    {
        return $this->get('sulu_core.list_builder.field_descriptor_factory')
            ->getFieldDescriptorForClass($this->getParameter('sulu.model.thread.class'));
    }
}
