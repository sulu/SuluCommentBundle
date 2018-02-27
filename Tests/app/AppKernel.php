<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Sulu\Bundle\CommentBundle\SuluCommentBundle;
use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends SuluTestKernel
{
    /**
     * {@inheritdoc}
     */
    public function __construct($environment, $debug, $suluContext = self::CONTEXT_ADMIN)
    {
        $this->name = $suluContext;

        parent::__construct($environment, $debug, $suluContext);
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = array_merge(
            [
                new SuluCommentBundle(),
            ],
            parent::registerBundles()
        );

        if (self::CONTEXT_ADMIN !== $this->getContext()) {
            $bundles[] = new \FOS\RestBundle\FOSRestBundle();
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);

        $context = $this->getContext();
        $loader->load(__DIR__ . '/config/config_' . $context . '.yml');
    }
}
