<?php declare(strict_types=1);

namespace App\Domains\Blog\Listeners;

use App\Domains\Blog\Events\BlogUrlChangedEvent;
use App\Domains\Blog\Jobs\UpdateUrlsJob;

class UpdateUrlsListener
{

    public function handle(BlogUrlChangedEvent $event) : void
    {
        UpdateUrlsJob::dispatch($event->blog, $event->oldUrl, $event->newUrl);
    }

}