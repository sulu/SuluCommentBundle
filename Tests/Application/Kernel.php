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
    public function registerBundles(): iterable
    {
        $bundles = parent::registerBundles();
        $bundles[] = new SuluCommentBundle();

        if (SuluTestKernel::CONTEXT_WEBSITE === $this->getContext()) {
            $bundles[] = new SecurityBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $context = $this->getContext();
        $loader->load(__DIR__ . '/config/config_' . $context . '.yml');

        if (\class_exists(\Symfony\Bundle\SecurityBundle\Command\UserPasswordEncoderCommand::class)) { // detect Symfony <= 5.4
            $loader->load(__DIR__ . '/config/security-5-4.yml');
        } else {
            $loader->load(__DIR__ . '/config/security-6.yml');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getKernelParameters(): array
    {
        $parameters = parent::getKernelParameters();

        $reflection = new \ReflectionClass(\Gedmo\Exception::class);
        $gedmoDirectory = \dirname($reflection->getFileName());

        $parameters['gedmo_directory'] = $gedmoDirectory;

        return $parameters;
    }
}
