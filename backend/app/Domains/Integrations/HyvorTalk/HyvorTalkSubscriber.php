<?php declare(strict_types=1);

namespace App\Domains\Integrations\HyvorTalk;

use App\Domains\Blog\Events\BlogUrlChangedEvent;
use App\Domains\Integrations\HyvorTalk\Event\GatedContentChangedEvent;
use Illuminate\Events\Dispatcher;

class HyvorTalkSubscriber
{

    public function subscribe(Dispatcher $events) : void
    {
        $events->listen(BlogUrlChangedEvent::class, [static::class, 'onBlogUrlUpdate']);
        $events->listen(GatedContentChangedEvent::class, [static::class, 'onGatedContentChange']);
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

    public function onGatedContentChange(GatedContentChangedEvent $event) : void
    {
        $hyvorTalkWebsite = HyvorTalkService::getHyvorTalkWebsite($event->blog);

        if (!$hyvorTalkWebsite)
            return;

        $website = HyvorTalkService::callConsoleApi(
            $hyvorTalkWebsite,
            'GET',
            '/website'
        );

        // if encryption key is not created, create one
        if (!$website['encryption_key']) {

            $website = HyvorTalkService::callConsoleApi(
                $hyvorTalkWebsite,
                'PATCH',
                '/website',
                [
                    'encryption_key' => true
                ]
            );

        }

        $encryptionKey = $website['encryption_key'];

        if (!$encryptionKey)
            return;

        $hyvorTalkWebsite->encryption_key = $encryptionKey;
        $hyvorTalkWebsite->save();
    }

}