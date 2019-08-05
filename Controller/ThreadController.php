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

use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\FieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ThreadController extends RestController implements ClassResourceInterface
{
    use RequestParametersTrait;

    public function cgetAction(Request $request): Response
    {
        $restHelper = $this->get('sulu_core.doctrine_rest_helper');
        $factory = $this->get('sulu_core.doctrine_list_builder_factory');
        $listBuilder = $factory->create($this->getParameter('sulu.model.thread.class'));

        /** @var FieldDescriptorInterface[] $fieldDescriptors */
        $fieldDescriptors = $this->get('sulu_core.list_builder.field_descriptor_factory')
            ->getFieldDescriptors('threads');
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
        $thread = $this->get('sulu.repository.thread')->findThreadById($id);
        if (!$thread) {
            throw new EntityNotFoundException(ThreadInterface::class, $id);
        }

        return $this->handleView($this->view($thread));
    }

    public function putAction($id, Request $request): Response
    {
        /** @var ThreadInterface|null $thread */
        $thread = $this->get('sulu.repository.thread')->findThreadById($id);
        if (!$thread) {
            throw new EntityNotFoundException(ThreadInterface::class, $id);
        }

        $thread->setTitle($request->request->get('title'));

        $this->get('sulu_comment.manager')->updateThread($thread);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view($thread));
    }

    public function cdeleteAction(Request $request): Response
    {
        /** @var int[] $ids */
        $ids = array_filter(explode(',', $request->query->get('ids')));
        if (0 === count($ids)) {
            return $this->handleView($this->view(null, 204));
        }

        $this->get('sulu_comment.manager')->deleteThreads($ids);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view(null, 204));
    }

    public function deleteAction(int $id): Response
    {
        $this->get('sulu_comment.manager')->deleteThreads([$id]);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view(null, 204));
    }
}
