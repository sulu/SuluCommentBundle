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

use Sulu\Component\Persistence\Repository\ORM\EntityRepository;

/**
 * Repository for querying comments.
 */
class ThreadRepository extends EntityRepository implements ThreadRepositoryInterface
{
}
