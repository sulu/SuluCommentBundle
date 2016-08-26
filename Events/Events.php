<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Events;

/**
 * Container for comment-events.
 */
final class Events
{
    const PRE_PERSIST_EVENT = 'sulu_comment.pre_persist';
    const POST_PERSIST_EVENT = 'sulu_comment.post_persist';

    /**
     * Private constructor.
     */
    private function __construct()
    {
    }
}
