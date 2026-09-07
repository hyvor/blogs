<?php

namespace App\Tests\Service\Cache;

use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\PostVariantStatus;
use App\Service\Blog\Event\BlogHostingChangedEvent;
use App\Service\Blog\Event\BlogUpdatedEvent;
use App\Service\Blog\Event\BlogVariantUpdatedEvent;
use App\Service\Cache\BlogCacheService;
use App\Service\Cache\ClearCacheListener;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Service\Cache\Event\CacheClearSingleEvent;
use App\Service\Cache\Event\CacheClearTemplatesEvent;
use App\Service\Hosting\Exception\PendingHostingChangeException;
use App\Service\Hosting\HostingChangeService;
use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Media\Event\MediaCreatedEvent;
use App\Service\Media\Event\MediaDeletedEvent;
use App\Service\Navigation\Event\NavigationChangedEvent;
use App\Service\Navigation\Event\NavigationVariantChangedEvent;
use App\Service\Post\Event\PostDeletedEvent;
use App\Service\Post\Event\PostUpdatedEvent;
use App\Service\Post\Event\PostVariantDeletedEvent;
use App\Service\Post\Event\PostVariantPublishedEvent;
use App\Service\Post\Event\PostVariantUnpublishedEvent;
use App\Service\Post\Event\PostVariantUpdatedEvent;
use App\Service\Redirect\Event\RedirectChangedEvent;
use App\Service\Route\Event\RouteChangedEvent;
use App\Service\Tag\Event\TagCreatedEvent;
use App\Service\Tag\Event\TagDeletedEvent;
use App\Service\Tag\Event\TagUpdatedEvent;
use App\Service\Tag\Event\TagVariantDeletedEvent;
use App\Service\Tag\Event\TagVariantUpdatedEvent;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\Event\UserDeletedEvent;
use App\Service\User\Event\UserUpdatedEvent;
use App\Service\User\Event\UserVariantDeletedEvent;
use App\Service\User\Event\UserVariantUpdatedEvent;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\NavigationFactory;
use App\Tests\Factory\NavigationVariantFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RedirectFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ClearCacheListener::class)]
#[UsesClass(BlogCacheService::class)]
#[UsesClass(HostingChangeService::class)]
class ClearCacheListenerTest extends KernelTestCase
{
    private function dispatch(object $event): void
    {
        $this->getService(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class)->dispatch($event);
    }

    public function test_navigation_changed_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $nav = NavigationFactory::createOne(['blog' => $blog]);

        $this->dispatch(new NavigationChangedEvent($nav));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_navigation_variant_changed_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $nav = NavigationFactory::createOne(['blog' => $blog]);
        $lang = LanguageFactory::createOne(['blog' => $blog]);
        $variant = NavigationVariantFactory::createOne([
            'navigation' => $nav,
            'language' => $lang,
        ]);

        $this->dispatch(new NavigationVariantChangedEvent($variant));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_language_changed_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOne(['blog' => $blog]);

        $this->dispatch(new LanguageChangedEvent($lang));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_redirect_changed_clears_single_cache_for_non_dynamic(): void
    {
        $blog = BlogFactory::createOne();
        $redirect = RedirectFactory::createOne([
            'blog' => $blog,
            'path' => '/old-path',
            'dynamic' => false,
        ]);

        $this->dispatch(new RedirectChangedEvent($redirect));

        $this->getEd()->assertDispatched(CacheClearSingleEvent::class);
        $this->getEd()->assertNotDispatched(CacheClearAllEvent::class);
    }

    public function test_redirect_changed_clears_all_cache_for_dynamic(): void
    {
        $blog = BlogFactory::createOne();
        $redirect = RedirectFactory::createOne([
            'blog' => $blog,
            'path' => '/wildcard',
            'dynamic' => true,
        ]);

        $this->dispatch(new RedirectChangedEvent($redirect));

        $this->getEd()->assertDispatched(CacheClearAllEvent::class);
        $this->getEd()->assertNotDispatched(CacheClearSingleEvent::class);
    }

    public function test_route_changed_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $route = RouteFactory::createOne(['blog' => $blog]);

        $this->dispatch(new RouteChangedEvent($route));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_blog_updated_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $blogOld = clone $blog;

        $this->dispatch(new BlogUpdatedEvent($blog, $blogOld));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
        $this->getEd()->assertNotDispatched(CacheClearAllEvent::class);
    }

    /** @throws PendingHostingChangeException */
    public function test_blog_hosting_changed_clears_all_cache(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $hostingChangeService = $this->getService(HostingChangeService::class);
        $hostingChange = $hostingChangeService->startHostingChange($blog, BlogHostingAt::SELF, toHostingUrl: 'https://example.com');

        $this->dispatch(new BlogHostingChangedEvent($hostingChange));

        $this->getEd()->assertDispatched(CacheClearAllEvent::class);
        $this->getEd()->assertNotDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_blog_variant_updated_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOne(['blog' => $blog]);
        $variant = BlogVariantFactory::createOne(['blog' => $blog, 'language' => $lang]);
        $variantOld = clone $variant;

        $this->dispatch(new BlogVariantUpdatedEvent($variant, $variantOld));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_tag_created_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $tag = TagFactory::createOne(['blog' => $blog]);

        $this->dispatch(new TagCreatedEvent($tag));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_tag_updated_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $tag = TagFactory::createOne(['blog' => $blog]);
        $tagOld = clone $tag;

        $this->dispatch(new TagUpdatedEvent($tag, $tagOld));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_tag_deleted_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $tag = TagFactory::createOne(['blog' => $blog]);

        $this->dispatch(new TagDeletedEvent($tag));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_tag_variant_updated_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $tag = TagFactory::createOne(['blog' => $blog]);
        $lang = LanguageFactory::createOne(['blog' => $blog]);
        $variant = TagVariantFactory::createOne(['tag' => $tag, 'language' => $lang]);
        $variantOld = clone $variant;

        $this->dispatch(new TagVariantUpdatedEvent($variant, $variantOld));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_tag_variant_deleted_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $tag = TagFactory::createOne(['blog' => $blog]);
        $lang = LanguageFactory::createOne(['blog' => $blog]);
        $variant = TagVariantFactory::createOne(['tag' => $tag, 'language' => $lang]);

        $this->dispatch(new TagVariantDeletedEvent($variant));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_media_created_clears_single_cache(): void
    {
        $blog = BlogFactory::createOne();
        $media = MediaFactory::createOne(['blog' => $blog, 'name' => 'photo.png']);

        $this->dispatch(new MediaCreatedEvent($media));

        $this->getEd()->assertDispatched(CacheClearSingleEvent::class);
        $this->getEd()->assertNotDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_media_deleted_clears_single_cache(): void
    {
        $blog = BlogFactory::createOne();
        $media = MediaFactory::createOne(['blog' => $blog, 'name' => 'photo.png']);

        $this->dispatch(new MediaDeletedEvent($media));

        $this->getEd()->assertDispatched(CacheClearSingleEvent::class);
        $this->getEd()->assertNotDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_user_created_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog]);

        $this->dispatch(new UserCreatedEvent($user));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_user_updated_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog]);
        $userOld = clone $user;

        $this->dispatch(new UserUpdatedEvent($user, $userOld));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_user_deleted_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog]);

        $this->dispatch(new UserDeletedEvent($user));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_user_variant_updated_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog]);
        $lang = LanguageFactory::createOne(['blog' => $blog]);
        $variant = UserVariantFactory::createOne(['user' => $user, 'language' => $lang]);
        $variantOld = clone $variant;

        $this->dispatch(new UserVariantUpdatedEvent($variant, $variantOld));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_user_variant_deleted_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog]);
        $lang = LanguageFactory::createOne(['blog' => $blog]);
        $variant = UserVariantFactory::createOne(['user' => $user, 'language' => $lang]);

        $this->dispatch(new UserVariantDeletedEvent($variant));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_post_variant_updated_clears_template_cache_when_published(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $lang,
            'status' => PostVariantStatus::PUBLISHED,
        ]);

        $this->dispatch(new PostVariantUpdatedEvent($variant));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_post_variant_updated_does_not_clear_template_cache_when_not_published(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $lang,
            'status' => PostVariantStatus::DRAFT,
        ]);

        $this->dispatch(new PostVariantUpdatedEvent($variant));

        $this->getEd()->assertNotDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_post_variant_published_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $lang]);

        $this->dispatch(new PostVariantPublishedEvent($variant));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_post_variant_unpublished_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $lang]);

        $this->dispatch(new PostVariantUnpublishedEvent($variant));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_post_variant_deleted_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $lang]);

        $this->dispatch(new PostVariantDeletedEvent($variant));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_post_deleted_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $post = PostFactory::createOne(['blog' => $blog]);

        $this->dispatch(new PostDeletedEvent($post));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_post_updated_clears_template_cache_when_primary_variant_published(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $lang,
            'status' => PostVariantStatus::PUBLISHED,
        ]);

        $this->dispatch(new PostUpdatedEvent($post));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_post_updated_does_not_clear_template_cache_when_primary_variant_not_published(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $lang,
            'status' => PostVariantStatus::DRAFT,
        ]);

        $this->dispatch(new PostUpdatedEvent($post));

        $this->getEd()->assertNotDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_post_updated_does_not_clear_template_cache_when_no_primary_variant(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);

        $this->dispatch(new PostUpdatedEvent($post));

        $this->getEd()->assertNotDispatched(CacheClearTemplatesEvent::class);
    }
}
