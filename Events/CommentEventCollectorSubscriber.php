<?php

namespace Sulu\Bundle\CommentBundle\Events;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;

class CommentEventCollectorSubscriber implements EventSubscriber
{
    /**
     * @var CommentEventCollectorInterface
     */
    private $commentEventCollector;

    public function __construct(
        CommentEventCollectorInterface $commentEventDispatcher
    ) {
        $this->commentEventCollector = $commentEventDispatcher;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onClear,
            Events::postFlush,
        ];
    }

    public function onClear(OnClearEventArgs $args): void
    {
        $this->commentEventCollector->clear();
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $this->commentEventCollector->dispatch();
    }
}
