<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommentType extends AbstractType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attributes = ['threadId' => $options['threadId']];
        if ($options['referrer']) {
            $attributes['referrer'] = $options['referrer'];
        }
        if ($options['parent']) {
            $attributes['parent'] = $options['parent'];
        }

        $builder->setAction($this->router->generate('sulu_comment.post_thread_comments', $attributes));
        $builder->add('message', TextareaType::class);
        $builder->add('threadTitle', HiddenType::class, ['mapped' => false]);
        $builder->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('threadId');
        $resolver->setDefault('referrer', null);
        $resolver->setDefault('parent', null);
        $resolver->setDefault('csrf_protection', false);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
