<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides templates for threads.
 */
class ThreadTemplateController extends Controller
{
    /**
     * Renders the details form.
     *
     * @return Response
     */
    public function detailsAction()
    {
        return $this->render('SuluCommentBundle:Thread:details.html.twig');
    }
}
