<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle;

use Sulu\Bundle\CommentBundle\Entity\CommentInterface;
use Sulu\Bundle\CommentBundle\Entity\ThreadInterface;
use Sulu\Bundle\PersistenceBundle\PersistenceBundleTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Register the bundles compiler passes.
 */
class SuluCommentBundle extends Bundle
{
    use PersistenceBundleTrait;

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $this->buildPersistence(
            [
                CommentInterface::class => 'sulu.model.comment.class',
                ThreadInterface::class => 'sulu.model.thread.class',
            ],
            $container
        );
    }
}
