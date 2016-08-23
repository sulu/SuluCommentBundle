<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\Entity;

/**
 * Interface for comment.
 */
interface CommentInterface
{
    const STATE_UNPUBLISHED = 0;
    const STATE_PUBLISHED = 1;

    /**
     * Returns id.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns state.
     *
     * @return int
     */
    public function getState();

    /**
     * Set published.
     *
     * @return $this
     */
    public function publish();

    /**
     * Set unpublished.
     *
     * @return $this
     */
    public function unpublish();

    /**
     * Returns true if comment is published.
     *
     * @return bool
     */
    public function isPublished();

    /**
     * Returns message.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set message.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message);

    /**
     * Returns thread.
     *
     * @return ThreadInterface
     */
    public function getThread();

    /**
     * Set thread.
     *
     * @param ThreadInterface $thread
     *
     * @return $this
     */
    public function setThread(ThreadInterface $thread);
}
