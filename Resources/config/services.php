<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\CommentBundle\Admin\CommentAdmin;
use Sulu\Bundle\CommentBundle\Controller\CommentController;
use Sulu\Bundle\CommentBundle\Controller\ThreadController;
use Sulu\Bundle\CommentBundle\Controller\WebsiteCommentController;
use Sulu\Bundle\CommentBundle\Entity\CommentRepositoryInterface;
use Sulu\Bundle\CommentBundle\Events\CommentEventCollector;
use Sulu\Bundle\CommentBundle\Events\CommentEventCollectorInterface;
use Sulu\Bundle\CommentBundle\Events\CommentEventCollectorSubscriber;
use Sulu\Bundle\CommentBundle\EventSubscriber\CommentSerializationSubscriber;
use Sulu\Bundle\CommentBundle\Form\Type\CommentType;
use Sulu\Bundle\CommentBundle\Manager\CommentManager;
use Sulu\Bundle\CommentBundle\Manager\CommentManagerInterface;
use Sulu\Bundle\CommentBundle\Twig\CommentFormFactoryTwigExtension;
use Symfony\Component\DependencyInjection\Reference;

return static function(ContainerConfigurator $container) {
    $services = $container->services();

    $services->alias(CommentRepositoryInterface::class, 'sulu.repository.comment');

    $services->set('sulu_comment.comment_controller', CommentController::class)
        ->public()
        ->args([
            new Reference('fos_rest.view_handler'),
            new Reference('sulu_core.doctrine_rest_helper'),
            new Reference('sulu_core.doctrine_list_builder_factory'),
            new Reference('sulu_core.list_builder.field_descriptor_factory'),
            new Reference('sulu.repository.comment'),
            new Reference('sulu_comment.manager'),
            new Reference('doctrine.orm.entity_manager'),
            '%sulu.model.comment.class%',
        ])
        ->tag('sulu.context', ['context' => 'admin']);

    $services->set('sulu_comment.thread_controller', ThreadController::class)
        ->public()
        ->args([
            new Reference('fos_rest.view_handler'),
            new Reference('sulu_core.doctrine_rest_helper'),
            new Reference('sulu_core.doctrine_list_builder_factory'),
            new Reference('sulu_core.list_builder.field_descriptor_factory'),
            new Reference('sulu.repository.thread'),
            new Reference('sulu_comment.manager'),
            new Reference('doctrine.orm.entity_manager'),
            '%sulu.model.thread.class%',
        ])
        ->tag('sulu.context', ['context' => 'admin']);

    $services->set('sulu_comment.website_comment_controller', WebsiteCommentController::class)
        ->public()
        ->args([
            new Reference('fos_rest.view_handler'),
            new Reference('sulu_comment.manager'),
            new Reference('sulu.repository.comment'),
            new Reference('form.factory'),
            new Reference('twig'),
            new Reference('doctrine.orm.entity_manager'),
            '%sulu.model.comment.class%',
            '%sulu_comment.types%',
            '%sulu_comment.default_templates%',
            '%sulu_comment.serializer_groups%',
            '%sulu_comment.nested_comments%',
        ])
        ->tag('sulu.context', ['context' => 'website']);

    $services->set('sulu_comment.admin', CommentAdmin::class)
        ->args([
            new Reference(ViewBuilderFactoryInterface::class),
            new Reference('sulu_security.security_checker'),
            new Reference('translator'),
        ])
        ->tag('sulu.admin')
        ->tag('sulu.context', ['context' => 'admin']);

    $services->alias(CommentManagerInterface::class, 'sulu_comment.manager');

    $services->set('sulu_comment.manager', CommentManager::class)
        ->public()
        ->args([
            new Reference('sulu.repository.thread'),
            new Reference('sulu.repository.comment'),
            new Reference('event_dispatcher'),
            new Reference(CommentEventCollector::class),
        ]);

    $services->set(CommentType::class)
        ->args([
            new Reference('router'),
        ])
        ->tag('form.type');

    $services->set(CommentFormFactoryTwigExtension::class)
        ->args([
            new Reference('form.factory'),
            '%sulu.model.comment.class%',
        ])
        ->tag('twig.extension');

    $services->set(CommentSerializationSubscriber::class)
        ->args([
            new Reference('sulu_media.media_manager'),
            new Reference('request_stack'),
        ])
        ->tag('jms_serializer.event_subscriber');

    $services->set(CommentEventCollector::class)
        ->args([
            new Reference('event_dispatcher'),
        ]);

    $services->alias(CommentEventCollectorInterface::class, CommentEventCollector::class);

    $services->set(CommentEventCollectorSubscriber::class)
        ->args([
            new Reference(CommentEventCollectorInterface::class),
        ])
        ->tag('doctrine.event_subscriber', ['priority' => -256]);
};
