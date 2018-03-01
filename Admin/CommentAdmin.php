<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Navigation\Navigation;
use Sulu\Bundle\AdminBundle\Navigation\NavigationItem;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

/**
 * Integrates sulu_comment into sulu-admin.
 */
class CommentAdmin extends Admin
{
    const COMMENT_SECURITY_CONTEXT = 'sulu.comment.comments';

    const THREAD_SECURITY_CONTEXT = 'sulu.comment.threads';

    /**
     * @var SecurityCheckerInterface
     */
    private $securityChecker;

    /**
     * @param SecurityCheckerInterface $securityChecker
     * @param string $title
     */
    public function __construct(SecurityCheckerInterface $securityChecker, $title)
    {
        $this->securityChecker = $securityChecker;

        $rootNavigationItem = new NavigationItem($title);
        $section = new NavigationItem('navigation.modules');
        $section->setPosition(20);

        $commentModule = new NavigationItem('sulu_comment.comments');
        $commentModule->setPosition(9);
        $commentModule->setIcon('commenting');

        if ($this->securityChecker->hasPermission(self::COMMENT_SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $comments = new NavigationItem('sulu_comment.comments');
            $comments->setPosition(10);
            $comments->setAction('comments');

            $commentModule->addChild($comments);
        }

        if ($this->securityChecker->hasPermission(self::THREAD_SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $threads = new NavigationItem('sulu_comment.threads');
            $threads->setPosition(20);
            $threads->setAction('threads');

            $commentModule->addChild($threads);
        }

        if ($commentModule->hasChildren()) {
            $section->addChild($commentModule);
            $rootNavigationItem->addChild($section);
        }

        $this->setNavigation(new Navigation($rootNavigationItem));
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getJsBundleName()
    {
        return 'sulucomment';
    }
}
