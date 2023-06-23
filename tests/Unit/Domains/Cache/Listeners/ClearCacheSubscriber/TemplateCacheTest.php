<?php

namespace Tests\Unit\Domains\Cache\Listeners;

use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Events\BlogVariantUpdatedEvent;
use App\Domains\Cache\CacheService;
use App\Domains\Cache\Listeners\ClearCacheSubscriber;
use App\Domains\Language\Events\LanguageChangedEvent;
use App\Domains\Navigation\Events\NavigationChangedEvent;
use App\Domains\Navigation\Events\NavigationVariantChangedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Post\Events\PostVariantDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Route\Events\RouteChangedEvent;
use App\Domains\Tag\Events\TagCreatedEvent;
use App\Domains\Tag\Events\TagDeletedEvent;
use App\Domains\Tag\Events\TagUpdatedEvent;
use App\Domains\Tag\Events\TagVariantDeletedEvent;
use App\Domains\Tag\Events\TagVariantUpdatedEvent;
use App\Domains\Theme\Events\ConfigEditedEvent;
use App\Domains\Theme\Events\TemplateEditedEvent;
use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\Events\UserDeletedEvent;
use App\Domains\User\Events\UserUpdatedEvent;
use App\Domains\User\Events\UserVariantDeletedEvent;
use App\Domains\User\Events\UserVariantUpdatedEvent;
use App\Models\Language;
use App\Models\Navigation;
use App\Models\NavigationVariant;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\Route;
use App\Models\ThemeFile;
use App\Models\User;
use App\Models\UserVariant;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;

it('is attached', function () {
    Event::fake();

    // blog
    Event::assertListening(BlogUpdatedEvent::class, [ClearCacheSubscriber::class, 'onBlogUpdate']);
    Event::assertListening(BlogVariantUpdatedEvent::class, [ClearCacheSubscriber::class, 'onBlogVariantUpdate']);

    // posts
    Event::assertListening(PostUpdatedEvent::class, [ClearCacheSubscriber::class, 'onPostUpdate']);
    Event::assertListening(PostDeletedEvent::class, [ClearCacheSubscriber::class, 'onPostDelete']);

    // post variants
    Event::assertListening(PostVariantUpdatedEvent::class, [ClearCacheSubscriber::class, 'onPostVariantUpdate']);
    Event::assertListening(PostVariantDeletedEvent::class, [ClearCacheSubscriber::class, 'onPostVariantDelete']);

    // user
    $userEventListener = [ClearCacheSubscriber::class, 'onUserEvent'];
    $userVariantEventListener = [ClearCacheSubscriber::class, 'onUserVariantEvent'];
    Event::assertListening(UserCreatedEvent::class, $userEventListener);
    Event::assertListening(UserUpdatedEvent::class, $userEventListener);
    Event::assertListening(UserDeletedEvent::class, $userEventListener);
    Event::assertListening(UserVariantUpdatedEvent::class, $userVariantEventListener);
    Event::assertListening(UserVariantDeletedEvent::class, $userVariantEventListener);

    // tag
    $tagEventListener = [ClearCacheSubscriber::class, 'onTagEvent'];
    $tagVariantEventListener = [ClearCacheSubscriber::class, 'onTagVariantEvent'];
    Event::assertListening(TagCreatedEvent::class, $tagEventListener);
    Event::assertListening(TagUpdatedEvent::class, $tagEventListener);
    Event::assertListening(TagDeletedEvent::class, $tagEventListener);
    Event::assertListening(TagVariantUpdatedEvent::class, $tagVariantEventListener);
    Event::assertListening(TagVariantDeletedEvent::class, $tagVariantEventListener);

    // navigation
    Event::assertListening(NavigationChangedEvent::class, [ClearCacheSubscriber::class, 'onNavigationEvent']);
    Event::assertListening(NavigationVariantChangedEvent::class, [ClearCacheSubscriber::class, 'onNavigationVariantEvent']);

    // language
    Event::assertListening(LanguageChangedEvent::class, [ClearCacheSubscriber::class, 'onLanguageEvent']);

    // route
    Event::assertListening(RouteChangedEvent::class, [ClearCacheSubscriber::class, 'onRouteEvent']);

    // theme files
    Event::assertListening(TemplateEditedEvent::class, [ClearCacheSubscriber::class, 'onTemplateEditedEvent']);
});

beforeEach(function () {
    $this->templateMock = function ($times = 1) {
        $this->mock(
            CacheService::class,
            fn (MockInterface $mock) =>
            $mock
                ->shouldReceive('clearTemplateCache')
                ->times($times)
        )->makePartial();
    };

    $this->templateNoMock = function () {
        $this->mock(CacheService::class, function (MockInterface $mock) {
            $mock->shouldReceive('clearTemplateCache')
                ->never();
        })->makePartial();
    };
});

// BLOG ===

it('clears cache when a blog is updated', function () {
    ($this->templateMock)();

    $blog = blog();

    $event = new BlogUpdatedEvent($blog, $blog);
    $listener = new ClearCacheSubscriber();
    $listener->onBlogUpdate($event);
});

it('clears cache when a blog variant is updated', function () {
    ($this->templateMock)();

    $blog = blog();
    addPrimaryLanguage($blog);
    addBlogVariants($blog);

    $event = new BlogVariantUpdatedEvent($blog->variants[0]);
    $listener = new ClearCacheSubscriber();
    $listener->onBlogVariantUpdate($event);
});

// POST ===

it('clears cache when editing a post', function () {
    ($this->templateMock)();

    $blog = blog();
    addPrimaryLanguage($blog);
    $language = $blog->languages[0];

    $post = Post::factory()
        ->has(PostVariant::factory()->state([
            'language_id' => $language->id,
            'status' => 'published',
        ]), 'variants')
        ->create(['blog_id' => $blog]);

    $post->code_head = 'new-code';

    $event = new PostUpdatedEvent($post);
    $listener = new ClearCacheSubscriber();
    $listener->onPostUpdate($event);
});

it('does not clear cache when editing a post if the primary variant is not published', function () {
    ($this->templateNoMock)();

    $blog = blog();
    addPrimaryLanguage($blog);
    $language = $blog->languages[0];

    $post = Post::factory()
        ->has(PostVariant::factory()->state([
            'language_id' => $language->id,
            'status' => 'draft',
        ]), 'variants')
        ->create(['blog_id' => $blog]);

    $post->code_head = 'new-code';

    $event = new PostUpdatedEvent($post);
    $listener = new ClearCacheSubscriber();
    $listener->onPostUpdate($event);
});

it('clears cache when a post is deleted', function () {
    ($this->templateMock)();

    $blog = blog();

    $post = Post::factory()->create(['blog_id' => $blog]);

    $post->code_head = 'new-code';

    $event = new PostDeletedEvent($post);
    $listener = new ClearCacheSubscriber();
    $listener->onPostDelete($event);
});

// POST VARIANTS ===

it('clears cache on post variant status change', function () {
    ($this->templateMock)();

    $variant = PostVariant::factory()->create();
    $variant->status = 'published';
    $event = new PostVariantUpdatedEvent($variant, $variant);

    $listener = new ClearCacheSubscriber();
    $listener->onPostVariantUpdate($event);
});

it('does not clear cache when attrs changes on non-published posts', function () {
    ($this->templateNoMock)();

    $variant = PostVariant::factory()->create(['status' => 'draft']);
    $variant->content = 'Hey';
    $event = new PostVariantUpdatedEvent($variant, $variant);

    $listener = new ClearCacheSubscriber();
    $listener->onPostVariantUpdate($event);
});

it('clears cache if the post is published', function () {
    ($this->templateMock)();

    $variant = PostVariant::factory()->create(['status' => 'published']);
    $variant->content = 'Hey';
    $event = new PostVariantUpdatedEvent($variant, $variant);

    $listener = new ClearCacheSubscriber();
    $listener->onPostVariantUpdate($event);
});

it('clears cache on post variant status delete', function () {
    ($this->templateMock)();

    $variant = PostVariant::factory()->create();
    $event = new PostVariantDeletedEvent($variant);

    $listener = new ClearCacheSubscriber();
    $listener->onPostVariantDelete($event);
});

it('clears cache on user events', function () {
    ($this->templateMock)(3);

    $user = User::factory()->create();
    $createEvent = new UserCreatedEvent($user);
    $updateEvent = new UserUpdatedEvent($user);
    $deleteEvent = new UserDeletedEvent($user);

    $listener = new ClearCacheSubscriber();
    $listener->onUserEvent($createEvent);
    $listener->onUserEvent($updateEvent);
    $listener->onUserEvent($deleteEvent);
});

it('clears cache on user variant events', function () {
    ($this->templateMock)(2);

    $variant = UserVariant::factory()->create();
    $updateEvent = new UserVariantUpdatedEvent($variant);
    $deleteEvent = new UserVariantDeletedEvent($variant);

    $listener = new ClearCacheSubscriber();
    $listener->onUserVariantEvent($updateEvent);
    $listener->onUserVariantEvent($deleteEvent);
});

it('clears cache on tag events', function () {
    ($this->templateMock)(3);

    $blog = blog();
    $user = addTag($blog);
    $createEvent = new TagCreatedEvent($user);
    $updateEvent = new TagUpdatedEvent($user);
    $deleteEvent = new TagDeletedEvent($user);

    $listener = new ClearCacheSubscriber();
    $listener->onTagEvent($createEvent);
    $listener->onTagEvent($updateEvent);
    $listener->onTagEvent($deleteEvent);
});

it('clears cache on tag variant events', function () {
    ($this->templateMock)(2);

    $blog = blog();
    addPrimaryLanguage($blog);
    $tag = addTag($blog);
    $variant = $tag->variants[0];
    $updateEvent = new TagVariantUpdatedEvent($variant);
    $deleteEvent = new TagVariantDeletedEvent($variant);

    $listener = new ClearCacheSubscriber();
    $listener->onTagVariantEvent($updateEvent);
    $listener->onTagVariantEvent($deleteEvent);
});

it('clears cache on navigation event', function () {
    ($this->templateMock)();

    $blog = blog();
    $navigation = Navigation::factory()->create(['blog_id' => $blog]);
    $event = new NavigationChangedEvent($navigation);

    $listener = new ClearCacheSubscriber();
    $listener->onNavigationEvent($event);
});

it('clears cache on navigation variant event', function () {
    ($this->templateMock)();

    $blog = blog();
    $language = addPrimaryLanguage($blog);
    $navigation = Navigation::factory()->create(['blog_id' => $blog]);
    $variant = NavigationVariant::factory()->create(['navigation_id' => $navigation, 'language_id' => $language]);
    $event = new NavigationVariantChangedEvent($variant);

    $listener = new ClearCacheSubscriber();
    $listener->onNavigationVariantEvent($event);
});

it('clears cache on language event', function () {
    ($this->templateMock)();

    $language = Language::factory()->create();
    $event = new LanguageChangedEvent($language);

    $listener = new ClearCacheSubscriber();
    $listener->onLanguageEvent($event);
});

it('clears cache on route event', function () {
    ($this->templateMock)();

    $route = Route::factory()->create();
    $event = new RouteChangedEvent($route);

    $listener = new ClearCacheSubscriber();
    $listener->onRouteEvent($event);
});

it('clears cache on template editing', function() {
    ($this->templateMock)();

    $file = ThemeFile::factory()->create();
    $event = new TemplateEditedEvent($file);

    $listener = new ClearCacheSubscriber();
    $listener->onTemplateEditedEvent($event);
});

it('clears cache when config is updated', function() {
    ($this->templateMock)();

    $file = ThemeFile::factory()->create(['name' => 'config.yaml']);
    $event = new ConfigEditedEvent($file);

    $listener = new ClearCacheSubscriber();
    $listener->onConfigEditedEvent($event);

});