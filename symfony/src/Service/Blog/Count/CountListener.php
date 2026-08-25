<?php

namespace App\Service\Blog\Count;

use App\Entity\Blog;
use App\Service\Media\Event\MediaCreatedEvent;
use App\Service\Media\Event\MediaDeletedEvent;
use App\Service\Post\Event\PostAuthorsChangedEvent;
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
    public function onPostAuthorsChanged(PostAuthorsChangedEvent $event): void
    {
        $oldIds = array_map(fn($user) => $user->getId(), $event->oldAuthors);
        $newIds = array_map(fn($user) => $user->getId(), $event->newAuthors);
        $allIds = array_unique(array_merge($oldIds, $newIds));

        $this->dispatch(
            $event->post->getBlog(),
            [CountType::POSTS_OF_USERS],
            [$allIds]
        );
    }

    #[AsEventListener]
    public function onUserCreated(UserCreatedEvent $event): void
    {
        $this->dispatch($event->user->getBlog(), [CountType::USERS_OF_BLOG]);
    }

    #[AsEventListener]
    public function onUserDeleted(UserDeletedEvent $event): void
    {
        $this->dispatch($event->user->getBlog(), [CountType::USERS_OF_BLOG]);
    }

    #[AsEventListener]
    public function onMediaCreated(MediaCreatedEvent $event): void
    {
        $this->dispatch($event->media->getBlog(), [CountType::MEDIA_OF_BLOG]);
    }

    #[AsEventListener]
    public function onMediaDeleted(MediaDeletedEvent $event): void
    {
        $this->dispatch($event->media->getBlog(), [CountType::MEDIA_OF_BLOG]);
    }

    private function dispatchPostCountTypes(Blog $blog): void
    {
        $this->dispatch($blog, [CountType::POSTS_OF_BLOG, CountType::POSTS_OF_USERS, CountType::POSTS_OF_TAGS]);
    }

    /**
     * @param CountType[] $types
     * @param array<int[]> $entityIds
     */
    private function dispatch(
        Blog $blog,
        array $types,
        array $entityIds = [],
    ): void
    {
        $this->bus->dispatch(new RecalculateCountMessage($blog->getId(), $types, $entityIds));
    }
}
