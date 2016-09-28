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

    const PRE_DELETE_EVENT = 'sulu_comment.pre_delete';
    const POST_DELETE_EVENT = 'sulu_comment.post_delete';

    const PRE_UPDATE_EVENT = 'sulu_comment.pre_update';

    const PUBLISH_EVENT = 'sulu_comment.publish';
    const UNPUBLISH_EVENT = 'sulu_comment.unpublish';

    const THREAD_PRE_UPDATE_EVENT = 'sulu_comment.thread.pre_update';

    const THREAD_PRE_DELETE_EVENT = 'sulu_comment.thread.pre_delete';
    const THREAD_POST_DELETE_EVENT = 'sulu_comment.thread.post_delete';

    /**
     * Private constructor.
     */
    private function __construct()
    {
    }
}
