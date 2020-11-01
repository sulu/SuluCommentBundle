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
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\TogglerToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Integrates sulu_comment into sulu-admin.
 */
class CommentAdmin extends Admin
{
    const COMMENT_SECURITY_CONTEXT = 'sulu.comment.comments';
    const COMMENT_LIST_VIEW = 'sulu_comment.comments.list';
    const COMMENT_EDIT_FORM_VIEW = 'sulu_comment.comments.edit_form';
    const COMMENT_EDIT_FORM_DETAILS_VIEW = 'sulu_comment.comments.edit_form.details';

    const THREAD_SECURITY_CONTEXT = 'sulu.comment.threads';
    const THREAD_LIST_VIEW = 'sulu_comment.threads.list';
    const THREAD_EDIT_FORM_VIEW = 'sulu_comment.threads.edit_form';
    const THREAD_EDIT_FORM_DETAILS_VIEW = 'sulu_comment.threads.edit_form.details';

    /**
     * @var ViewBuilderFactoryInterface
     */
    private $viewBuilderFactory;

    /**
     * @var SecurityCheckerInterface
     */
    private $securityChecker;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        SecurityCheckerInterface $securityChecker,
        TranslatorInterface $translator
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker = $securityChecker;
        $this->translator = $translator;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        $commentModule = new NavigationItem('sulu_comment.comments');
        $commentModule->setPosition(21);
        $commentModule->setIcon('su-comment');

        if ($this->securityChecker->hasPermission(self::COMMENT_SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $comments = new NavigationItem('sulu_comment.comments');
            $comments->setPosition(10);
            $comments->setView(static::COMMENT_LIST_VIEW);

            $commentModule->addChild($comments);
        }

        if ($this->securityChecker->hasPermission(self::THREAD_SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $threads = new NavigationItem('sulu_comment.threads');
            $threads->setPosition(20);
            $threads->setView(static::THREAD_LIST_VIEW);

            $commentModule->addChild($threads);
        }

        if ($commentModule->hasChildren()) {
            $navigationItemCollection->add($commentModule);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $formToolbarActions = [
            new ToolbarAction('sulu_admin.save'),
            new ToolbarAction('sulu_admin.delete'),
        ];

        /** @var array $commentFormToolbarActions */
        $commentFormToolbarActions = array_merge($formToolbarActions, [
            new TogglerToolbarAction(
                $this->translator->trans('sulu_admin.publish', [], 'admin'),
                'published',
                'publish',
                'unpublish'
            ),
        ]);

        $listToolbarActions = [
            new ToolbarAction('sulu_admin.delete'),
            new ToolbarAction('sulu_admin.export'),
        ];

        $viewCollection->add(
            $this->viewBuilderFactory->createListViewBuilder(static::COMMENT_LIST_VIEW, '/comments')
                ->setResourceKey('comments')
                ->setListKey('comments')
                ->setTitle('sulu_comment.comments')
                ->addListAdapters(['table'])
                ->setEditView(static::COMMENT_EDIT_FORM_VIEW)
                ->enableSearching()
                ->addToolbarActions($listToolbarActions)
        );
        $viewCollection->add(
            $this->viewBuilderFactory->createResourceTabViewBuilder(static::COMMENT_EDIT_FORM_VIEW, '/comments/:id')
                ->setResourceKey('comments')
                ->setBackView(static::COMMENT_LIST_VIEW)
        );
        $viewCollection->add(
            $this->viewBuilderFactory->createFormViewBuilder(static::COMMENT_EDIT_FORM_DETAILS_VIEW, '/details')
                ->setResourceKey('comments')
                ->setFormKey('comment_details')
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($commentFormToolbarActions)
                ->setParent(static::COMMENT_EDIT_FORM_VIEW)
        );

        $viewCollection->add(
            $this->viewBuilderFactory->createListViewBuilder(static::THREAD_LIST_VIEW, '/threads')
                ->setResourceKey('threads')
                ->setListKey('threads')
                ->setTitle('sulu_comment.threads')
                ->addListAdapters(['table'])
                ->setEditView(static::THREAD_EDIT_FORM_VIEW)
                ->enableSearching()
                ->addToolbarActions($listToolbarActions)
        );
        $viewCollection->add(
            $this->viewBuilderFactory->createResourceTabViewBuilder(static::THREAD_EDIT_FORM_VIEW, '/threads/:id')
                ->setResourceKey('threads')
                ->setBackView(static::THREAD_LIST_VIEW)
        );
        $viewCollection->add(
            $this->viewBuilderFactory->createFormViewBuilder(static::THREAD_EDIT_FORM_DETAILS_VIEW, '/details')
                ->setResourceKey('threads')
                ->setFormKey('thread_details')
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::THREAD_EDIT_FORM_VIEW)
        );
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
