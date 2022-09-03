<?php

namespace App\Domains\Blog\Listeners;

use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Jobs\UpdateUrlsJob;
use App\Domains\Route\PermalinkRepository;

class UpdateUrlsListener
{

    public function handle(BlogUpdatedEvent $event)
    {

        $oldUrl = PermalinkRepository::getBaseUrl($event->blogOriginal);
        $newUrl = PermalinkRepository::getBaseUrl($event->blog);

        if ($oldUrl !== $newUrl) {
            UpdateUrlsJob::dispatch($event->blog, $oldUrl, $newUrl);
        }

    }

}