<?php

namespace App\Service\Blog\Count;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Service\App\Messenger\MessageTransport;
use App\Service\Blog\Event\BlogCreatedEvent;
use App\Service\Language\LanguageService;
use App\Service\Media\Event\MediaCreatedEvent;
use App\Service\Media\Event\MediaDeletedEvent;
use App\Service\Post\Event\PostAuthorsChangedEvent;
use App\Service\Post\Event\PostCreatedEvent;
use App\Service\Post\Event\PostDeletedEvent;
use App\Service\Post\Event\PostTagsChangedEvent;
use App\Service\Post\Event\PostUpdatedEvent;
use App\Service\Post\Event\PostVariantPublishedEvent;
use App\Service\Post\Event\PostVariantUnpublishedEvent;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\Event\UserDeletedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CountListener
{
    public function __construct(
        private MessageBusInterface $bus,
        private LanguageService $languageService,
    ) {}

    #[AsEventListener]
    public function onBlogCreated(BlogCreatedEvent $event): void
    {
        // update immediately to show counts right after a blog created
        $this->dispatch(
            $event->blog,
            CountType::cases(), // update all
            sync: true,
        );
    }

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
        $primaryLanguage = $this->languageService->getPrimaryLanguage($event->post->getBlog());
        $primaryVariant = $event->post->getVariants()->filter(fn($variant) => $variant->getLanguage()->getId() === $primaryLanguage->getId())->first();

        if (!$primaryVariant || $primaryVariant->getStatus() !== PostVariantStatus::PUBLISHED) {
            return;
        }

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
    public function onPostTagsChanged(PostTagsChangedEvent $event): void
    {
        $primaryLanguage = $this->languageService->getPrimaryLanguage($event->post->getBlog());
        $primaryVariant = $event->post->getVariants()->filter(fn($variant) => $variant->getLanguage()->getId() === $primaryLanguage->getId())->first();

        if (!$primaryVariant || $primaryVariant->getStatus() !== PostVariantStatus::PUBLISHED) {
            return;
        }

        $oldIds = array_map(fn($tag) => $tag->getId(), $event->oldTags);
        $newIds = array_map(fn($tag) => $tag->getId(), $event->newTags);
        $allIds = array_unique(array_merge($oldIds, $newIds));

        $this->dispatch(
            $event->post->getBlog(),
            [CountType::POSTS_OF_TAGS],
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
     * @param array<int, CountType> $types
     * @param array<int, int[]> $entityIds
     */
    private function dispatch(
        Blog $blog,
        array $types,
        array $entityIds = [],
        bool $sync = false,
    ): void
    {
        $stamps = [];

        if ($sync) {
            $stamps[] = MessageTransport::syncStamp();
        }

        $this->bus->dispatch(
            new RecalculateCountMessage($blog->getId(), $types, $entityIds),
            $stamps
        );
    }
}
