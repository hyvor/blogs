<?php declare(strict_types=1);

namespace App\Domains\Integrations\HyvorTalk;

use App\Domains\Blog\Events\BlogUrlChangedEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

class HyvorTalkSubscriber implements ShouldQueue
{

    public function subscribe(Dispatcher $events) : void
    {
        $events->listen(BlogUrlChangedEvent::class, [static::class, 'onBlogUrlUpdate']);
    }

    public function onBlogUrlUpdate(BlogUrlChangedEvent $event) : void
    {

        $hyvorTalkIntegration = HyvorTalkService::getHyvorTalkWebsite($event->blog);

        if (!$hyvorTalkIntegration)
            return;

        $newUrl = $event->newUrl;
        $domain = parse_url($newUrl, PHP_URL_HOST);

        if (!is_string($domain))
            return;
        $subdomain = $event->blog->subdomain . '.hyvorblogs.io';

        $domains = [$domain];

        if ($domain !== $subdomain) {
            $domains[] = $subdomain;
        }

        HyvorTalkService::updateDomains($hyvorTalkIntegration, $domains);
    }

}