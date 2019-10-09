<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommentBundle\EventSubscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Sulu\Bundle\CommentBundle\Entity\Comment;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CommentSerializationSubscriber implements EventSubscriberInterface
{
    /**
     * @var MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(MediaManagerInterface $mediaManager, RequestStack $requestStack)
    {
        $this->mediaManager = $mediaManager;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'format' => 'json',
                'method' => 'onPostSerialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        $context = $event->getContext();

        if (!$context->hasAttribute('groups')
            || !in_array('commentWithAvatar', $context->getAttribute('groups'))) {
            return;
        }

        /** @var Comment $comment */
        $comment = $event->getObject();
        if (!$comment instanceof Comment || !$creator = $comment->getCreator()) {
            return;
        }

        /** @var SerializationVisitorInterface $visitor */
        $visitor = $event->getVisitor();

        $contact = $creator->getContact();
        $visitor->visitProperty(
            new StaticPropertyMetadata('', 'creatorId', $contact->getId()),
            $contact->getId()
        );

        if (!$avatar = $contact->getAvatar()) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $locale = $request ? $request->getLocale() : 'en';

        $avatar = $this->mediaManager->getById($avatar->getId(), $locale);
        $serializedAvatar = [
            'id' => $avatar->getId(),
            'title' => $avatar->getTitle(),
            'description' => $avatar->getDescription(),
            'credits' => $avatar->getCredits(),
            'copyright' => $avatar->getCopyright(),
            'name' => $avatar->getName(),
            'formats' => $avatar->getFormats(),
            'url' => $avatar->getUrl(),
        ];
        $visitor->visitProperty(
            new StaticPropertyMetadata('', 'creatorAvatar', $serializedAvatar),
            $serializedAvatar
        );
    }
}
