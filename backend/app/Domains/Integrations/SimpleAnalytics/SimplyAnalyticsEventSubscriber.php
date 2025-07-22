<?php

namespace App\Domains\Integrations\SimpleAnalytics;

use App\Domains\Hook\BlogCustomCodeHookEvent;
use Illuminate\Events\Dispatcher;

class SimplyAnalyticsEventSubscriber
{

    public function onBlogCustomCode(BlogCustomCodeHookEvent $event): void
    {
        $blog = $event->getBlog();
        $meta = $blog->getMeta('integration_simple_analytics_enabled');

        if (!$meta) {
            return;
        }

        $blogUrl = $blog->url();
        $hostname = parse_url($blogUrl, PHP_URL_HOST);

        if (!$hostname) {
            return;
        }

        $event->appendCodeHead(
            <<<HTML
        <!-- Simple Analytics platform integration -->
        <script 
            async
            data-hostname="$hostname"
            data-platform="hyvor-blogs"
            src="https://scripts.simpleanalyticscdn.com/latest.js"
        ></script>
        HTML
        );
    }

    /**
     * @return string[]
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            BlogCustomCodeHookEvent::class => 'onBlogCustomCode',
        ];
    }

}