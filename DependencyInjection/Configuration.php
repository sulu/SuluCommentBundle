<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\DependencyInjection;

use Sulu\Bundle\CommentBundle\Entity\Comment;
use Sulu\Bundle\CommentBundle\Entity\CommentRepository;
use Sulu\Bundle\CommentBundle\Entity\Thread;
use Sulu\Bundle\CommentBundle\Entity\ThreadRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Build configuration-tree for sulu_comment.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sulu_comment');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('default_templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('comments')->defaultValue('@SuluComment/WebsiteComment/comments.html.twig')->end()
                        ->scalarNode('comment')->defaultValue('@SuluComment/WebsiteComment/comment.html.twig')->end()
                        ->scalarNode('form')->defaultValue('@SuluComment/WebsiteComment/form.html.twig')->end()
                    ->end()
                ->end()
                ->arrayNode('serializer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('groups')
                            ->beforeNormalization()->castToArray()->end()
                            ->prototype('scalar')->end()
                            ->defaultValue(['Default'])
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('nested_comments')->defaultTrue()->end()
                ->arrayNode('types')
                    ->useAttributeAsKey('type')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('templates')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('comments')->defaultValue('@SuluComment/WebsiteComment/comments.html.twig')->end()
                                    ->scalarNode('comment')->defaultValue('@SuluComment/WebsiteComment/comment.html.twig')->end()
                                    ->scalarNode('form')->defaultValue('@SuluComment/WebsiteComment/form.html.twig')->end()
                                ->end()
                            ->end()
                            ->booleanNode('nested_comments')->defaultTrue()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('objects')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('comment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('model')->defaultValue(Comment::class)->end()
                                ->scalarNode('repository')->defaultValue(CommentRepository::class)->end()
                            ->end()
                        ->end()
                        ->arrayNode('thread')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('model')->defaultValue(Thread::class)->end()
                                ->scalarNode('repository')->defaultValue(ThreadRepository::class)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
