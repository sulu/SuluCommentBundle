<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Events;

final class Events
{
    public const PRE_PERSIST_EVENT = 'sulu_comment.pre_persist';

    public const POST_PERSIST_EVENT = 'sulu_comment.post_persist';

    public const PRE_DELETE_EVENT = 'sulu_comment.pre_delete';

    public const POST_DELETE_EVENT = 'sulu_comment.post_delete';

    public const PRE_UPDATE_EVENT = 'sulu_comment.pre_update';

    public const PUBLISH_EVENT = 'sulu_comment.publish';

    public const UNPUBLISH_EVENT = 'sulu_comment.unpublish';

    public const THREAD_PRE_UPDATE_EVENT = 'sulu_comment.thread.pre_update';

    public const THREAD_PRE_DELETE_EVENT = 'sulu_comment.thread.pre_delete';

    public const THREAD_POST_DELETE_EVENT = 'sulu_comment.thread.post_delete';

    /**
     * Private constructor.
     */
    private function __construct()
    {
    }
}
