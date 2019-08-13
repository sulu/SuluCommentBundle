<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Routing\RouteBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Navigation\Navigation;
use Sulu\Bundle\AdminBundle\Navigation\NavigationItem;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Integrates sulu_comment into sulu-admin.
 */
class CommentAdmin extends Admin
{
    const COMMENT_SECURITY_CONTEXT = 'sulu.comment.comments';
    const COMMENT_LIST_ROUTE = 'sulu_comment.comments.list';
    const COMMENT_EDIT_FORM_ROUTE = 'sulu_comment.comments.edit_form';
    const COMMENT_EDIT_FORM_DETAILS_ROUTE = 'sulu_comment.comments.edit_form.details';

    const THREAD_SECURITY_CONTEXT = 'sulu.comment.threads';
    const THREAD_LIST_ROUTE = 'sulu_comment.threads.list';
    const THREAD_EDIT_FORM_ROUTE = 'sulu_comment.threads.edit_form';
    const THREAD_EDIT_FORM_DETAILS_ROUTE = 'sulu_comment.threads.edit_form.details';

    /**
     * @var RouteBuilderFactoryInterface
     */
    private $routeBuilderFactory;

    /**
     * @var SecurityCheckerInterface
     */
    private $securityChecker;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        RouteBuilderFactoryInterface $routeBuilderFactory,
        SecurityCheckerInterface $securityChecker,
        TranslatorInterface $translator
    ) {
        $this->routeBuilderFactory = $routeBuilderFactory;
        $this->securityChecker = $securityChecker;
        $this->translator = $translator;
    }

    public function getNavigation(): Navigation
    {
        $rootNavigationItem = $this->getNavigationItemRoot();

        $commentModule = new NavigationItem('sulu_comment.comments');
        $commentModule->setPosition(21);
        $commentModule->setIcon('su-comment');

        if ($this->securityChecker->hasPermission(self::COMMENT_SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $comments = new NavigationItem('sulu_comment.comments');
            $comments->setPosition(10);
            $comments->setMainRoute(static::COMMENT_LIST_ROUTE);

            $commentModule->addChild($comments);
        }

        if ($this->securityChecker->hasPermission(self::THREAD_SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $threads = new NavigationItem('sulu_comment.threads');
            $threads->setPosition(20);
            $threads->setMainRoute(static::THREAD_LIST_ROUTE);

            $commentModule->addChild($threads);
        }

        if ($commentModule->hasChildren()) {
            $rootNavigationItem->addChild($commentModule);
        }

        return new Navigation($rootNavigationItem);
    }

    public function getRoutes(): array
    {
        $formToolbarActions = [
            'sulu_admin.save',
            'sulu_admin.delete',
        ];

        /** @var array $commentFormToolbarActions */
        $commentFormToolbarActions = array_merge($formToolbarActions, ['sulu_admin.toggler' => [
            'label' => $this->translator->trans('sulu_admin.publish', [], 'admin'),
            'property' => 'published',
            'activate' => 'publish',
            'deactivate' => 'unpublish',
        ]]);

        $listToolbarActions = [
            'sulu_admin.delete',
            'sulu_admin.export',
        ];

        return [
            $this->routeBuilderFactory->createListRouteBuilder(static::COMMENT_LIST_ROUTE, '/comments')
                ->setResourceKey('comments')
                ->setListKey('comments')
                ->setTitle('sulu_comment.comments')
                ->addListAdapters(['table'])
                ->setEditRoute(static::COMMENT_EDIT_FORM_ROUTE)
                ->enableSearching()
                ->addToolbarActions($listToolbarActions)
                ->getRoute(),
            $this->routeBuilderFactory->createResourceTabRouteBuilder(static::COMMENT_EDIT_FORM_ROUTE, '/comments/:id')
                ->setResourceKey('comments')
                ->setBackRoute(static::COMMENT_LIST_ROUTE)
                ->getRoute(),
            $this->routeBuilderFactory->createFormRouteBuilder(static::COMMENT_EDIT_FORM_DETAILS_ROUTE, '/details')
                ->setResourceKey('comments')
                ->setFormKey('comment_details')
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($commentFormToolbarActions)
                ->setParent(static::COMMENT_EDIT_FORM_ROUTE)
                ->getRoute(),
            $this->routeBuilderFactory->createListRouteBuilder(static::THREAD_LIST_ROUTE, '/threads')
                ->setResourceKey('threads')
                ->setListKey('threads')
                ->setTitle('sulu_comment.threads')
                ->addListAdapters(['table'])
                ->setEditRoute(static::THREAD_EDIT_FORM_ROUTE)
                ->enableSearching()
                ->addToolbarActions($listToolbarActions)
                ->getRoute(),
            $this->routeBuilderFactory->createResourceTabRouteBuilder(static::THREAD_EDIT_FORM_ROUTE, '/threads/:id')
                ->setResourceKey('threads')
                ->setBackRoute(static::THREAD_LIST_ROUTE)
                ->getRoute(),
            $this->routeBuilderFactory->createFormRouteBuilder(static::THREAD_EDIT_FORM_DETAILS_ROUTE, '/details')
                ->setResourceKey('threads')
                ->setFormKey('thread_details')
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::THREAD_EDIT_FORM_ROUTE)
                ->getRoute(),
        ];
    }

    public function getSecurityContexts()
    {
        return [
            'Sulu' => [
                'Comment' => [
                    self::COMMENT_SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::DELETE,
                        PermissionTypes::LIVE,
                    ],
                    self::THREAD_SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::DELETE,
                        PermissionTypes::EDIT,
                    ],
                ],
            ],
        ];
    }
}
