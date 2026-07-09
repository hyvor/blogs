<?php

namespace App\Service\Cache;

use App\Entity\Enum\PostVariantStatus;
use App\Service\Blog\Event\BlogHostingChangedEvent;
use App\Service\Blog\Event\BlogUpdatedEvent;
use App\Service\Blog\Event\BlogVariantUpdatedEvent;
use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Language\LanguageService;
use App\Service\Media\Event\MediaCreatedEvent;
use App\Service\Media\Event\MediaDeletedEvent;
use App\Service\Navigation\Event\NavigationChangedEvent;
use App\Service\Navigation\Event\NavigationVariantChangedEvent;
use App\Service\Redirect\Event\RedirectChangedEvent;
use App\Service\Route\Event\RouteChangedEvent;
use App\Service\Tag\Event\TagCreatedEvent;
use App\Service\Tag\Event\TagDeletedEvent;
use App\Service\Tag\Event\TagUpdatedEvent;
use App\Service\Tag\Event\TagVariantDeletedEvent;
use App\Service\Tag\Event\TagVariantUpdatedEvent;
use App\Service\Theme\Event\AssetEditedEvent;
use App\Service\Theme\Event\ConfigEditedEvent;
use App\Service\Theme\Event\LangEditedEvent;
use App\Service\Theme\Event\StylesEditedEvent;
use App\Service\Theme\Event\TemplateEditedEvent;
use App\Service\Post\Event\PostDeletedEvent;
use App\Service\Post\Event\PostUpdatedEvent;
use App\Service\Post\Event\PostVariantDeletedEvent;
use App\Service\Post\Event\PostVariantPublishedEvent;
use App\Service\Post\Event\PostVariantUnpublishedEvent;
use App\Service\Post\Event\PostVariantUpdatedEvent;
use App\Service\Post\PostService;
use App\Service\Route\PermalinkService;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\Event\UserDeletedEvent;
use App\Service\User\Event\UserUpdatedEvent;
use App\Service\User\Event\UserVariantDeletedEvent;
use App\Service\User\Event\UserVariantUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;


class ClearCacheListener
{
    public function __construct(
        private BlogCacheService $cacheService,
        private PermalinkService $permalinkService,
        private EntityManagerInterface $em,
        private LanguageService $languageService,
        private PostService $postService,
    ) {}

    #[AsEventListener]
    public function onNavigationChanged(NavigationChangedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->navigation->getBlog());
    }

    #[AsEventListener]
    public function onNavigationVariantChanged(NavigationVariantChangedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getNavigation()->getBlog());
    }

    #[AsEventListener]
    public function onLanguageChanged(LanguageChangedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->language->getBlog());
    }

    #[AsEventListener]
    public function onRouteChanged(RouteChangedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->route->getBlog());
    }

    #[AsEventListener]
    public function onBlogUpdated(BlogUpdatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->blog);
    }

    #[AsEventListener]
    public function onBlogHostingChanged(BlogHostingChangedEvent $event): void
    {
        $this->cacheService->clearAllCache($event->hostingChange->getBlog());
    }

    #[AsEventListener]
    public function onBlogVariantUpdated(BlogVariantUpdatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getBlog());
    }

    #[AsEventListener]
    public function onTagCreated(TagCreatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->tag->getBlog());
    }

    #[AsEventListener]
    public function onTagUpdated(TagUpdatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->tag->getBlog());
    }

    #[AsEventListener]
    public function onTagDeleted(TagDeletedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->tag->getBlog());
    }

    #[AsEventListener]
    public function onTagVariantUpdated(TagVariantUpdatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getTag()->getBlog());
    }

    #[AsEventListener]
    public function onTagVariantDeleted(TagVariantDeletedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getTag()->getBlog());
    }

    #[AsEventListener]
    public function onPostUpdated(PostUpdatedEvent $event): void
    {
        $post = $event->post;
        $blog = $post->getBlog();

        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
        $primaryVariant = $this->postService->getPostVariantByPostAndLanguage($post, $primaryLanguage);

        if ($primaryVariant === null || $primaryVariant->getStatus() !== PostVariantStatus::PUBLISHED) {
            return;
        }

        $this->cacheService->clearTemplateCache($blog);
    }

    #[AsEventListener]
    public function onPostDeleted(PostDeletedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->post->getBlog());
    }

    #[AsEventListener]
    public function onPostVariantUpdated(PostVariantUpdatedEvent $event): void
    {
        // status transitions clear the cache via onPostVariantPublished/onPostVariantUnpublished;
        // here we only need to catch content edits to an already-published variant
        if ($event->variant->getStatus() === PostVariantStatus::PUBLISHED) {
            $this->cacheService->clearTemplateCache($event->variant->getPost()->getBlog());
        }
    }

    #[AsEventListener]
    public function onPostVariantPublished(PostVariantPublishedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getPost()->getBlog());
    }

    #[AsEventListener]
    public function onPostVariantUnpublished(PostVariantUnpublishedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getPost()->getBlog());
    }

    #[AsEventListener]
    public function onPostVariantDeleted(PostVariantDeletedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getPost()->getBlog());
    }

    #[AsEventListener]
    public function onRedirectChanged(RedirectChangedEvent $event): void
    {
        $redirect = $event->redirect;
        $blog = $redirect->getBlog();

        if ($redirect->isDynamic()) {
            $this->cacheService->clearAllCache($blog);
        } else {
            $this->cacheService->clearSingleCache($blog, $redirect->getPath());
            // clear the old path cache as well if the redirect was updated
            if ($event->oldRedirect && $redirect->getPath() !== $event->oldRedirect->getPath()) {
                $this->cacheService->clearSingleCache($blog, $event->oldRedirect->getPath());
            }
        }
    }

    #[AsEventListener]
    public function onTemplateEdited(TemplateEditedEvent $event): void
    {
        $blog = $event->file->getBlog();
        $this->cacheService->clearTemplateCache($blog);
    }

    #[AsEventListener]
    public function onConfigEdited(ConfigEditedEvent $event): void
    {
        $blog = $event->file->getBlog();
        $this->cacheService->clearTemplateCache($blog);
    }

    #[AsEventListener]
    public function onLangEdited(LangEditedEvent $event): void
    {
        $blog = $event->file->getBlog();
        $this->cacheService->clearTemplateCache($blog);
    }

    #[AsEventListener]
    public function onAssetEdited(AssetEditedEvent $event): void
    {
        $path = $this->permalinkService->getAssetPermalink($event->name, $event->blog, true);
        $this->cacheService->clearSingleCache($event->blog, $path);
    }

    #[AsEventListener]
    public function onStylesEdited(StylesEditedEvent $event): void
    {
        $this->cacheService->clearSingleCache($event->blog, '/styles.css');
        $this->cacheService->clearTemplateCache($event->blog);

        // update style version to force cache busting for styles.css
        $meta = clone $event->blog->getMeta();
        $meta->cache_version_styles++;
        $event->blog->setMeta($meta);
        $this->em->flush();
    }

    #[AsEventListener]
    public function onUserCreated(UserCreatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->user->getBlog());
    }

    #[AsEventListener]
    public function onUserUpdated(UserUpdatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->user->getBlog());
    }

    #[AsEventListener]
    public function onUserDeleted(UserDeletedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->user->getBlog());
    }

    #[AsEventListener]
    public function onUserVariantUpdated(UserVariantUpdatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getUser()->getBlog());
    }

    #[AsEventListener]
    public function onUserVariantDeleted(UserVariantDeletedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getUser()->getBlog());
    }

    #[AsEventListener]
    public function onMediaCreated(MediaCreatedEvent $event): void
    {
        $media = $event->media;
        $blog = $media->getBlog();
        $path = $this->permalinkService->getMediaPermalink($media, $blog, true);
        $this->cacheService->clearSingleCache($blog, $path);
    }

    #[AsEventListener]
    public function onMediaDeleted(MediaDeletedEvent $event): void
    {
        $media = $event->media;
        $blog = $media->getBlog();
        $path = $this->permalinkService->getMediaPermalink($media, $blog, true);
        $this->cacheService->clearSingleCache($blog, $path);
    }
}
