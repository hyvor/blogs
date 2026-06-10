<?php

namespace App\Tests\Service\Webhook;

use App\Entity\Enum\WebhookEvent;
use App\Entity\WebhookDelivery;
use App\Message\WebhookDeliverMessage;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Service\Cache\Event\CacheClearSingleEvent;
use App\Service\Cache\Event\CacheClearTemplatesEvent;
use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Navigation\Event\NavigationChangedEvent;
use App\Service\Navigation\Event\NavigationVariantChangedEvent;
use App\Service\Route\Event\RouteChangedEvent;
use App\Service\Webhook\WebhookDeliveryService;
use App\Service\Webhook\WebhookSubscriberListener;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\NavigationFactory;
use App\Tests\Factory\NavigationVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\WebhookFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookSubscriberListener::class)]
#[CoversClass(WebhookDeliveryService::class)]
class WebhookSubscriberListenerTest extends KernelTestCase
{
    private function dispatch(object $event): void
    {
        $this->getService(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class)->dispatch($event);
    }

    /**
     * Asserts that exactly one WebhookDeliverMessage was dispatched, fetches the
     * corresponding WebhookDelivery from the database, and returns it for further assertions.
     *
     * @param array<string, mixed> $expectedDataSubset Key/value pairs that must be present in the delivery data
     */
    private function assertDelivery(WebhookEvent $expectedEvent, array $expectedDataSubset = []): WebhookDelivery
    {
        $dispatched = $this->transport('async')->dispatched();
        $dispatched->assertContains(WebhookDeliverMessage::class, 1);

        /** @var WebhookDeliverMessage $message */
        $message = $dispatched->first(WebhookDeliverMessage::class)->getMessage();

        $delivery = $this->getEm()->find(WebhookDelivery::class, $message->deliveryId);
        $this->assertNotNull($delivery, "WebhookDelivery {$message->deliveryId} not found in DB");

        $this->assertSame($expectedEvent, $delivery->getEvent());

        foreach ($expectedDataSubset as $key => $value) {
            $this->assertArrayHasKey($key, $delivery->getData());
            $this->assertSame($value, $delivery->getData()[$key]);
        }

        return $delivery;
    }

    private function assertNoDelivery(): void
    {
        $this->transport('async')->dispatched()->assertEmpty();
    }

    // -----------------------------------------------------------------------
    // No dispatch when no matching webhook
    // -----------------------------------------------------------------------

    public function test_no_message_when_no_matching_webhook(): void
    {
        $blog = BlogFactory::createOne();
        WebhookFactory::createOne([
            'blog' => $blog,
            'events' => [WebhookEvent::CACHE_ALL],
        ]);
        $lang = LanguageFactory::createOne(['blog' => $blog, 'blog_id' => $blog->getId()]);

        $this->dispatch(new LanguageChangedEvent($lang));

        $this->assertNoDelivery();
    }

    // -----------------------------------------------------------------------
    // NavigationChangedEvent
    // -----------------------------------------------------------------------

    public function test_navigation_changed_dispatches_message(): void
    {
        $blog = BlogFactory::createOne();
        WebhookFactory::createOne([
            'blog' => $blog,
            'events' => [WebhookEvent::NAVIGATION_CHANGED],
        ]);
        $nav = NavigationFactory::createOne(['blog' => $blog, 'blog_id' => $blog->getId()]);

        $this->dispatch(new NavigationChangedEvent($nav));

        $delivery = $this->assertDelivery(WebhookEvent::NAVIGATION_CHANGED);
        /** @var array<int, array{id: int}> $navigations */
        $navigations = $delivery->getData()['navigations'];
        $this->assertSame($nav->getId(), $navigations[0]['id']);
    }

    // -----------------------------------------------------------------------
    // NavigationVariantChangedEvent
    // -----------------------------------------------------------------------

    public function test_navigation_variant_changed_dispatches_message(): void
    {
        $blog = BlogFactory::createOne();
        WebhookFactory::createOne([
            'blog' => $blog,
            'events' => [WebhookEvent::NAVIGATION_CHANGED],
        ]);
        $nav = NavigationFactory::createOne(['blog' => $blog, 'blog_id' => $blog->getId()]);
        $lang = LanguageFactory::createOne(['blog' => $blog, 'blog_id' => $blog->getId()]);
        $variant = NavigationVariantFactory::createOne([
            'navigation' => $nav,
            'language' => $lang,
        ]);

        $this->dispatch(new NavigationVariantChangedEvent($variant));

        $delivery = $this->assertDelivery(WebhookEvent::NAVIGATION_CHANGED);
        /** @var array<int, array{id: int}> $navigations */
        $navigations = $delivery->getData()['navigations'];
        $this->assertSame($nav->getId(), $navigations[0]['id']);
    }

    // -----------------------------------------------------------------------
    // LanguageChangedEvent
    // -----------------------------------------------------------------------

    public function test_language_changed_dispatches_message(): void
    {
        $blog = BlogFactory::createOne();
        WebhookFactory::createOne([
            'blog' => $blog,
            'events' => [WebhookEvent::LANGUAGES_CHANGED],
        ]);
        $lang = LanguageFactory::createOne(['blog' => $blog, 'blog_id' => $blog->getId()]);

        $this->dispatch(new LanguageChangedEvent($lang));

        $delivery = $this->assertDelivery(WebhookEvent::LANGUAGES_CHANGED);
        /** @var array<int, array{id: int}> $languages */
        $languages = $delivery->getData()['languages'];
        $this->assertSame($lang->getId(), $languages[0]['id']);
    }

    // -----------------------------------------------------------------------
    // RouteChangedEvent
    // -----------------------------------------------------------------------

    public function test_route_changed_dispatches_message(): void
    {
        $blog = BlogFactory::createOne();
        WebhookFactory::createOne([
            'blog' => $blog,
            'events' => [WebhookEvent::ROUTES_CHANGED],
        ]);
        $route = RouteFactory::createOne(['blog' => $blog, 'blog_id' => $blog->getId()]);

        $this->dispatch(new RouteChangedEvent($route));

        $delivery = $this->assertDelivery(WebhookEvent::ROUTES_CHANGED);
        /** @var array<int, array{id: int}> $routes */
        $routes = $delivery->getData()['routes'];
        $this->assertSame($route->getId(), $routes[0]['id']);
    }

    // -----------------------------------------------------------------------
    // Cache events
    // -----------------------------------------------------------------------

    public function test_cache_clear_all_dispatches_message(): void
    {
        $blog = BlogFactory::createOne();
        WebhookFactory::createOne([
            'blog' => $blog,
            'events' => [WebhookEvent::CACHE_ALL],
        ]);

        $this->dispatch(new CacheClearAllEvent($blog));

        $this->assertDelivery(WebhookEvent::CACHE_ALL, []);
    }

    public function test_cache_clear_single_dispatches_message_with_path(): void
    {
        $blog = BlogFactory::createOne();
        WebhookFactory::createOne([
            'blog' => $blog,
            'events' => [WebhookEvent::CACHE_SINGLE],
        ]);

        $this->dispatch(new CacheClearSingleEvent($blog, '/test'));

        $this->assertDelivery(WebhookEvent::CACHE_SINGLE, ['path' => '/test']);
    }

    public function test_cache_clear_templates_dispatches_message(): void
    {
        $blog = BlogFactory::createOne();
        WebhookFactory::createOne([
            'blog' => $blog,
            'events' => [WebhookEvent::CACHE_TEMPLATES],
        ]);

        $this->dispatch(new CacheClearTemplatesEvent($blog));

        $this->assertDelivery(WebhookEvent::CACHE_TEMPLATES, []);
    }

    // -----------------------------------------------------------------------
    // Data callback not invoked when no matching webhook
    // -----------------------------------------------------------------------

    public function test_data_callback_not_called_when_no_matching_webhook(): void
    {
        $blog = BlogFactory::createOne();
        // webhook subscribes to CACHE_ALL, not CACHE_SINGLE
        WebhookFactory::createOne([
            'blog' => $blog,
            'events' => [WebhookEvent::CACHE_ALL],
        ]);

        $this->dispatch(new CacheClearSingleEvent($blog, '/test'));

        $this->assertNoDelivery();
    }
}
