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

use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\CommentRepositoryInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\ListBuilder\FieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides an api for comments.
 */
class CommentController extends RestController implements ClassResourceInterface
{
    use RequestParametersTrait;

    /**
     * Returns list of comments.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $restHelper = $this->get('sulu_core.doctrine_rest_helper');
        $factory = $this->get('sulu_core.doctrine_list_builder_factory');
        $listBuilder = $factory->create($this->getParameter('sulu.model.comment.class'));

        /** @var FieldDescriptorInterface[] $fieldDescriptors */
        $fieldDescriptors = $this->get('sulu_core.list_builder.field_descriptor_factory')
            ->getFieldDescriptors('comments');
        $restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        if ($request->query->get('threadType')) {
            $listBuilder->in(
                $fieldDescriptors['threadType'],
                array_filter(explode(',', $request->query->get('threadType')))
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

    /**
     * Returns single comment.
     *
     * @param int $id
     *
     * @return Response
     *
     * @throws EntityNotFoundException
     */
    public function getAction($id)
    {
        $comment = $this->get('sulu.repository.comment')->findCommentById($id);
        if (!$comment) {
            throw new EntityNotFoundException(CommentInterface::class, $id);
        }

        return $this->handleView($this->view($comment));
    }

    /**
     * Update comment.
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
        /** @var CommentInterface|null $comment */
        $comment = $this->get('sulu.repository.comment')->findCommentById($id);
        if (!$comment) {
            throw new EntityNotFoundException(CommentInterface::class, $id);
        }

        $comment->setMessage($request->request->get('message'));

        $this->get('sulu_comment.manager')->update($comment);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view($comment));
    }

    /**
     * Delete multiple comments identified by id.
     *
     * @return Response
     */
    public function cdeleteAction(Request $request)
    {
        $ids = array_filter(explode(',', $request->query->get('ids')));
        if (0 === count($ids)) {
            return $this->handleView($this->view(null, 204));
        }

        $this->get('sulu_comment.manager')->delete($ids);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view(null, 204));
    }

    /**
     * Delete comment identified by id.
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $this->get('sulu_comment.manager')->delete([$id]);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view(null, 204));
    }

    /**
     * trigger a action for given comment specified over action get-parameter
     * - publish: Publish a comment
     * - unpublish: Unpublish a comment.
     *
     * @Post("/comments/{id}")
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     *
     * @throws RestException
     */
    public function postTriggerAction($id, Request $request)
    {
        $action = $this->getRequestParameter($request, 'action', true);

        /** @var CommentRepositoryInterface $commentRepository */
        $commentRepository = $this->get('sulu.repository.comment');
        $commentManager = $this->get('sulu_comment.manager');
        $comment = $commentRepository->findCommentById($id);
        if (!$comment) {
            return $this->handleView($this->view(null, 404));
        }

        switch ($action) {
            case 'unpublish':
                $commentManager->unpublish($comment);

                break;
            case 'publish':
                $commentManager->publish($comment);

                break;
            default:
                throw new RestException('Unrecognized action: ' . $action);
        }

        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view($comment));
    }
}
