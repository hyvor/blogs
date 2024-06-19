<?php

namespace Tests\Unit\Domains\Webhook\Listeners;

use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Events\BlogVariantUpdatedEvent;
use App\Domains\Cache\Events\CacheClearAllEvent;
use App\Domains\Cache\Events\CacheClearSingleEvent;
use App\Domains\Cache\Events\CacheClearTemplatesEvent;
use App\Domains\Language\Events\LanguageChangedEvent;
use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Media\Events\MediaDeletedEvent;
use App\Domains\Navigation\Events\NavigationChangedEvent;
use App\Domains\Navigation\Events\NavigationVariantChangedEvent;
use App\Domains\Post\Events\PostCreatedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Post\Events\PostVariantCreatedEvent;
use App\Domains\Post\Events\PostVariantDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Route\Events\RouteChangedEvent;
use App\Domains\Tag\Events\TagCreatedEvent;
use App\Domains\Tag\Events\TagDeletedEvent;
use App\Domains\Tag\Events\TagUpdatedEvent;
use App\Domains\Tag\Events\TagVariantCreatedEvent;
use App\Domains\Tag\Events\TagVariantDeletedEvent;
use App\Domains\Tag\Events\TagVariantUpdatedEvent;
use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\Events\UserDeletedEvent;
use App\Domains\User\Events\UserUpdatedEvent;
use App\Domains\User\Events\UserVariantCreatedEvent;
use App\Domains\User\Events\UserVariantDeletedEvent;
use App\Domains\User\Events\UserVariantUpdatedEvent;
use App\Domains\Webhook\Jobs\WebhookDeliveryJob;
use App\Domains\Webhook\Listeners\WebhookSubscriber;
use App\Models\Navigation;
use App\Models\NavigationVariant;
use Database\Factories\BlogVariantFactory;
use Database\Factories\LanguageFactory;
use Database\Factories\MediaFactory;
use Database\Factories\PostFactory;
use Database\Factories\PostVariantFactory;
use Database\Factories\RouteFactory;
use Database\Factories\TagFactory;
use Database\Factories\TagVariantFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserVariantFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Data\Enums\WebhookEventEnum;

it('listens', function () {
    Event::fake();

    Event::assertListening(CacheClearSingleEvent::class, [WebhookSubscriber::class, 'onCacheClearSingleEvent']);
    Event::assertListening(CacheClearTemplatesEvent::class, [WebhookSubscriber::class, 'onCacheClearTemplatesEvent']);
    Event::assertListening(CacheClearAllEvent::class, [WebhookSubscriber::class, 'onCacheClearAllEvent']);
});

it('does not call delivery job when webhooks are not registered', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, WebhookEventEnum::CACHE_TEMPLATES); // wrong event

    $event = new CacheClearSingleEvent($blog, '/test');
    $listener = new WebhookSubscriber();
    $listener->onCacheClearSingleEvent($event);

    Queue::assertNotPushed(WebhookDeliveryJob::class);
});

it('calls webhook delivery job on blog updated event', function () {
    Queue::fake();

    $blog = blog();
    $blogOriginal = $blog;
    $blog->subdomain = 'new-subdomain';

    createWebhookFor($blog, WebhookEventEnum::BLOGS_UPDATED);

    $event = new BlogUpdatedEvent($blog, $blogOriginal);
    $listener = new WebhookSubscriber();
    $listener->onBlogUpdatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($blog){
        expect($job->delivery->event)->toBe(WebhookEventEnum::BLOGS_UPDATED);
        expect($job->delivery->data['blog']['id'])->toBe($blog->id);
        return true;
    });
});

it('calls webhook delivery job on blog variant updated event', function () {
    Queue::fake();

    $blog = blog();
    $blogVariant = BlogVariantFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::BLOGS_UPDATED);

    $event = new BlogVariantUpdatedEvent($blogVariant);
    $listener = new WebhookSubscriber();
    $listener->onBlogVariantUpdatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($blog){
        expect($job->delivery->event)->toBe(WebhookEventEnum::BLOGS_UPDATED);
        expect($job->delivery->data['blog']['id'])->toBe($blog->id);
        return true;
    });
});

it('calls webhook delivery job on post created event', function () {
    Queue::fake();

    $blog = blog();
    $post = PostFactory::new()->create([
        'blog_id' => $blog->id,
    ]);
    createWebhookFor($blog, WebhookEventEnum::POST_CREATED);

    $event = new PostCreatedEvent($post);
    $listener = new WebhookSubscriber();
    $listener->onPostCreatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($post){
        expect($job->delivery->event)->toBe(WebhookEventEnum::POST_CREATED);
        expect($job->delivery->data['post']['id'])->toBe($post->id);
        return true;
    });
});

it('calls webhook delivery job on post variant created event', function () {
    Queue::fake();

    $blog = blog();
    $language = LanguageFactory::new()->create([
        'blog_id' => $blog->id,
        'is_primary' => true,
    ]);
    $post = PostFactory::new()->create([
        'blog_id' => $blog->id,
    ]);
    $postVariant = PostVariantFactory::new()->create([
        'post_id' => $post->id,
        'language_id' => $language->id,
    ]);
    RouteFactory::new()->create([
        'blog_id' => $blog->id,
        'name' => 'post',
    ]);

    createWebhookFor($blog, WebhookEventEnum::POST_UPDATED);

    $event = new PostVariantCreatedEvent($postVariant);
    $listener = new WebhookSubscriber();
    $listener->onPostVariantCreatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($post){
        expect($job->delivery->event)->toBe(WebhookEventEnum::POST_UPDATED);
        expect($job->delivery->data['post']['id'])->toBe($post->id);
        return true;
    });

});

it('calls webhook delivery job on post updated event', function () {
    Queue::fake();

    $blog = blog();
    $post = PostFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::POST_UPDATED);

    $event = new PostUpdatedEvent($post);
    $listener = new WebhookSubscriber();
    $listener->onPostUpdatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($post){
        expect($job->delivery->event)->toBe(WebhookEventEnum::POST_UPDATED);
        expect($job->delivery->data['post']['id'])->toBe($post->id);
        return true;
    });
});

it('calls webhook delivery job on post variant updated event', function () {
    Queue::fake();

    $blog = blog();
    $post = PostFactory::new()->create([
        'blog_id' => $blog->id,
    ]);
    $postVariant = PostVariantFactory::new()->create([
        'post_id' => $post->id,
    ]);
    RouteFactory::new()->create([
        'blog_id' => $blog->id,
        'name' => 'post',
    ]);
    $postVariantOld = $postVariant;
    $postVariant->content = 'new content';

    createWebhookFor($blog, WebhookEventEnum::POST_UPDATED);

    $event = new PostVariantUpdatedEvent($postVariant, $postVariantOld);
    $listener = new WebhookSubscriber();
    $listener->onPostVariantUpdatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($post){
        expect($job->delivery->event)->toBe(WebhookEventEnum::POST_UPDATED);
        expect($job->delivery->data['post']['id'])->toBe($post->id);
        return true;
    });
});

it('call webhook delivery job on post deleted event', function () {
    Queue::fake();

    $blog = blog();
    $post = PostFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::POST_DELETED);

    $event = new PostDeletedEvent($post);
    $listener = new WebhookSubscriber();
    $listener->onPostDeletedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($post){
        expect($job->delivery->event)->toBe(WebhookEventEnum::POST_DELETED);
        expect($job->delivery->data['post']['id'])->toBe($post->id);
        return true;
    });
});

it('call webhook delivery job on post variant deleted event', function () {
    Queue::fake();

    $blog = blog();
    $post = PostFactory::new()->create([
        'blog_id' => $blog->id,
    ]);
    $postVariant = PostVariantFactory::new()->create([
        'post_id' => $post->id,
    ]);
    RouteFactory::new()->create([
        'blog_id' => $blog->id,
        'name' => 'post',
    ]);

    createWebhookFor($blog, WebhookEventEnum::POST_UPDATED);

    $event = new PostVariantDeletedEvent($postVariant);
    $listener = new WebhookSubscriber();
    $listener->onPostVariantDeletedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($post){
        expect($job->delivery->event)->toBe(WebhookEventEnum::POST_UPDATED);
        expect($job->delivery->data['post']['id'])->toBe($post->id);
        return true;
    });
});

it('calls webhook delivery job on tag created event', function () {
    Queue::fake();

    $blog = blog();
    $tag = TagFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::TAG_CREATED);

    $event = new TagCreatedEvent($tag);
    $listener = new WebhookSubscriber();
    $listener->onTagCreatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($tag){
        expect($job->delivery->event)->toBe(WebhookEventEnum::TAG_CREATED);
        expect($job->delivery->data['tag']['id'])->toBe($tag->id);
        return true;
    });
});

it('calls webhook delivery job on tag variant created event', function () {
    Queue::fake();

    $blog = blog();
    $tag = TagFactory::new()->create([
        'blog_id' => $blog->id,
    ]);
    $tagVariant = TagVariantFactory::new()->create([
        'tag_id' => $tag->id,
    ]);
    RouteFactory::new()->create([
        'blog_id' => $blog->id,
        'name' => 'tag',
    ]);

    createWebhookFor($blog, WebhookEventEnum::TAG_UPDATED);

    $event = new TagVariantCreatedEvent($tagVariant);
    $listener = new WebhookSubscriber();
    $listener->onTagVariantCreatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($tag){
        expect($job->delivery->event)->toBe(WebhookEventEnum::TAG_UPDATED);
        expect($job->delivery->data['tag']['id'])->toBe($tag->id);
        return true;
    });
});

it('calls webhook delivery job on tag updated event', function () {
    Queue::fake();

    $blog = blog();
    $tag = TagFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::TAG_UPDATED);

    $event = new TagUpdatedEvent($tag);
    $listener = new WebhookSubscriber();
    $listener->onTagUpdatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($tag){
        expect($job->delivery->event)->toBe(WebhookEventEnum::TAG_UPDATED);
        expect($job->delivery->data['tag']['id'])->toBe($tag->id);
        return true;
    });
});

it('calls webhook delivery job on tag variant updated event', function () {
    Queue::fake();

    $blog = blog();
    $tag = TagFactory::new()->create([
        'blog_id' => $blog->id,
    ]);
    $tagVariant = TagVariantFactory::new()->create([
        'tag_id' => $tag->id,
    ]);
    RouteFactory::new()->create([
        'blog_id' => $blog->id,
        'name' => 'tag',
    ]);

    createWebhookFor($blog, WebhookEventEnum::TAG_UPDATED);

    $event = new TagVariantUpdatedEvent($tagVariant);
    $listener = new WebhookSubscriber();
    $listener->onTagVariantUpdatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($tag){
        expect($job->delivery->event)->toBe(WebhookEventEnum::TAG_UPDATED);
        expect($job->delivery->data['tag']['id'])->toBe($tag->id);
        return true;
    });
});

it('calls webhook delivery job on tag deleted event', function () {
    Queue::fake();

    $blog = blog();
    $tag = TagFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::TAG_DELETED);

    $event = new TagDeletedEvent($tag);
    $listener = new WebhookSubscriber();
    $listener->onTagDeletedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($tag){
        expect($job->delivery->event)->toBe(WebhookEventEnum::TAG_DELETED);
        expect($job->delivery->data['tag']['id'])->toBe($tag->id);
        return true;
    });
});

it('calls webhook delivery job on tag variant deleted event', function () {
    Queue::fake();

    $blog = blog();
    $tag = TagFactory::new()->create([
        'blog_id' => $blog->id,
    ]);
    $tagVariant = TagVariantFactory::new()->create([
        'tag_id' => $tag->id,
    ]);
    RouteFactory::new()->create([
        'blog_id' => $blog->id,
        'name' => 'tag',
    ]);

    createWebhookFor($blog, WebhookEventEnum::TAG_UPDATED);

    $event = new TagVariantDeletedEvent($tagVariant);
    $listener = new WebhookSubscriber();
    $listener->onTagVariantDeletedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($tag){
        expect($job->delivery->event)->toBe(WebhookEventEnum::TAG_UPDATED);
        expect($job->delivery->data['tag']['id'])->toBe($tag->id);
        return true;
    });
});

it('calls webhook delivery job on user created event', function () {
    Queue::fake();

    $blog = blog();
    $user = UserFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::USER_CREATED);

    $event = new UserCreatedEvent($user);
    $listener = new WebhookSubscriber();
    $listener->onUserCreatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($user){
        expect($job->delivery->event)->toBe(WebhookEventEnum::USER_CREATED);
        expect($job->delivery->data['user']['id'])->toBe($user->id);
        return true;
    });
});

it('calls webhook delivery job on user variant created event', function () {
    Queue::fake();

    $blog = blog();
    $user = UserFactory::new()->create([
        'blog_id' => $blog->id,
    ]);
    $userVariant = UserVariantFactory::new()->create([
        'user_id' => $user->id,
    ]);
    RouteFactory::new()->create([
        'blog_id' => $blog->id,
        'name' => 'author',
    ]);

    createWebhookFor($blog, WebhookEventEnum::USER_UPDATED);

    $event = new UserVariantCreatedEvent($userVariant);
    $listener = new WebhookSubscriber();
    $listener->onUserVariantCreatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($user){
        expect($job->delivery->event)->toBe(WebhookEventEnum::USER_UPDATED);
        expect($job->delivery->data['user']['id'])->toBe($user->id);
        return true;
    });
});

it('calls webhook delivery job on user updated event', function () {
    Queue::fake();

    $blog = blog();
    $user = UserFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::USER_UPDATED);

    $event = new UserUpdatedEvent($user);
    $listener = new WebhookSubscriber();
    $listener->onUserUpdatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($user){
        expect($job->delivery->event)->toBe(WebhookEventEnum::USER_UPDATED);
        expect($job->delivery->data['user']['id'])->toBe($user->id);
        return true;
    });
});

it('calls webhook delivery job on user variant updated event', function () {
    Queue::fake();

    $blog = blog();
    $user = UserFactory::new()->create([
        'blog_id' => $blog->id,
    ]);
    $userVariant = UserVariantFactory::new()->create([
        'user_id' => $user->id,
    ]);
    RouteFactory::new()->create([
        'blog_id' => $blog->id,
        'name' => 'author',
    ]);
    
    createWebhookFor($blog, WebhookEventEnum::USER_UPDATED);

    $event = new UserVariantUpdatedEvent($userVariant);
    $listener = new WebhookSubscriber();
    $listener->onUserVariantUpdatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($user){
        expect($job->delivery->event)->toBe(WebhookEventEnum::USER_UPDATED);
        expect($job->delivery->data['user']['id'])->toBe($user->id);
        return true;
    });
});

it('calls webhook delivery job on user deleted event', function () {
    Queue::fake();

    $blog = blog();
    $user = UserFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::USER_DELETED);

    $event = new UserDeletedEvent($user);
    $listener = new WebhookSubscriber();
    $listener->onUserDeletedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($user){
        expect($job->delivery->event)->toBe(WebhookEventEnum::USER_DELETED);
        expect($job->delivery->data['user']['id'])->toBe($user->id);
        return true;
    });
});

it('calls webhook delivery job on user variant deleted event', function () {
    Queue::fake();

    $blog = blog();
    $user = UserFactory::new()->create([
        'blog_id' => $blog->id,
    ]);
    $userVariant = UserVariantFactory::new()->create([
        'user_id' => $user->id,
    ]);
    RouteFactory::new()->create([
        'blog_id' => $blog->id,
        'name' => 'author',
    ]);
    
    createWebhookFor($blog, WebhookEventEnum::USER_UPDATED);

    $event = new UserVariantDeletedEvent ($userVariant);
    $listener = new WebhookSubscriber();
    $listener->onUserVariantDeletedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($user){
        expect($job->delivery->event)->toBe(WebhookEventEnum::USER_UPDATED);
        expect($job->delivery->data['user']['id'])->toBe($user->id);
        return true;
    });
});

it('calls webhook delivery job on media created event', function () {
    Queue::fake();

    $blog = blog();
    $media = MediaFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::MEDIA_CREATED);

    $event = new MediaCreatedEvent($media);
    $listener = new WebhookSubscriber();
    $listener->onMediaCreatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($media){
        expect($job->delivery->event)->toBe(WebhookEventEnum::MEDIA_CREATED);
        expect($job->delivery->data['media']['id'])->toBe($media->id);
        return true;
    });
});

it('calls webhook delivery job on media deleted event', function () {
    Queue::fake();
    $blog = blog();
    $media = MediaFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::MEDIA_DELETED);

    $event = new MediaDeletedEvent($media);
    $listener = new WebhookSubscriber();
    $listener->onMediaDeletedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($media){
        expect($job->delivery->event)->toBe(WebhookEventEnum::MEDIA_DELETED);
        expect($job->delivery->data['media']['id'])->toBe($media->id);
        return true;
    });
});

it('calls webhook delivery job on navigation changed event', function () {
    Queue::fake();

    $blog = blog();
    $navigation = Navigation::factory()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::NAVIGATION_CHANGED);

    $event = new NavigationChangedEvent($navigation);
    $listener = new WebhookSubscriber();
    $listener->onNavigationChangedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($navigation){
        expect($job->delivery->event)->toBe(WebhookEventEnum::NAVIGATION_CHANGED);
        expect($job->delivery->data['navigation'][0]['id'])->toBe($navigation->id);
        return true;
    });
});

it('calls webhook delivery job on navigation variant changed event', function () {
    Queue::fake();

    $blog = blog();
    $navigation = Navigation::factory()->create([
        'blog_id' => $blog->id,
    ]);
    $navigationVariant = NavigationVariant::factory()->create([
        'navigation_id' => $navigation->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::NAVIGATION_CHANGED);

    $event = new NavigationVariantChangedEvent($navigationVariant);
    $listener = new WebhookSubscriber();
    $listener->onNavigationVariantChangedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($navigation){
        expect($job->delivery->event)->toBe(WebhookEventEnum::NAVIGATION_CHANGED);
        expect($job->delivery->data['navigation'][0]['id'])->toBe($navigation->id);
        return true;
    });
});

it('calls webhook delivery job on route changed event', function () {
    Queue::fake();

    $blog = blog();
    $route = RouteFactory::new()->create([
        'blog_id' => $blog->id,
        'name' => 'post',
    ]);

    createWebhookFor($blog, WebhookEventEnum::ROUTES_CHANGED);

    $event = new RouteChangedEvent($route);
    $listener = new WebhookSubscriber();
    $listener->onRouteChangedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($route){
        expect($job->delivery->event)->toBe(WebhookEventEnum::ROUTES_CHANGED);
        expect($job->delivery->data['routes'][0]['id'])->toBe($route->id);
        return true;
    });
});

it('calls webhook delivery job on language changed event', function () {
    Queue::fake();

    $blog = blog();
    $language = LanguageFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::LANGUAGES_CHANGED);

    $event = new LanguageChangedEvent($language);
    $listener = new WebhookSubscriber();
    $listener->onLanguageChangedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($language){
        expect($job->delivery->event)->toBe(WebhookEventEnum::LANGUAGES_CHANGED);
        expect($job->delivery->data['languages'][0]['id'])->toBe($language->id);
        return true;
    });
});

it('calls webhook delivery job on cache clear single event', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, WebhookEventEnum::CACHE_SINGLE);

    $event = new CacheClearSingleEvent($blog, '/test');
    $listener = new WebhookSubscriber();
    $listener->onCacheClearSingleEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) {

        // convert above to expect
        expect($job->delivery->event)->toBe(WebhookEventEnum::CACHE_SINGLE);
        expect($job->delivery->data['path'])->toBe('/test');

        return true;
    });
});

it('calls webhook delivery job on cache clear templates event', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, WebhookEventEnum::CACHE_TEMPLATES);

    $event = new CacheClearTemplatesEvent($blog);
    $listener = new WebhookSubscriber();
    $listener->onCacheClearTemplatesEvent($event);

    Queue::assertPushed(fn (WebhookDeliveryJob $job) => $job->delivery->event === WebhookEventEnum::CACHE_TEMPLATES);
});

it('calls webhook delivery job on cache clear all event', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, WebhookEventEnum::CACHE_ALL);

    $event = new CacheClearAllEvent($blog);
    $listener = new WebhookSubscriber();
    $listener->onCacheClearAllEvent($event);

    Queue::assertPushed(fn (WebhookDeliveryJob $job) => $job->delivery->event === WebhookEventEnum::CACHE_ALL);
});
