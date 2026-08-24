<?php

namespace App\Service\Blog\Count;

use App\Entity\Blog;
use App\Service\Media\Event\MediaCreatedEvent;
use App\Service\Media\Event\MediaDeletedEvent;
use App\Service\Post\Event\PostCreatedEvent;
use App\Service\Post\Event\PostDeletedEvent;
use App\Service\Post\Event\PostUpdatedEvent;
use App\Service\Post\Event\PostVariantPublishedEvent;
use App\Service\Post\Event\PostVariantUnpublishedEvent;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\Event\UserDeletedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

class CountListener
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {}

    #[AsEventListener]
    public function onPostCreated(PostCreatedEvent $event): void
    {
        $this->dispatchPostCountTypes($event->post->getBlog());
    }

    #[AsEventListener]
    public function onPostDeleted(PostDeletedEvent $event): void
    {
        $this->dispatchPostCountTypes($event->post->getBlog());
    }

    #[AsEventListener]
    public function onPostUpdated(PostUpdatedEvent $event): void
    {
        $this->dispatchPostCountTypes($event->post->getBlog());
    }

    #[AsEventListener]
    public function onPostVariantPublished(PostVariantPublishedEvent $event): void
    {
        $this->dispatchPostCountTypes($event->variant->getPost()->getBlog());
    }

    #[AsEventListener]
    public function onPostVariantUnpublished(PostVariantUnpublishedEvent $event): void
    {
        $this->dispatchPostCountTypes($event->variant->getPost()->getBlog());
    }

    #[AsEventListener]
    public function onUserCreated(UserCreatedEvent $event): void
    {
        $this->dispatch($event->user->getBlog(), [CountType::USERS]);
    }

    #[AsEventListener]
    public function onUserDeleted(UserDeletedEvent $event): void
    {
        $this->dispatch($event->user->getBlog(), [CountType::USERS]);
    }

    #[AsEventListener]
    public function onMediaCreated(MediaCreatedEvent $event): void
    {
        $this->dispatch($event->media->getBlog(), [CountType::MEDIA]);
    }

    #[AsEventListener]
    public function onMediaDeleted(MediaDeletedEvent $event): void
    {
        $this->dispatch($event->media->getBlog(), [CountType::MEDIA]);
    }

    private function dispatchPostCountTypes(Blog $blog): void
    {
        $this->dispatch($blog, [CountType::POSTS, CountType::AUTHORS, CountType::TAGS]);
    }

    /**
     * @param CountType[] $types
     */
    private function dispatch(Blog $blog, array $types): void
    {
        $this->bus->dispatch(new RecalculateCountMessage($blog->getId(), $types));
    }
}
