<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Twig;

use Sulu\Bundle\CommentBundle\Form\Type\CommentType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CommentFormFactoryTwigExtension extends AbstractExtension
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var string
     */
    private $commentClass;

    public function __construct(FormFactoryInterface $formFactory, string $commentClass)
    {
        $this->formFactory = $formFactory;
        $this->commentClass = $commentClass;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('sulu_create_comment_form', [$this, 'createCommentForm']),
        ];
    }

    public function createCommentForm(string $threadId, string $referrer = null, int $parent = null): FormView
    {
        $form = $this->formFactory->create(
            CommentType::class,
            null,
            [
                'data_class' => $this->commentClass,
                'threadId' => $threadId,
                'referrer' => $referrer,
                'parent' => $parent,
            ]
        );

        return $form->createView();
    }
}
