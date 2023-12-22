<?php declare(strict_types=1);

namespace App\Domains\Blog\Listeners;

use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Events\BlogUrlChangedEvent;
use App\Domains\Route\PermalinkRepository;

class CheckBlogUrlChangedListener
{

    public function handle(BlogUpdatedEvent $event) : void
    {

        $oldUrl = PermalinkRepository::getBaseUrl($event->blogOriginal);
        $newUrl = PermalinkRepository::getBaseUrl($event->blog);

        if ($oldUrl !== $newUrl) {
            BlogUrlChangedEvent::dispatch(
                $event->blog,
                $event->blogOriginal,
                $oldUrl,
                $newUrl
            );
        }

    }

}