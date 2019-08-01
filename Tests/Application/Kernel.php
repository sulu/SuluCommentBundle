<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Tests\Application;

use Sulu\Bundle\CommentBundle\SuluCommentBundle;
use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

class Kernel extends SuluTestKernel
{
    public function registerBundles()
    {
        $bundles = parent::registerBundles();
        $bundles[] = new SuluCommentBundle();

        if (SuluTestKernel::CONTEXT_WEBSITE === $this->getContext()) {
            $bundles[] = new \FOS\RestBundle\FOSRestBundle();
            $bundles[] = new SecurityBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);

        $context = $this->getContext();
        $loader->load(__DIR__ . '/config/config_' . $context . '.yml');
    }
}
